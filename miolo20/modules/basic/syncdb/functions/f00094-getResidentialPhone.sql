CREATE OR REPLACE FUNCTION getResidentialPhone(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT phone FROM basphone WHERE type = 'RES' AND personId = $1
$_$;
