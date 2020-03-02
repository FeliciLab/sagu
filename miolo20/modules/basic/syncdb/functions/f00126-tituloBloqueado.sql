CREATE OR REPLACE FUNCTION tituloBloqueado(p_invoiceid integer, v_somenteTitulosEmDia boolean)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: tituloBloqueado
  PURPOSE: Verifica se um tí­tulo está bloqueado, ou seja, se foi pago ou se está vencido

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       05/03/2013 Leovan Tavares    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
BEGIN
    RETURN EXISTS (SELECT 1
                     FROM finreceivableinvoice A
                    WHERE A.invoiceid = p_invoiceid AND
                         (EXISTS (SELECT 1
                                    FROM finentry
                                   WHERE invoiceid = A.invoiceid
                                     AND (operationid IN (SELECT paymentoperation
                                                           FROM findefaultoperations)
                                       OR operationid IN (SELECT agreementOperation
                                                           FROM findefaultoperations)))
                          OR CASE WHEN (v_somenteTitulosEmDia IS TRUE)
                                  THEN maturitydate < now()::DATE
                                  ELSE FALSE 
                                  END  
                          OR EXISTS (SELECT 1
                                       FROM finhistoricoremessa
                                      WHERE invoiceid = A.invoiceid)
                          OR CASE WHEN A.titulodereferencia IS NOT NULL 
                                  THEN tituloBloqueado(A.titulodereferencia, true)
                                  ELSE FALSE
                             END ) );
END;       
$BODY$
  LANGUAGE plpgsql;
--
