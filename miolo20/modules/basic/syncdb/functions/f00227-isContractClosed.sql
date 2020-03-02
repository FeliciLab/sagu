CREATE OR REPLACE FUNCTION iscontractclosed(p_contractid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
NAME: isContractClosed
PURPOSE: Retorna TRUE se um contrato está fechado ou FALSE, se estiver aberto.
REVISIONS:
Ver       Date       Author            Description
--------- ---------- ----------------- ------------------------------------
1.0       21/09/2011 AlexSmith         1. Função criada.
*************************************************************************************/           
DECLARE
    v_retVal RECORD;
BEGIN
    SELECT * INTO v_retVal
      FROM acdMovementContract A
INNER JOIN acdStateContract B
        ON B.stateContractId = A.stateContractId
     WHERE A.contractId = p_contractId
  ORDER BY A.stateTime DESC
     LIMIT 1;

    RETURN (v_retVal.isCloseContract AND v_retVal.inOutTransition IN ('T', 'O'));
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION iscontractclosed(integer)
  OWNER TO postgres;
