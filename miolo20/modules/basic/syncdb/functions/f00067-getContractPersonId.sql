CREATE FUNCTION getContractPersonId("contractId" integer) RETURNS bigint
    LANGUAGE sql
    AS $_$
  SELECT personId::bigint FROM acdContract WHERE contractId = $1 
$_$;
