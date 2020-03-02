DROP TRIGGER IF EXISTS trg_calculaMediasDoCurso ON acpofertaturma;

CREATE OR REPLACE FUNCTION calculaMediasDoCurso(p_ofertaturmaid INTEGER)
RETURNS INTEGER AS
$BODY$
DECLARE
    v_todas_cursadas BOOLEAN;

    v_matricula acpmatricula%ROWTYPE;
    v_personid INTEGER;

    v_avaliacao acpavaliacao%ROWTYPE;
    v_tipo_disciplina acptipocomponentecurricular%ROWTYPE;
    v_ima INTEGER;

    v_curso_inscricao acpcursoinscricao%ROWTYPE;

    v_modelo RECORD;

    v_temp_avaliacao RECORD;
    v_conceito char(1);

    v_cursoid INTEGER;
    v_ofertacursoid INTEGER;

    v_cargahorariaoferecida NUMERIC(10,2);
    v_cargahorariacursada NUMERIC(10,2);
    v_cargahorariafrequente NUMERIC(10,2);

    v_notafinalmatricula NUMERIC(10,2);
    v_conceitomatricula INTEGER;

BEGIN

    SELECT INTO v_ofertacursoid ofertacursoid FROM acpofertaturma WHERE ofertaturmaid = p_ofertaturmaid;

    SELECT INTO v_cursoid C.cursoid 
      FROM acpofertacurso OC 
INNER JOIN acpocorrenciacurso OCC ON (OC.ocorrenciacursoid = OCC.ocorrenciacursoid)
INNER JOIN acpcurso C ON (OCC.cursoid = C.cursoid)
     WHERE OC.ofertacursoid = v_ofertacursoid;

    -- Obtem os alunos matriculados na turma e para cada um calcula as médias.
    FOR v_personid IN ( SELECT DISTINCT personid 
                          FROM acpmatricula M 
                    INNER JOIN acpinscricaoturmagrupo G ON (M.inscricaoturmagrupoid = G.inscricaoturmagrupoid) 
                    INNER JOIN acpofertaturma T ON (G.ofertaturmaid = T.ofertaturmaid) 
                         WHERE T.ofertaturmaid = p_ofertaturmaid )
    LOOP

        CREATE TEMP TABLE avaliacoes (
            modelodeavaliacaoid integer,
            tipocomponentecurricularid integer,
            nota numeric(7,2),
            ima numeric(7,2)
        );

        -- Se alguma disciplina não tiver sido cursada, vai marcar esta flag como falsa.
        SELECT INTO v_todas_cursadas CASE WHEN COUNT(*) > 0 THEN FALSE ELSE TRUE END --Não cursou todas obrigatorias
          FROM acpcomponentecurricularmatriz componentematriz
     LEFT JOIN acpmatrizcurriculargrupo matrizgrupo ON matrizgrupo.matrizcurriculargrupoid = componentematriz.matrizcurriculargrupoid
     LEFT JOIN acpmatrizcurricular matriz ON matriz.matrizcurricularid = matrizgrupo.matrizcurricularid
         WHERE matriz.cursoid = v_cursoid
           AND componentematriz.obrigatorio IS TRUE
           AND NOT EXISTS ( SELECT 1 
                              FROM acpmatricula matricula
                         LEFT JOIN acpofertacomponentecurricular ofertacomponente ON ofertacomponente.ofertacomponentecurricularid = matricula.ofertacomponentecurricularid
                         LEFT JOIN acpcomponentecurricularmatriz componentematrizcursado ON componentematrizcursado.componentecurricularmatrizid = ofertacomponente.componentecurricularmatrizid
                         LEFT JOIN acpinscricaoturmagrupo inscricaoturma ON inscricaoturma.inscricaoturmagrupoid = matricula.inscricaoturmagrupoid
                         LEFT JOIN acpinscricao inscricao ON inscricao.inscricaoid = inscricaoturma.inscricaoid
                         LEFT JOIN acpofertaturma ofertaturma ON ofertaturma.ofertaturmaid = inscricaoturma.ofertaturmaid
                         LEFT JOIN acpofertacurso ofertacurso ON ofertacurso.ofertacursoid = ofertaturma.ofertacursoid
                         LEFT JOIN acpocorrenciacurso ocorrenciacurso ON ocorrenciacurso.ocorrenciacursoid = ofertacurso.ocorrenciacursoid
                             WHERE inscricao.personid = v_personid
                               AND ocorrenciacurso.cursoid = matriz.cursoid
                               AND componentematrizcursado.componentecurricularid = componentematriz.componentecurricularid
                               AND matricula.situacao IN ('A', 'P', 'E'));

        -- Obtem as matriculas do aluno e percorre cada uma.
        FOR v_matricula IN ( SELECT M.* FROM acpmatricula M 
                          LEFT JOIN acpinscricaoturmagrupo G ON (M.inscricaoturmagrupoid = G.inscricaoturmagrupoid) 
                          LEFT JOIN acpofertaturma T ON (G.ofertaturmaid = T.ofertaturmaid) 
                              WHERE T.ofertaturmaid = p_ofertaturmaid AND M.personid = v_personid )
        LOOP

            SELECT INTO v_modelo * FROM acp_obtermodelodaofertadecomponentecurricular(v_matricula.ofertacomponentecurricularid) LIMIT 1;

            -- Obtem o tipo de disciplina.
            SELECT INTO v_tipo_disciplina T.* FROM acptipocomponentecurricular T 
                                          LEFT JOIN acpcomponentecurricular C ON (T.tipocomponentecurricularid = C.tipocomponentecurricularid) 
                                          LEFT JOIN acpcomponentecurricularmatriz M ON (C.componentecurricularid = M.componentecurricularid) 
                                          LEFT JOIN acpofertacomponentecurricular O ON (M.componentecurricularmatrizid = O.componentecurricularmatrizid) 
                                          LEFT JOIN acpmatricula A ON (O.ofertacomponentecurricularid = A.ofertacomponentecurricularid) 
                                              WHERE  A.matriculaid = v_matricula.matriculaid;

            -- Se avaliação for do tipo nota
            IF ( v_modelo.tipodedados = 'N' ) THEN
                SELECT INTO v_notafinalmatricula notafinal FROM acpmatricula WHERE matriculaid = v_matricula.matriculaid;
                INSERT INTO avaliacoes VALUES (v_modelo.modelodeavaliacaoid, v_tipo_disciplina.tipocomponentecurricularid, v_notafinalmatricula, NULL);
            END IF;

            -- Se avaliação for do tipo conceito
            IF ( v_modelo.tipodedados = 'C' ) THEN
                SELECT INTO v_conceitomatricula conceitodeavaliacaoid FROM acpconceitosdeavaliacao WHERE resultado = v_matricula.conceitofinal;
                SELECT INTO v_ima ima FROM acpconceitosdeavaliacao WHERE conceitodeavaliacaoid = v_conceitomatricula;
                INSERT INTO avaliacoes VALUES (v_modelo.modelodeavaliacaoid, v_tipo_disciplina.tipocomponentecurricularid, NULL, v_ima);
            END IF;

            -- Se avaliação for do tipo parecer
            IF ( v_modelo.tipodedados = 'P' ) THEN
            END IF;

            -- Se avaliação for do tipo nenhum
            IF ( v_modelo.tipodedados = '-' ) THEN
            END IF;

        END LOOP;

        IF NOT EXISTS (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = v_personid AND cursoid = v_cursoid) THEN
        BEGIN
            INSERT INTO acpcursoinscricao (personid, cursoid) VALUES (v_personid, v_cursoid);
        END;
        END IF;

        SELECT INTO v_curso_inscricao * FROM acpcursoinscricao WHERE personid = v_personid AND cursoid = v_cursoid;
        
        -- Calcula cargas horárias oferecida e cursada.
        SELECT INTO v_cargahorariaoferecida acp_obtercargahorariatotaloferecida(v_cursoid, v_personid);
        SELECT INTO v_cargahorariacursada acp_obtercargahorariacursada(v_cursoid, v_personid);
        SELECT INTO v_cargahorariafrequente acp_obtercargahorariafrequente(v_cursoid, v_personid);

        UPDATE acpcursoinscricao SET cargahorariaoferecida = v_cargahorariaoferecida, cargahorariacursada = v_cargahorariacursada, cargahorariafrequente = v_cargahorariafrequente WHERE cursoinscricaoid = v_curso_inscricao.cursoinscricaoid AND cursoid = v_cursoid;

        -- Atualizar médias do curso.
        DELETE FROM acpcursoinscricaoavaliacao WHERE cursoinscricaoid = v_curso_inscricao.cursoinscricaoid;
        FOR v_temp_avaliacao IN SELECT modelodeavaliacaoid, tipocomponentecurricularid, ROUND(AVG(nota), 2) as nota, ROUND(AVG(ima), 2) as ima 
                                  FROM avaliacoes 
                              GROUP BY modelodeavaliacaoid,tipocomponentecurricularid 
                              ORDER BY modelodeavaliacaoid,tipocomponentecurricularid 
        LOOP

            IF ( v_temp_avaliacao.nota IS NULL ) THEN
            BEGIN
                IF ( v_temp_avaliacao.ima IS NOT NULL ) THEN
                BEGIN
                    SELECT INTO v_conceito resultado FROM acpconceitosdeavaliacao WHERE ima <= v_temp_avaliacao.ima ORDER BY ima DESC LIMIT 1;
                    INSERT INTO acpcursoinscricaoavaliacao (cursoinscricaoid, modelodeavaliacaoid, tipocomponentecurricularid, conceito)
                     VALUES (v_curso_inscricao.cursoinscricaoid, v_temp_avaliacao.modelodeavaliacaoid, v_temp_avaliacao.tipocomponentecurricularid, v_conceito);
                END;
                END IF;
            END;
            ELSE
                INSERT INTO acpcursoinscricaoavaliacao (cursoinscricaoid, modelodeavaliacaoid, tipocomponentecurricularid, nota)
                     VALUES (v_curso_inscricao.cursoinscricaoid, v_temp_avaliacao.modelodeavaliacaoid, v_temp_avaliacao.tipocomponentecurricularid, v_temp_avaliacao.nota);
            END IF;

        END LOOP;

        -- Se todas as disciplinas foram cursadas, atualiza a data de encerramento do curso.
        IF ( v_todas_cursadas ) THEN
            UPDATE acpcursoinscricao SET datafechamento = now()::date WHERE cursoinscricaoid = v_curso_inscricao.cursoinscricaoid AND cursoid = v_cursoid;
            PERFORM acp_verificasituacaofinalnocurso(p_ofertaturmaid);
        ELSE
            UPDATE acpcursoinscricao SET situacao = 'M' WHERE cursoinscricaoid = v_curso_inscricao.cursoinscricaoid AND cursoid = v_cursoid;
        END IF;

        DROP TABLE avaliacoes;

    END LOOP;

    RETURN 1;

END;
$BODY$ LANGUAGE plpgsql;
