CREATE OR REPLACE FUNCTION array_except(anyarray, anyarray)   
RETURNS anyarray AS 
$BODY$  
/*************************************************************************************
  NAME: array_except
  PURPOSE: Retorna os registros divergentes comparados 
           de uma array secundária à outra principal.
           Ex.: 
           Consulta: SELECT array_except(ARRAY[1, 2, 3, 4], ARRAY[1, 2, 5]);
           Retorno: ARRAY[3, 4]
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       28/04/2012 Augusto A. Silva  1. Função criada.
**************************************************************************************/
BEGIN   
    RETURN (
        SELECT ARRAY(SELECT UNNEST($1)
	             EXCEPT         
		     SELECT UNNEST($2))
    ); 
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;