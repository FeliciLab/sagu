CREATE OR REPLACE FUNCTION getPersonDocument(bigint, integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT content 
  FROM basDocument
 WHERE personId = $1
   AND documentTypeId = $2
$_$;
