CREATE OR REPLACE FUNCTION getMessagePhone(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
    SELECT phone FROM basphone WHERE type = 'REC' AND personId = $1
$_$;
