CREATE OR REPLACE FUNCTION getPersonName(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
   SELECT name 
FROM ONLY basPerson
    WHERE personId = $1 
$_$;
