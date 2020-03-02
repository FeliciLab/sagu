CREATE OR REPLACE FUNCTION getopeninvoicebyparcel(p_contractid integer, p_learningperiodid integer, p_parcelnumber integer, v_vcont double precision, v_mens double precision)
  RETURNS integer AS
$BODY$
/*********************************************************************************************
  NAME: getopeninvoicebyparcel
  PURPOSE: Obtem titulo aberto para o contrato + periodo letivo + numero parcela passados
*********************************************************************************************/
DECLARE
BEGIN
    RETURN  (SELECT MIN(I.invoiceId)
               FROM finInvoice I
         INNER JOIN finEntry E
                 ON (I.invoiceId = E.invoiceId)
              WHERE I.isCanceled IS FALSE
                AND I.parcelNumber = p_parcelnumber
                AND EXISTS(SELECT 1 FROM finEntry E1 WHERE E1.invoiceID = I.invoiceId AND E1.contractId = p_contractid AND E1.learningPeriodId = p_learningperiodid)
                AND (SELECT * FROM getblockedinvoice(p_contractid, p_learningperiodid, p_parcelnumber, V_VCONT, V_MENS)) IS NULL
                );
END
$BODY$
  LANGUAGE 'plpgsql';
