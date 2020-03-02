CREATE OR REPLACE FUNCTION acp_fecharMatricula(p_matriculaid integer)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: acp_fecharmatricula
  DESCRIPTION: Realiza o fechamento da matricula, calculando notas, percentual
  de frequencia e atribuinto situacao final (Aprovado, Reprovado, Reprovado
  por faltas)

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   Jonas Diel         Funcao criada.
******************************************************************************/
DECLARE
    v_situacao char(1); --Situacao da matricula
    v_estadodematriculaid integer; -- Estado de matricula
    v_frequencia numeric; -- Frequencia do aluno
    v_notafinal numeric; -- Nota final do aluno
    v_conceitofinal char(2); -- Conceito final do aluno
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
BEGIN
    --Matricula
    SELECT INTO v_matricula * FROM acpmatricula WHERE matriculaid = p_matriculaid;

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
		     WHERE A1.matriculaid = p_matriculaid;

    --Estado aprovado
    v_estadodematriculaid := v_modelodeavaliacao.estadodematriculaaprovacaoid;
    v_situacao := 'A';

    --Codigo do componente de avaliacao
    SELECT INTO v_componentedeavaliacaoid avaliacao.componentedeavaliacaoid
      FROM acpavaliacao avaliacao
 LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao 
        ON (avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid)
     WHERE componentedeavaliacao.classedecomponente = 'F' 
       AND avaliacao.matriculaid = v_matricula.matriculaid
       AND componentedeavaliacao.modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid;

    --Nota final
    IF v_modelodeavaliacao.tipodedados = 'N' THEN

        --Componente de avaliacao nota
        SELECT INTO v_componentedeavaliacaonota * FROM acpcomponentedeavaliacaonota WHERE componentedeavaliacaoid = v_componentedeavaliacaoid;

        --Nota final: Nota do componente final, caso for tcc realiza a media de todos os avaliadores
        SELECT INTO v_notafinal ROUND(AVG(avaliacao.nota),2)
          FROM acpavaliacao avaliacao
     LEFT JOIN acpcomponentedeavaliacao componentedeavaliacao 
            ON (avaliacao.componentedeavaliacaoid=componentedeavaliacao.componentedeavaliacaoid)
         WHERE componentedeavaliacao.classedecomponente = 'F' 
           AND avaliacao.matriculaid = v_matricula.matriculaid
           AND componentedeavaliacao.modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid;

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
    --Nenhum Modelo de avaliação
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
    END IF;

    -- Verificacoes de notas/conceito pendente
    -- Quando modelo avaliacao for do tipo NOTA ou CONCEITO
    IF v_modelodeavaliacao.tipodedados IN ('N', 'C') THEN
        -- Quando nao encontrar avaliacoes
        IF ( (SELECT COUNT(*) FROM acpavaliacao INNER JOIN acpcomponentedeavaliacao C USING (componentedeavaliacaoid) WHERE matriculaid = v_matricula.matriculaid AND nota IS NULL AND c.classedecomponente = 'F') = 0 AND
             (SELECT COUNT(*) FROM acpavaliacao INNER JOIN acpcomponentedeavaliacao C USING (componentedeavaliacaoid) WHERE matriculaid = v_matricula.matriculaid AND conceito IS NULL AND c.classedecomponente = 'F') = 0 )
        THEN
            -- Atribui o estado de matricula para PENDENTE
            v_estadodematriculaid := (SELECT estadodematriculaid FROM acpestadodematricula WHERE aprovado IS NULL LIMIT 1);
            v_situacao := 'M';
        END IF;
    END IF;

    --Encerra matricula
    UPDATE acpmatricula 
    SET frequencia = case when situacao='C' and v_frequencia is null then 0 else v_frequencia end, 
        notafinal = case when situacao='C' and v_notafinal is null then 0 else v_notafinal end, 
        conceitofinal = v_conceitofinal, 
        situacao = case when situacao='C' then 'C' else v_situacao end, 
        estadodematriculaid = v_estadodematriculaid
    WHERE matriculaid = p_matriculaid;

    RETURN true;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION acp_fecharmatricula(integer)
  OWNER TO postgres;
