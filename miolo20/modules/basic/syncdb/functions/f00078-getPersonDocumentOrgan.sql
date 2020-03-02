CREATE OR REPLACE FUNCTION getPersonDocumentOrgan(bigint, integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT organ
  FROM basDocument
 WHERE personId = $1
   AND documentTypeId = $2 
$_$;
