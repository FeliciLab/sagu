CREATE OR REPLACE FUNCTION getPersonDocumentCity(bigint, integer) RETURNS integer
    LANGUAGE sql
    AS $_$
SELECT cityId
  FROM basDocument
 WHERE personId = $1
   AND documentTypeId = $2 
$_$;
