CREATE OR REPLACE FUNCTION getContractStateByPeriod(v_contractid integer, v_periodid character varying)
  RETURNS integer AS
$BODY$
DECLARE
    result1 int;
BEGIN
    SELECT INTO result1 MC.stateContractId 
           FROM acdMovementContract MC
     INNER JOIN acdLearningPeriod LP
             ON LP.learningPeriodId = MC.learningperiodid
          WHERE MC.contractId = v_contractId
            AND LP.periodId = v_periodid
       ORDER BY MC.stateTime DESC
          LIMIT 1;

    RETURN result1;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
