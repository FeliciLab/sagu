CREATE OR REPLACE FUNCTION existeMatriculaCursoFerias(p_contractId integer, p_learningperiodid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: existematricula
  PURPOSE: Função que verifica se existe matrícula em curso de férias para um contrato 
           em um determinado período letivo, evitando processamento desnecessário.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/2014 Samuel Koch        1. Criação da função
**************************************************************************************/
DECLARE
    result boolean;
BEGIN

   IF ( SELECT count(A.enrollId) > 0
          FROM acdEnroll A
    INNER JOIN acdGroup B
            ON A.groupId = B.groupId
    INNER JOIN acdLearningPeriod C
            ON B.learningPeriodId = C.learningPeriodId
         WHERE A.contractId = p_contractId
           AND C.periodId IN (SELECT periodid FROM acdlearningperiod WHERE learningperiodid = p_learningperiodid)
           AND B.regimenId = getParameter('ACADEMIC', 'REGIME_DE_FERIAS')::int ) THEN
        result = true; 
    ELSE
        result = false;
    END IF; 

    RETURN result;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION existematriculacursoferias(integer, integer)
  OWNER TO postgres;
