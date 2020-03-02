CREATE OR REPLACE FUNCTION getInternalExternalActivitiesRealizedHours(p_professorid bigint, p_begindate date, p_enddate date) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: getInternalExternalActivitiesRealizedHours
  PURPOSE: Obtém o total de horas alocadas realizadas em atividades internas ou
  externas no peréodo especificado

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       24/06/2011 Alexandre Schmidt 1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_retVal float;
BEGIN
    SELECT EXTRACT(DAY FROM totalTime) * 24 +
           EXTRACT(HOUR FROM totalTime) +
           EXTRACT(MINUTE FROM totalTime) / 60 +
           EXTRACT(SECOND FROM totalTime) / (60 * 60) INTO v_retVal
      FROM (SELECT SUM(A.endDate - A.startDate) AS totalTime
              FROM hur.realizedActivity A
             WHERE (A.startDate BETWEEN p_beginDate
                                    AND p_endDate
                    OR A.endDate BETWEEN p_beginDate
                                    AND p_endDate)
               AND EXISTS (SELECT 'x'
                             FROM hur.realizedActivityParticipant X
                            WHERE X.realizedActivityId = A.realizedActivityId
                              AND X.personId = p_professorId)) A;

    RETURN COALESCE(v_retVal, 0);
END;
$$;
