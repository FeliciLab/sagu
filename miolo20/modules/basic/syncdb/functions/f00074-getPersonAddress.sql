CREATE OR REPLACE FUNCTION getPersonAddress(bigint) RETURNS character varying
    LANGUAGE sql
    AS $_$
   SELECT COALESCE(A.location||' - ', '')||COALESCE(A.complement||' - ', '')||COALESCE(A.neighborhood||' - ', '')||COALESCE(B.name||' - ', '')|| B.stateId 
      FROM ONLY basPerson A 
LEFT JOIN basCity B 
          ON A.cityId = B.cityId 
    WHERE personId = $1
$_$;
