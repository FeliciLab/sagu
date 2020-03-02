CREATE OR REPLACE FUNCTION getContractState(_contractid integer)
  RETURNS integer AS
$BODY$
DECLARE
    result1 int;
BEGIN
    SELECT INTO result1 stateContractId 
           FROM acdMovementContract 
          WHERE contractId = _contractId
       ORDER BY stateTime DESC
          LIMIT 1;

    RETURN result1;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION getcontractstate(integer)
  OWNER TO postgres;
