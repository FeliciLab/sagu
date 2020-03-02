CREATE OR REPLACE FUNCTION getInternalExternalActivitiesProgrammedHours(p_professorid bigint, p_begindate date, p_enddate date) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: getInternalExternalActivitiesProgrammedHours
  PURPOSE: Obtém o total de horas alocadas para atividades internas ou externas no
  peréodo especificado

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
              FROM hur.scheduledActivity A
             WHERE (A.startDate BETWEEN p_beginDate
                                    AND p_endDate
                    OR A.endDate BETWEEN p_beginDate
                                    AND p_endDate)
               AND EXISTS (SELECT 'x'
                             FROM hur.scheduledActivityParticipant X
                            WHERE X.scheduledActivityId = A.scheduledActivityId
                              AND X.personId = p_professorId)) A;

    RETURN COALESCE(v_retVal, 0);
END;
$$;
