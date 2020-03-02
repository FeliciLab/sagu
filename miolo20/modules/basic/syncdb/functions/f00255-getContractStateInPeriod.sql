CREATE OR REPLACE FUNCTION getContractStateInPeriod(_contractid integer, _periodid character varying)
  RETURNS integer AS
$BODY$

DECLARE
    result1 int;

BEGIN

    SELECT INTO result1 stateContractId A
           FROM acdMovementContract A
      LEFT JOIN acdLearningPeriod B
          USING (learningPeriodId)
          WHERE A.contractId = _contractid
            AND B.periodId = _periodid
       ORDER BY stateTime DESC
          LIMIT 1;

    RETURN result1;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION getcontractstateinperiod(integer, character varying)
  OWNER TO postgres;
