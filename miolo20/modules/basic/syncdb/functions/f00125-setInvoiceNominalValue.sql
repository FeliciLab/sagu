CREATE OR REPLACE FUNCTION setInvoiceNominalValue()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: setinvoicenominalvalue
  DESCRIPTION: Trigger que atualiza o campo finreceivableinvoice.balance com o
               valor nominal de acordo com os lançamentos de débito.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       05/03/13   Leovan T. da Silva 1. Trigger criada.
******************************************************************************/
DECLARE
    v_value NUMERIC;
    v_invoiceid INTEGER;
BEGIN
    IF TG_OP = 'INSERT' THEN
        v_invoiceid = NEW.invoiceId;
    END IF;

    IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
        v_invoiceid = OLD.invoiceId;
    END IF;

    SELECT INTO v_value
                COALESCE(SUM( CASE WHEN B.operationTypeId = 'D' THEN ( 1 * A.value ) 
                                   WHEN B.operationTypeId = 'C' THEN ( -1 * A.value ) 
                               END ), 0)
    FROM finentry A
    INNER JOIN finoperation B ON (B.operationid = A.operationid)
    WHERE A.invoiceid = v_invoiceid 
      AND ((B.operationtypeid = 'D' AND
           A.operationid NOT IN (SELECT interestoperation
                                   FROM findefaultoperations
                                 UNION 
                                 SELECT otheradditionsoperation
                                   FROM findefaultoperations))
       OR A.operationid IN (SELECT cancelcurricularcomponentoperation
                              FROM findefaultoperations));

    UPDATE finInvoice SET nominalvalue = v_value WHERE invoiceId = v_invoiceid;

    IF TG_OP = 'INSERT' THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
        RETURN OLD;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql ;

DROP TRIGGER IF EXISTS setinvoicenominalvalue ON finentry;
CREATE TRIGGER setinvoicenominalvalue
  AFTER INSERT OR UPDATE OR DELETE
  ON finentry
  FOR EACH ROW
  EXECUTE PROCEDURE setinvoicenominalvalue();
