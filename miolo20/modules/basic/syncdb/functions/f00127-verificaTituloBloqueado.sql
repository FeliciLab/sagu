CREATE OR REPLACE FUNCTION verificaTituloBloqueado(p_invoiceid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaTituloBloqueado
  PURPOSE: Verifica se um título está bloqueado, ou seja, se foi pago, remetido ao banco ou se está vencido.
           A função tituloBloqueado não pode ser utilizado, pois no processo de transferência, por exemplo,
           as configurações que existem a partir de parâmetros não podem ser consideradas.

  REVISIONS:
  Ver       Date         Author                  Description
  --------- ----------   --------------------    ------------------------------------
  1.0       04/03/2015   Nataniel I. da Silva    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
BEGIN
    RETURN EXISTS (SELECT 1
                     FROM finreceivableinvoice A
                    WHERE A.invoiceid = p_invoiceid 
                      AND (EXISTS (SELECT 1
                                    FROM finentry
                                   WHERE invoiceid = A.invoiceid
                                     AND (operationid IN (SELECT paymentoperation
                                                           FROM findefaultoperations)
                                       OR operationid IN (SELECT agreementOperation
                                                           FROM findefaultoperations)))
                          OR (maturitydate < now()::DATE)
                          OR EXISTS (SELECT 1
                                       FROM finhistoricoremessa
                                      WHERE invoiceid = A.invoiceid) ) );
END;       
$BODY$
  LANGUAGE plpgsql;
--
