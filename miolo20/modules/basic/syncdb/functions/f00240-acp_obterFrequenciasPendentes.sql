CREATE OR REPLACE FUNCTION acp_obterfrequenciaspendentes(p_ofertacomponentecurricularid int)
RETURNS TABLE(ofertacomponentecurricularid int, disciplina varchar, personid bigint, personname varchar, professorid bigint, professorname varchar, email varchar, data varchar) AS
$BODY$
/******************************************************************************
  NAME: acp_obterFrequenciasPendentes
  DESCRIPTION: Verifica se todas as matriculas tiveram suas frequencias digitadas

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0      21/10/2014  Felipe Ferreira         Função criada.
******************************************************************************/
DECLARE
    v_matricula acpmatricula; --Matriculas
    v_componentecurricular acpComponenteCurricular; --Componente curricular
    v_ofertacomponentecurricular acpofertacomponentecurricular; --Oferta componente curricular
    v_componentecurricularmatriz acpComponenteCurricularMatriz; --Componente curricular da matriz
    v_aluno basphysicalpersonstudent; -- Aluno
    v_professor basphysicalpersonprofessor; -- Professor
    v_ocorrenciahorariooferta acpocorrenciahorariooferta; --Ocorrencia horario oferta
    
BEGIN

SELECT INTO v_ofertacomponentecurricular * FROM acpOfertaComponenteCurricular WHERE acpOfertaComponenteCurricular.ofertacomponentecurricularid = p_ofertacomponentecurricularid;

SELECT INTO v_componentecurricularmatriz * FROM acpComponenteCurricularMatriz WHERE componentecurricularmatrizid = v_ofertacomponentecurricular.componentecurricularmatrizid;

SELECT INTO v_componentecurricular * FROM acpComponenteCurricular WHERE componentecurricularid = v_componentecurricularmatriz.componentecurricularid;     --Percorre todas ocorrencias de aula da oferta do componente curricular

FOR v_ocorrenciahorariooferta IN SELECT * FROM acpocorrenciahorariooferta WHERE acpocorrenciahorariooferta.ofertacomponentecurricularid = p_ofertacomponentecurricularid AND cancelada IS FALSE ORDER BY dataaula ASC
LOOP

        --Percorre todas matriculas com status MATRICULADO da oferta do componente curricular e verifica se suas frequencias foram registradas
        FOR v_matricula IN SELECT * FROM acpmatricula WHERE acpmatricula.ofertacomponentecurricularid = p_ofertacomponentecurricularid AND situacao = 'M'
        LOOP
            --Aluno
            SELECT INTO v_aluno * FROM basphysicalpersonstudent WHERE basphysicalpersonstudent.personid = v_matricula.personid;
     
            --Professor
             SELECT DISTINCT INTO v_professor * FROM basphysicalpersonprofessor WHERE basphysicalpersonprofessor.personid = v_ocorrenciahorariooferta.professorid;

            --Verifica frequências
            IF count(*) = 0 FROM acpfrequencia WHERE acpfrequencia.ocorrenciahorarioofertaid = v_ocorrenciahorariooferta.ocorrenciahorarioofertaid AND acpfrequencia.matriculaid = v_matricula.matriculaid
            THEN
                    RETURN QUERY SELECT p_ofertacomponentecurricularid, v_componentecurricular.descricao::varchar, v_aluno.personid , v_aluno.name, v_professor.personid, v_professor.name, v_professor.email, datetouser(v_ocorrenciahorariooferta.dataaula);
            END IF;

        END LOOP;
END LOOP;
END;
$BODY$
LANGUAGE plpgsql;
