CREATE OR REPLACE FUNCTION acp_cancelarMatricula()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: acp_cancelarmatricula
  DESCRIPTION: Realiza o fechamento da matricula, calculando notas, percentual
  de frequencia e atribuinto situacao final (Aprovado, Reprovado, Reprovado
  por faltas)

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       20/11/14   Jonas Diel         Funcao criada.
******************************************************************************/
DECLARE
    v_situacao char(1); --Situacao da matricula
    v_estadodematriculaid integer; -- Estado de matricula
    v_frequencia numeric; -- Frequencia do aluno
    v_notafinal numeric := NULL; -- Nota final do aluno
    v_conceitofinal char(2) := NULL; -- Conceito final do aluno
    v_modelodeavaliacao acpmodelodeavaliacao; --Modelo de avaliacao
    v_matricula acpmatricula; --Matricula
    v_controledefrequencia acpcontroledefrequencia; --Controle de frequencia
    v_frequencialimite numeric; --Frequencia limite
    v_componentedeavaliacaoid integer; --Codigo do componente de avaliacao 
    v_componentedeavaliacaonota acpcomponentedeavaliacaonota; -- Componente de avaliacao nota
    v_componentedeavaliacaoconceito acpcomponentedeavaliacaoconceito; --Componente de avaliacao conceito
    v_aprova boolean; --Conceito aprova
    v_componentedeavaliacaonotarecuperacaoid integer; --Codigo do componente de avaliacao nota recuperacao
    v_componentedeavaliacaonotarecuperacao acpcomponentedeavaliacaonotarecuperacao; --Componente de avaliacao nota recuperacao
    v_recuperacaonota numeric; --Nota da recuperacao
    v_conceitodeavaliacaorecuperacaoid integer; --Conceito da recuperacao
    v_altera_situacao BOOLEAN;
    v_disciplina_matriculada INTEGER := 0;
BEGIN
    IF NEW.situacao = 'C' OR OLD.situacao = 'C'
    THEN
        --Matricula
        SELECT INTO v_matricula * FROM acpmatricula WHERE matriculaid = NEW.matriculaid;

        --Modelo de avaliacao
        SELECT INTO v_modelodeavaliacao * FROM acp_obtermodelodaofertadecomponentecurricular(v_matricula.ofertacomponentecurricularid);

        --Calcula o percentual de frequencia da matricula
        SELECT INTO v_frequencia 
    ROUND((SUM(CASE WHEN B1.frequencia = 'P' THEN D1.minutosfrequencia::numeric/60::numeric
                            WHEN B1.frequencia = 'M' THEN D1.minutosfrequencia::numeric/120::numeric
                            ELSE 0 END)/
       SUM(D1.minutosfrequencia::numeric/60::numeric)::numeric)*100,2)
          FROM acpMatricula A1
    INNER JOIN acpFrequencia B1  ON (A1.matriculaId = B1.matriculaId)
    INNER JOIN acpOcorrenciaHorarioOferta C1 ON (B1.ocorrenciaHorarioOfertaId = C1.ocorrenciaHorarioOfertaId)
    INNER JOIN acpHorario D1 ON (C1.horarioId = D1.horarioId)
         WHERE A1.matriculaid = NEW.matriculaid;

        --Estado aprovado
        v_estadodematriculaid := v_modelodeavaliacao.estadodematriculaaprovacaoid;
        v_situacao := 'A';

        --Codigo do componente de avaliacao
        SELECT INTO v_componentedeavaliacaoid avaliacao.componentedeavaliacaoid
            FROM acpavaliacao avaliacao
                LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao on avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid
            WHERE componentedeavaliacao.classedecomponente = 'F' AND avaliacao.matriculaid = v_matricula.matriculaid;

        --Nota final
        IF v_modelodeavaliacao.tipodedados = 'N' THEN

            --Componente de avaliacao nota
            SELECT INTO v_componentedeavaliacaonota * FROM acpcomponentedeavaliacaonota WHERE componentedeavaliacaoid = v_componentedeavaliacaoid;

            --Nota final: Nota do componente final, caso for tcc realiza a media de todos os avaliadores
            SELECT INTO v_notafinal ROUND(AVG(avaliacao.nota),2)
                FROM acpavaliacao avaliacao
                    LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao on avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid
                WHERE componentedeavaliacao.classedecomponente = 'F' AND avaliacao.matriculaid = v_matricula.matriculaid;

            --Caso nao alcansou nota minima
            IF v_notafinal < v_componentedeavaliacaonota.valorminimoaprovacao
            THEN
                --Estado reprovado
                v_estadodematriculaid := v_modelodeavaliacao.estadodematriculareprovacaoid;
                v_situacao := 'R';

                --Busca por recuperacao
                SELECT INTO v_componentedeavaliacaonotarecuperacaoid acpcomponentedeavaliacaonotarecuperacao.componentedeavaliacaonotarecuperacaoid
                    FROM acpcomponentedeavaliacaonotarecuperacao
                    LEFT JOIN acpcomponentedeavaliacaonota ON acpcomponentedeavaliacaonotarecuperacao.componentedeavaliacaonotaid = acpcomponentedeavaliacaonota.componentedeavaliacaonotaid
                    LEFT JOIN acpcomponentedeavaliacao on acpcomponentedeavaliacao.componentedeavaliacaoid = acpcomponentedeavaliacaonota.componentedeavaliacaoid
                    LEFT JOIN acprelacionamentodecomponentes on acprelacionamentodecomponentes.componentedeavaliacaofilho = acpcomponentedeavaliacao.componentedeavaliacaoid
                    WHERE acprelacionamentodecomponentes.componentedeavaliacaopai = v_componentedeavaliacaoid AND acpcomponentedeavaliacao.classedecomponente = 'R';

                --Busca por recuperacao: caso a disciplina for tcc obtem a nota sendo a medida de todos os avaliadores
                SELECT INTO v_recuperacaonota ROUND(AVG(acpavaliacao.nota),2)
                    FROM acpcomponentedeavaliacaonotarecuperacao
                    LEFT JOIN acpcomponentedeavaliacaonota ON acpcomponentedeavaliacaonotarecuperacao.componentedeavaliacaonotaid = acpcomponentedeavaliacaonota.componentedeavaliacaonotaid
                    LEFT JOIN acpcomponentedeavaliacao on acpcomponentedeavaliacao.componentedeavaliacaoid = acpcomponentedeavaliacaonota.componentedeavaliacaoid
                    LEFT JOIN acpavaliacao on acpavaliacao.componentedeavaliacaoid = acpcomponentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acprelacionamentodecomponentes on acprelacionamentodecomponentes.componentedeavaliacaofilho = acpcomponentedeavaliacao.componentedeavaliacaoid
                    WHERE acprelacionamentodecomponentes.componentedeavaliacaopai = v_componentedeavaliacaoid AND acpcomponentedeavaliacao.classedecomponente = 'R' AND acpavaliacao.matriculaid = v_matricula.matriculaid;

                IF v_recuperacaonota IS NOT NULL
                THEN
                    SELECT INTO v_componentedeavaliacaonotarecuperacao * FROM acpcomponentedeavaliacaonotarecuperacao WHERE componentedeavaliacaonotarecuperacaoid = v_componentedeavaliacaonotarecuperacaoid;
                    --Se a nota e maior ou igual a nota de dispensa da recuperacao
                    IF v_componentedeavaliacaonotarecuperacao.notadedispensa <= v_recuperacaonota
                    THEN
                        v_estadodematriculaid := COALESCE(v_modelodeavaliacao.estadodematriculaaprovacaorecuperacaoid, v_modelodeavaliacao.estadodematriculaaprovacaoid);
                        v_situacao := 'A';
                    ELSE
                        v_estadodematriculaid := COALESCE(v_modelodeavaliacao.estadodematriculareprovacaorecuperacaoid, v_modelodeavaliacao.estadodematriculareprovacaoid);
                        v_situacao := 'R';
                    END IF;
                END IF;

            END IF;
            
            --Seta nota final
            NEW.notafinal = COALESCE(v_notafinal,0);

        --Conceito final
        ELSEIF v_modelodeavaliacao.tipodedados = 'C' THEN

            --Componente de avaliacao conceito
            SELECT INTO v_componentedeavaliacaoconceito * FROM acpcomponentedeavaliacaoconceito WHERE componentedeavaliacaoid = v_componentedeavaliacaoid;

            --Salva conceito final
            SELECT INTO v_conceitofinal conceitosdeavaliacao.resultado
                FROM acpavaliacao avaliacao
                    LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao on avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acpcomponentedeavaliacaoconceito componentedeavaliacaoconceito on componentedeavaliacao.componentedeavaliacaoid=componentedeavaliacaoconceito.componentedeavaliacaoid
                    LEFT JOIN acpconjuntodeconceitos conjuntodeconceitos on componentedeavaliacaoconceito.conjuntodeconceitosid=conjuntodeconceitos.conjuntodeconceitosid
                    LEFT JOIN acpconceitosdeavaliacao conceitosdeavaliacao on conceitosdeavaliacao.conjuntodeconceitosid = conjuntodeconceitos.conjuntodeconceitosid
                    AND conceitosdeavaliacao.conceitodeavaliacaoid = avaliacao.conceitodeavaliacaoid
                WHERE componentedeavaliacao.classedecomponente = 'F' 
                  AND avaliacao.matriculaid = v_matricula.matriculaid
                  AND avaliacao.tccbancaid IS NULL;

            --Verifica se conceito final aprova ou nao
            SELECT INTO v_aprova conceitosdeavaliacao.aprova
                FROM acpavaliacao avaliacao
                    LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao on avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acpcomponentedeavaliacaoconceito componentedeavaliacaoconceito on componentedeavaliacao.componentedeavaliacaoid=componentedeavaliacaoconceito.componentedeavaliacaoid
                    LEFT JOIN acpconjuntodeconceitos conjuntodeconceitos on componentedeavaliacaoconceito.conjuntodeconceitosid=conjuntodeconceitos.conjuntodeconceitosid
                    LEFT JOIN acpconceitosdeavaliacao conceitosdeavaliacao on conceitosdeavaliacao.conjuntodeconceitosid = conjuntodeconceitos.conjuntodeconceitosid
                    AND conceitosdeavaliacao.conceitodeavaliacaoid = avaliacao.conceitodeavaliacaoid
                WHERE componentedeavaliacao.classedecomponente = 'F' AND avaliacao.matriculaid = v_matricula.matriculaid;

            --Caso conceito nao aprove
            IF v_aprova IS FALSE
            THEN
                --Estado reprovado
                v_estadodematriculaid := v_modelodeavaliacao.estadodematriculareprovacaoid;
                v_situacao := 'R';

                SELECT INTO v_aprova conceitosdeavaliacao.aprova 
                FROM acpcomponentedeavaliacao
                    LEFT JOIN acpavaliacao on acpavaliacao.componentedeavaliacaoid = acpcomponentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acprelacionamentodecomponentes on acprelacionamentodecomponentes.componentedeavaliacaofilho = acpcomponentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acpcomponentedeavaliacaoconceito on acpcomponentedeavaliacaoconceito.componentedeavaliacaoid = acpcomponentedeavaliacao.componentedeavaliacaoid
                    LEFT JOIN acpconjuntodeconceitos on acpconjuntodeconceitos.conjuntodeconceitosid = acpcomponentedeavaliacaoconceito.conjuntodeconceitosid
                    LEFT JOIN acpconceitosdeavaliacao conceitosdeavaliacao on conceitosdeavaliacao.conjuntodeconceitosid = acpconjuntodeconceitos.conjuntodeconceitosid
                    AND conceitosdeavaliacao.conceitodeavaliacaoid = avaliacao.conceitodeavaliacaoid
                WHERE acprelacionamentodecomponentes.componentedeavaliacaopai = v_componentedeavaliacaoid AND acpcomponentedeavaliacao.classedecomponente = 'R' AND acpavaliacao.matriculaid = v_matricula.matriculaid AND acpavaliacao.tccbancaid IS NULL;

                IF v_aprova IS NOT NULL
                THEN
                    IF v_aprova IS TRUE
                    THEN
                        v_estadodematriculaid := COALESCE(v_modelodeavaliacao.estadodematriculaaprovacaorecuperacaoid, v_modelodeavaliacao.estadodematriculaaprovacaoid);
                        v_situacao := 'A';
                    ELSE
                        v_estadodematriculaid := COALESCE(v_modelodeavaliacao.estadodematriculareprovacaorecuperacaoid, v_modelodeavaliacao.estadodematriculareprovacaoid);
                        v_situacao := 'R';
                    END IF;
                END IF;

            END IF;
        --Nenhum Modelo de avaliao
        ELSEIF v_modelodeavaliacao.tipodedados = '-' THEN
            --Aprovado
            v_estadodematriculaid := v_modelodeavaliacao.estadodematriculaaprovacaoid;
            v_situacao := 'A';
        END IF;

        --Controle de frequencia
        IF v_modelodeavaliacao.habilitacontroledefrequencia IS TRUE
        THEN
            SELECT INTO v_controledefrequencia * FROM acpcontroledefrequencia WHERE modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid;

            IF v_controledefrequencia.tipodelimite = '1' --Percentual (%)
            THEN
                v_frequencialimite := v_controledefrequencia.limitedefrequencia;
            END IF;

            --Verifica se ultrapassou o limite percentual de frequencia
            IF v_frequencialimite > v_frequencia
            THEN
                --Estado de reprovacao por frequencia
                v_estadodematriculaid := v_controledefrequencia.estadodereprovacao;
                v_situacao := 'F';
            END IF;

            --Seta conceito final
            NEW.conceitofinal = v_conceitofinal;

        END IF;

        IF v_frequencia = 0 THEN
            v_frequencia := NULL;
        END IF;

        NEW.frequencia = v_frequencia;

        -- Verifica se todas as disciplinas estão canceladas e atualiza o registro na tabela acpcursoinscricao referente a pessoa e curso
        FOR v_matricula IN ( SELECT M.* 
                               FROM acpmatricula M 
                          LEFT JOIN acpinscricaoturmagrupo G ON (M.inscricaoturmagrupoid = G.inscricaoturmagrupoid) 
                          LEFT JOIN acpofertaturma T ON (G.ofertaturmaid = T.ofertaturmaid)
                          LEFT JOIN acpofertacurso C ON (C.ofertacursoid = T.ofertacursoid)
                          LEFT JOIN acpocorrenciacurso OC ON (C.ocorrenciacursoid = OC.ocorrenciacursoid)
                              WHERE M.personid = NEW.personid
                                AND OC.cursoid IN (SELECT A.cursoId 
                                                     FROM acpocorrenciacurso A
                                               INNER JOIN acpofertacurso B ON (A.ocorrenciacursoid = B.ocorrenciacursoid)
                                               INNER JOIN acpofertaturma C ON (B.ofertacursoid = C.ofertacursoid)
                                               INNER JOIN acpinscricaoturmagrupo D ON (C.ofertaturmaid = D.ofertaturmaid)
                                               INNER JOIN acpmatricula E ON (D.inscricaoturmagrupoid = E.inscricaoturmagrupoid)
                                                    WHERE E.matriculaid = NEW.matriculaid) )
        LOOP
            IF v_matricula.situacao = 'M' 
	    THEN
	        v_disciplina_matriculada := v_disciplina_matriculada + 1;

                IF (v_matricula.matriculaid = NEW.matriculaid AND NEW.situacao = 'C')
                THEN
                    v_disciplina_matriculada := v_disciplina_matriculada - 1;
                END IF;
	    END IF;

	    IF v_matricula.situacao = 'C' OR (v_matricula.matriculaid = NEW.matriculaid AND NEW.situacao = 'C')
	    THEN
	        v_altera_situacao := TRUE;
	    END IF;
        END LOOP;

        IF v_altera_situacao IS TRUE AND v_disciplina_matriculada = 0
        THEN
	    UPDATE acpcursoinscricao SET situacao = 'C' WHERE personid = NEW.personid AND cursoid IN (SELECT A.cursoId 
												        FROM acpocorrenciacurso A
											          INNER JOIN acpofertacurso B ON (A.ocorrenciacursoid = B.ocorrenciacursoid)
											          INNER JOIN acpofertaturma C ON (B.ofertacursoid = C.ofertacursoid)
											          INNER JOIN acpinscricaoturmagrupo D ON (C.ofertaturmaid = D.ofertaturmaid)
											          INNER JOIN acpmatricula E ON (D.inscricaoturmagrupoid = E.inscricaoturmagrupoid)
												       WHERE E.matriculaid = NEW.matriculaid);
        END IF;

    END IF;
    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION acp_cancelarmatricula()
  OWNER TO postgres;


DROP TRIGGER IF EXISTS trg_acp_cancelarmatricula ON acpmatricula;
CREATE TRIGGER trg_acp_cancelarmatricula BEFORE UPDATE ON acpmatricula FOR EACH ROW EXECUTE PROCEDURE acp_cancelarmatricula();
