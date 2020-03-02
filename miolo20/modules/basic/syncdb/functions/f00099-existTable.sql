--VERIFICA SE EXISTE UMA TABELA
CREATE OR REPLACE FUNCTION existTable( nomeTabela varchar ) 
RETURNS bool as $BODY$
DECLARE
    result boolean;
BEGIN
    result = false;             
   IF ( select count(*) > 0 from pg_catalog.pg_tables  WHERE tablename = nomeTabela )
   THEN
   result = true; 
   END IF; 
    RETURN result;
 END;
$BODY$ language plpgsql;
