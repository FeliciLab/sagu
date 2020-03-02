CREATE OR REPLACE FUNCTION getCellphone(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT phone FROM basphone WHERE type = 'CEL' AND personId = $1
$_$;
