CREATE OR REPLACE FUNCTION getTotalHours(p_contractId integer, p_learningPeriodId integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: getTotalCredits
  PURPOSE: Obtém o valor total de horas academicos em que o aluno estão matriculado

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       04/03/2013 Jonas Diel    1. FUNÇÂO criada.
**************************************************************************************/
DECLARE

BEGIN
    RETURN SUM(E.academicnumberhours)
                  FROM unit_acdEnroll A
            INNER JOIN unit_acdcurriculum C
                    ON C.curriculumId = A.curriculumId
            INNER JOIN acdCurricularComponent E
                    ON (E.curricularComponentId = C.curricularComponentId AND
                        E.curricularComponentVersion = C.curricularComponentVersion)
            INNER JOIN acdGroup B
		    ON A.groupId = B.groupId 
            INNER JOIN unit_acdlearningperiod D
                    ON D.learningPeriodId = B.learningPeriodId
                 WHERE A.contractId = p_contractId
                   AND D.periodId = (SELECT periodId
                                       FROM unit_acdlearningperiod
                                      WHERE learningPeriodId = p_learningPeriodId)
                   AND A.statusId NOT IN (getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT, getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT, getParameter('ACADEMIC', 'ENROLL_STATUS_DESISTING')::INT ) 
                   AND B.regimenId <> GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::integer
                   AND C.curriculumTypeId != GETPARAMETER('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::integer; --Desconsiderar ATIVIDADES COMPLEMENTARES

END
$BODY$
  LANGUAGE plpgsql;
--
