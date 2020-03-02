CREATE OR REPLACE FUNCTION acp_obternotaspendentes(p_ofertacomponentecurricularid int)
RETURNS TABLE(ofertacomponentecurricularid int, disciplina varchar, personid bigint, personname varchar, professorid bigint, professorname varchar, email varchar, descricaonota varchar) AS
$BODY$
/******************************************************************************
  NAME: acp_obternotaspendentes
  DESCRIPTION: Verifica se todas as matriculas tiveram suas frequencias digitadas

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0      21/10/2014  Felipe Ferreira         Função criada.
******************************************************************************/
DECLARE
    v_modelodeavaliacao acpmodelodeavaliacao; --Modelo de avaliacao
    v_componentedeavaliacaoid integer; --Código do componente de avaliacao não registrado
    v_componentedeavaliacao acpcomponentedeavaliacao; --Componente de avaliação não registrado
    v_aluno basphysicalpersonstudent; --Aluno
    v_professor basphysicalpersonprofessor; -- Professor
    v_ofertacomponentecurricular acpofertacomponentecurricular; --Oferta componente curricular
    v_componentecurricularmatriz acpComponenteCurricularMatriz; --Componente curricular da matriz
    v_componentecurricular acpComponenteCurricular; --Componente curricular
    v_ocorrenciahorariooferta acpocorrenciahorariooferta; --Ocorrencia horario oferta
    v_matricula acpmatricula; --Matricula
BEGIN
    SELECT INTO v_modelodeavaliacao * FROM acp_obtermodelodaofertadecomponentecurricular(p_ofertacomponentecurricularid);

    SELECT INTO v_ofertacomponentecurricular * FROM acpOfertaComponenteCurricular WHERE acpOfertaComponenteCurricular.ofertacomponentecurricularid = p_ofertacomponentecurricularid;
    SELECT INTO v_componentecurricularmatriz * FROM acpComponenteCurricularMatriz WHERE acpComponenteCurricularMatriz.componentecurricularmatrizid = v_ofertacomponentecurricular.componentecurricularmatrizid;
    SELECT INTO v_componentecurricular * FROM acpComponenteCurricular WHERE acpComponenteCurricular.componentecurricularid = v_componentecurricularmatriz.componentecurricularid;    
    SELECT INTO v_ocorrenciahorariooferta * FROM acpocorrenciahorariooferta WHERE acpocorrenciahorariooferta.ofertacomponentecurricularid = p_ofertacomponentecurricularid AND cancelada IS FALSE;
    SELECT DISTINCT INTO v_professor * FROM basphysicalpersonprofessor WHERE basphysicalpersonprofessor.personid = v_ocorrenciahorariooferta.professorid;

    --Percorre todas matriculas com status MATRICULADO da oferta do componente curricular e verifica se suas frequencias foram registradas
    FOR v_matricula IN SELECT * FROM acpmatricula WHERE acpmatricula.ofertacomponentecurricularid = p_ofertacomponentecurricularid AND situacao = 'M'
    LOOP
        --Aluno
        SELECT INTO v_aluno * FROM basphysicalpersonstudent WHERE basphysicalpersonstudent.personid = v_matricula.personid;


        --Nota ou conceito
        IF v_modelodeavaliacao.tipodedados != 'P' THEN

            --Percorre todas avaliações da disciplina
            FOR v_componentedeavaliacao IN SELECT * FROM acpcomponentedeavaliacao WHERE acpcomponentedeavaliacao.modelodeavaliacaoid = v_modelodeavaliacao.modelodeavaliacaoid AND classedecomponente = 'F' ORDER BY ordem
            LOOP
                --Nota
                IF v_modelodeavaliacao.tipodedados = 'N' THEN
                    --Verifica se o aluno possui a nota registrada
                    IF count(*) = 0 FROM acpavaliacao WHERE acpavaliacao.componentedeavaliacaoid = v_componentedeavaliacao.componentedeavaliacaoid AND matriculaid = v_matricula.matriculaid AND nota IS NOT NULL
                    THEN                        
                    RETURN QUERY SELECT p_ofertacomponentecurricularid, v_componentecurricular.descricao::varchar, v_aluno.personid , v_aluno.name, v_professor.personid, v_professor.name, v_professor.email, v_componentedeavaliacao.descricao;
                    END IF;

                --Parecer
                ELSEIF v_modelodeavaliacao.tipodedados = 'C' THEN

                    --Verifica se o aluno possui oconceito registrado
                    IF count(*) = 0 FROM acpavaliacao WHERE acpavaliacao.componentedeavaliacaoid = v_componentedeavaliacao.componentedeavaliacaoid AND acpavaliacao.matriculaid = v_matricula.matriculaid AND acpavaliacao.conceitodeavaliacaoid IS NOT NULL
                    THEN
                       RETURN QUERY SELECT p_ofertacomponentecurricularid, v_componentecurricular.descricao::varchar, v_aluno.personid , v_aluno.name, v_professor.personid, v_professor.name, v_professor.email, v_componentedeavaliacao.descricao;
                    END IF;

                END IF;

            END LOOP;
        ELSE --Parecer

            IF v_matricula.parecerfinal IS NULL THEN
               RETURN QUERY SELECT p_ofertacomponentecurricularid, v_componentecurricular.descricao::varchar, v_aluno.personid , v_aluno.name, v_professor.personid, v_professor.name, v_professor.email, ''::varchar;       
            END IF;

        END IF;

    END LOOP;
END;
$BODY$
  LANGUAGE plpgsql;
