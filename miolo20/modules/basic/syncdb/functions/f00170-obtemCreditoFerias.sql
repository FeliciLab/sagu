CREATE OR REPLACE FUNCTION obtemCreditoFerias(p_contractid integer, p_learningperiodid integer)
  RETURNS integer AS
$BODY$

DECLARE

BEGIN
    RETURN COALESCE (
           (SELECT CASE getParameter('ACADEMIC', 'USAR_CREDITOS_ACADEMICOS') 
                   WHEN 't' THEN SUM(D.academiccredits)
                   WHEN 'f' THEN SUM(D.lessoncredits)
                    END
                          
              FROM acdEnroll A
        INNER JOIN acdGroup B
                ON (A.groupId = B.groupId)
        INNER JOIN acdCurriculum C
                ON (B.curriculumId = C.curriculumId)
        INNER JOIN acdCurricularComponent D
                ON (C.curricularComponentId = D.curricularComponentId AND
                    C.curricularComponentVersion = D.curricularComponentVersion)
        INNER JOIN acdLearningPeriod E
                ON (E.learningPeriodId = B.learningPeriodId)
             WHERE E.periodId IN (SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = p_learningPeriodId)
               AND A.contractId = p_contractId
               AND A.statusid <> GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::integer
               AND B.regimenId = GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::integer)
               , 0);
END;
$BODY$
  LANGUAGE plpgsql;
--
