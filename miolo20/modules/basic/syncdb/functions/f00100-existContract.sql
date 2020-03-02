--VERIFICA SE A PESSOA INFORMADA POSSUI CONTRATO
CREATE OR REPLACE FUNCTION existContract( person bigint ) 
RETURNS bool as $BODY$
DECLARE
    result boolean;
BEGIN

   IF ( select count(*) > 0 personid from acdcontract where personid = person )
   THEN
   result = true; 
    ELSE
    result = false;
   END IF; 
    RETURN result;
 END;
$BODY$ language plpgsql;
