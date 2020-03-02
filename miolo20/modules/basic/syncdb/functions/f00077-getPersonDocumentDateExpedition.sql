CREATE OR REPLACE FUNCTION getPersonDocumentDateExpedition(bigint, integer) RETURNS date
    LANGUAGE sql
    AS $_$
SELECT dateExpedition
  FROM basDocument
 WHERE personId = $1
   AND documentTypeId = $2 
$_$;
