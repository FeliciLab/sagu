CREATE OR REPLACE FUNCTION tmp_getBalanceInterests(p_invoiceid integer)
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
                    AND A.useInInterests is true
                    AND B.invoiceId = $1)::FLOAT;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tmp_getbalanceinterests(integer)
  OWNER TO postgres;
--
