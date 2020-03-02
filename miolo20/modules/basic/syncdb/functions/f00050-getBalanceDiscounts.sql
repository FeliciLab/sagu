--
-- Jonas Guilherme Dahmer - referente ao ticket #13608
--
CREATE OR REPLACE FUNCTION getBalanceDiscounts(p_invoiceId int)
RETURNS FLOAT AS
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
                    AND A.useInDiscounts is true
                    AND B.invoiceId = $1)::FLOAT;
END;
$BODY$
LANGUAGE plpgsql;
---
