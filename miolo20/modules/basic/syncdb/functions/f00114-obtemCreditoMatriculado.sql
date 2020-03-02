CREATE OR REPLACE FUNCTION obtemcreditomatriculado(p_contractid integer, p_learningperiodid integer)
  RETURNS real AS
$BODY$
/*************************************************************************************
  NAME: obtemCreditoMatriculado
  PURPOSE: Retorna o total de créditos em que o aluno está matriculado.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       12/02/2013 Samuel Koch       1. FUNÇÃO criada.
  1.0       19/02/2014 Samuel Koch       1. Alterado função para retornar valores
                                            quebrados. Ex: 19.5
**************************************************************************************/
DECLARE

BEGIN
    RETURN (SELECT CASE getParameter('ACADEMIC', 'USAR_CREDITOS_ACADEMICOS') 
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
               AND B.regimenId <> GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::integer
               AND C.curriculumTypeId <> GETPARAMETER('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::integer);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION obtemcreditomatriculado(integer, integer)
  OWNER TO postgres;
