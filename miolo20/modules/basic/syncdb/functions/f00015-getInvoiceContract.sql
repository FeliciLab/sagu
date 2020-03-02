CREATE OR REPLACE FUNCTION getInvoiceContract(p_invoiceId int)
RETURNS int AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceContract
  PURPOSE: Obtem o contrato de um titulo
**************************************************************************************/
BEGIN
    RETURN (SELECT max(contractId)
              FROM finEntry
             WHERE invoiceId = p_invoiceId
               AND contractId IS NOT NULL);
END;
$BODY$
LANGUAGE 'plpgsql'
IMMUTABLE;
