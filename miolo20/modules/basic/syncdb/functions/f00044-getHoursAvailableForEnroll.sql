CREATE OR REPLACE FUNCTION getHoursAvailableForEnroll(p_contractid int, p_classid varchar, p_learningperiodid int)
RETURNS FLOAT AS
$BODY$
/*********************************************************************************************
  NAME: getHoursAvailableForEnroll
  PURPOSE: Obtem carga horaria do periodo
*********************************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT SUM(C.academicNumberHours)
                      FROM acdCurriculum A
                INNER JOIN acdContract B
                        ON (B.courseId = A.courseId)
                INNER JOIN acdCurricularComponent C
                        ON (C.curricularComponentId = A.curricularComponentId
                            AND C.curricularComponentVersion = A.curricularComponentVersion)
                INNER JOIN acdGroup D
                        ON (D.curriculumId = A.curriculumId)
                INNER JOIN acdLearningPeriod E
                        ON E.learningPeriodId = D.learningPeriodId
                INNER JOIN basTurn F
                        ON F.turnId = B.turnId
                INNER JOIN basUnit G
                        ON G.unitId = B.unitId
                INNER JOIN acdRegimen H
                        ON H.regimenId = D.regimenId
                INNER JOIN acdClass K
                        ON K.classId = D.classId
                     WHERE B.contractId = p_contractid
                       AND D.classId = p_classid
                       AND NOT E.isClosed
                       AND E.periodId IN (SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = p_learningperiodid)
                       AND D.regimenId != GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::integer
                       AND A.curriculumTypeId != GETPARAMETER('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::integer --Desconsiderar ATIVIDADES COMPLEMENTARES

    );
END
$BODY$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION getHoursAvailableForEnroll(p_contractId integer, p_learningPeriodId integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: getTotalCredits
  PURPOSE: Obtém o valor total de horas disponíveis para o aluno se matricular

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       04/03/2013 Jonas Diel    1. FUNÇÂO criada.
**************************************************************************************/
DECLARE

BEGIN
    RETURN sum(C.academicnumberhours)
                      FROM acdcurriculum A
                INNER JOIN acdContract B
                        ON (B.courseId = A.courseId
                            AND B.courseVersion = A.courseVersion)
                INNER JOIN acdCurricularComponent C
                        ON (C.curricularComponentId = A.curricularComponentId
                            AND C.curricularComponentVersion = A.curricularComponentVersion)
                INNER JOIN acdGroup D
                        ON (D.curriculumId = A.curriculumId)
                INNER JOIN acdlearningperiod E
                        ON E.learningPeriodId = D.learningPeriodId
                INNER JOIN basTurn F
                        ON F.turnId = B.turnId
                INNER JOIN basUnit G
                        ON G.unitId = B.unitId
                INNER JOIN acdRegimen H
                        ON H.regimenId = D.regimenId
                INNER JOIN acdclass K
                        ON K.classId = D.classId
                     WHERE B.contractId = p_contractId
                       AND D.classId = getContractClassId(p_contractId)
                       AND NOT E.isClosed
                       AND E.periodId IN (SELECT periodId FROM acdlearningperiod WHERE learningPeriodId = p_learningPeriodId)
                       AND D.regimenId <> GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::INTEGER
                       AND A.curriculumTypeId != GETPARAMETER('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::integer;
END
$BODY$
  LANGUAGE plpgsql;
--

