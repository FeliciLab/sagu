CREATE OR REPLACE FUNCTION getWorkPhone(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT phone FROM basphone WHERE type = 'PRO' AND personId = $1
$_$;
