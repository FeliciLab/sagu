CREATE OR REPLACE FUNCTION acp_verificaSituacaoFinalNoCurso(p_ofertaturmaid INTEGER)
RETURNS INTEGER AS
$BODY$
DECLARE
    v_modelodeavaliacao acpmodelodeavaliacao;
    v_personid INTEGER;
    v_cursoid INTEGER;
    v_medianota NUMERIC(7,2);
    v_valorminimoaprovacao INTEGER;
    v_controlefrequencia RECORD;
    v_frequenciacursada NUMERIC(10,2);
    
    v_conceito char(1);
    v_contaconceito INTEGER;
    v_ima INTEGER;
    v_mediaima NUMERIC(10,2); 
    v_aprova boolean;   

    -- Carga horário total do curso
    v_carga_horaria_total_do_curso NUMERIC(10,2);

    v_situacao char(1);
BEGIN

    v_situacao := 'M';

    --Modelo de avaliacao
    SELECT INTO v_modelodeavaliacao MA.* 
      FROM acpmodelodeavaliacao MA
 LEFT JOIN acpperfilcurso PC ON (MA.modelodeavaliacaoid = PC.modelodeavaliacaogeral)
 LEFT JOIN acpcurso C ON (PC.perfilcursoid = C.perfilcursoid)
 LEFT JOIN acpocorrenciacurso OC ON (C.cursoid = OC.cursoid)
 LEFT JOIN acpofertacurso OFC ON (OC.ocorrenciacursoid = OFC.ocorrenciacursoid)
 LEFT JOIN acpofertaturma OFT ON (OFC.ofertacursoid = OFT.ofertacursoid)
     WHERE OFT.ofertaturmaid = p_ofertaturmaid;

     -- Curso
     SELECT INTO v_cursoid C.cursoid
      FROM acpcurso C
 LEFT JOIN acpocorrenciacurso OC ON (C.cursoid = OC.cursoid)
 LEFT JOIN acpofertacurso OFC ON (OC.ocorrenciacursoid = OFC.ocorrenciacursoid)
 LEFT JOIN acpofertaturma OFT ON (OFC.ofertacursoid = OFT.ofertacursoid)
     WHERE OFT.ofertaturmaid = p_ofertaturmaid;

     -- Obtem os alunos matriculados na turma e para cada um calcula as médias.
    FOR v_personid IN ( SELECT DISTINCT personid FROM acpmatricula M 
                              LEFT JOIN acpinscricaoturmagrupo G ON (M.inscricaoturmagrupoid = G.inscricaoturmagrupoid) 
                              LEFT JOIN acpofertaturma T ON (G.ofertaturmaid = T.ofertaturmaid) WHERE T.ofertaturmaid = p_ofertaturmaid )
    LOOP

        -- Tipo de dados por nota
        IF v_modelodeavaliacao.tipodedados = 'N' THEN
        BEGIN

            SELECT INTO v_medianota AVG(nota)::NUMERIC(7,2)
              FROM acpcursoinscricaoavaliacao 
             WHERE cursoinscricaoid IN (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = v_personid AND cursoid = v_cursoid)
               AND modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid;

            SELECT INTO v_valorminimoaprovacao CN.valorminimo
              FROM acpcomponentedeavaliacaonota CN
         LEFT JOIN acpcomponentedeavaliacao C ON (CN.componentedeavaliacaoid = C.componentedeavaliacaoid)
             WHERE C.modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid
               AND C.classedecomponente = 'F';

            IF ( v_medianota >= v_valorminimoaprovacao ) THEN
                v_situacao := 'A';
            ELSE
                v_situacao := 'R';
            END IF;
        END;
        -- Tipo de dados por conceito
        ELSIF v_modelodeavaliacao.tipodedados = 'C' THEN
        BEGIN

            v_contaconceito := 0;
            v_mediaima := 0;
            FOR v_conceito IN SELECT conceito 
                                FROM acpcursoinscricaoavaliacao 
                               WHERE cursoinscricaoid IN (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = v_personid AND cursoid = v_cursoid) 
                                 AND modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid
            LOOP
            BEGIN
                SELECT INTO v_ima ima FROM acpconceitosdeavaliacao WHERE resultado = v_conceito LIMIT 1;
                v_mediaima := v_mediaima + v_ima;

                v_contaconceito = v_contaconceito + 1;
            END;
            END LOOP;

            IF ( v_contaconceito > 0 ) THEN
                v_mediaima := v_mediaima / v_contaconceito;
            END IF;

            SELECT INTO v_aprova aprova FROM acpconceitosdeavaliacao WHERE ima <= v_mediaima ORDER BY ima DESC LIMIT 1;

            IF ( v_aprova ) THEN
                v_situacao := 'A';
            ELSE
                v_situacao := 'R';
            END IF;

        END;
        -- Tipo de dados nenhum ou parecer, sempre cai aprovado
        ELSE
            v_situacao := 'A';
        END IF;

        IF ( v_modelodeavaliacao.habilitacontroledefrequencia ) THEN
        BEGIN
            SELECT INTO v_controlefrequencia * FROM acpcontroledefrequencia WHERE modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid LIMIT 1;
            SELECT INTO v_frequenciacursada cargahorariacursada FROM acpcursoinscricao WHERE personid = v_personid AND cursoid = v_cursoid;
            SELECT INTO v_carga_horaria_total_do_curso * FROM acp_obterCargaHorariaTotalDoCurso(v_cursoid);

            -- Controle de frequência por percentual
            IF ( v_controlefrequencia.tipoDeLimite = '1'::CHAR )
            THEN
   
                IF ( ((v_frequenciacursada * 100) / v_carga_horaria_total_do_curso) < v_controlefrequencia.limiteDeFrequencia ) 
                THEN
                    v_situacao := 'F';
                END IF;

            END IF;
        END;
        END IF;

        UPDATE acpcursoinscricao SET situacao = v_situacao WHERE personid = v_personid AND cursoid = v_cursoid;

    END LOOP;

    RETURN 1;
END;
$BODY$ LANGUAGE plpgsql;

