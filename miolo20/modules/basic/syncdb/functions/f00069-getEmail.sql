CREATE OR REPLACE FUNCTION getEmail(personid bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
  SELECT lower(email) FROM ONLY basPerson WHERE personId = $1 
$_$;