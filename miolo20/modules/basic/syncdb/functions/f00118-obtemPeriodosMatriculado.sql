CREATE OR REPLACE FUNCTION obtemPeriodosMatriculado(p_contractId integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: obtemPeriodosMatriculado
  PURPOSE: Retorna o número de períodos que o aluno se matriculou
  se encontra.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ---------------------------------------------
  1.0       14/03/2013 Samuel Koch       1. FUNÇÃO criada.

**************************************************************************************/
DECLARE
BEGIN
    
    RETURN (SELECT COUNT(periodId)
             FROM acdPeriod
            WHERE periodId IN ( SELECT DISTINCT A.periodId
                                           FROM acdLearningPeriod A
                                     INNER JOIN acdGroup B
                                             ON (A.learningPeriodId = B.learningPeriodId)
                                     INNER JOIN acdEnroll C
                                             ON (B.groupId = C.groupId)
                                          WHERE C.contractId = p_contractId ));
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION obtemPeriodosMatriculado(integer)
  OWNER TO postgres;
