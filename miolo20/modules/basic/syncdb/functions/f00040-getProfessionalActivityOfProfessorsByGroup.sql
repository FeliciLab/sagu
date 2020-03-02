CREATE OR REPLACE FUNCTION getProfessionalActivityOfProfessorsByGroup(p_groupid int)
RETURNS SETOF int AS
$BODY$
/*********************************************************************************************
  NAME: getProfessionalActivityOfProfessorsByGroup
  PURPOSE: Obtém atividade profissional de cada professor, da disciplina especificada.
           Obs: Professores com horério definido e professor responsável.
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY (SELECT DISTINCT C.professionalactivityid
                  FROM acdScheduleProfessor A
            INNER JOIN acdSchedule B
                    ON (B.scheduleId = A.scheduleId)
            INNER JOIN basProfessionalActivityPeople C
                    ON (C.personId = A.professorId)
                 WHERE B.groupId = p_groupId
                 UNION
                SELECT B.professionalactivityid
                  FROM acdGroup A
            INNER JOIN basProfessionalActivityPeople B
                    ON (A.professorresponsible = B.personId)
                 WHERE A.groupId = p_groupId
                 );
END
$BODY$
LANGUAGE 'plpgsql';
