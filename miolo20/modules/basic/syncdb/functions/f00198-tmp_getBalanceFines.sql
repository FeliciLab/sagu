CREATE OR REPLACE FUNCTION tmp_getBalanceFines(p_invoiceid integer)
  RETURNS double precision AS
$BODY$
/*************************************************************************************
  NAME: tmp_getbalancefines
  PURPOSE: Mesma funcionalidade getbalancefines, porém não considera os valores já pagos.

**************************************************************************************/
BEGIN
   return (SELECT SUM( 
                CASE WHEN A.operationTypeId = 'D' THEN ( 1 * B.value )
                END 
               )    
               FROM finOperation A,                                                 
                    finEntry B                                                      
               WHERE A.operationId = B.operationId 
                    AND A.useInFines is true
                    AND B.invoiceId = $1)::FLOAT;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tmp_getbalancefines(integer)
  OWNER TO postgres;

CREATE OR REPLACE FUNCTION tmp_getbalancediscounts(p_invoiceid integer)
  RETURNS double precision AS
$BODY$
BEGIN
   return (SELECT SUM( 
                CASE WHEN A.operationTypeId = 'D' THEN ( 1 * B.value ) 
                WHEN A.operationTypeId = 'C' THEN ( -1 * B.value )    
                END 
               )    
               FROM finOperation A,                                                 
                    finEntry B                                                      
               WHERE A.operationId = B.operationId
                    AND A.operationId not in (5, 9, 11, 25, 26, 90) 
                    AND A.useInDiscounts is true
                    AND B.invoiceId = $1)::FLOAT;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tmp_getbalancediscounts(integer)
  OWNER TO postgres;

