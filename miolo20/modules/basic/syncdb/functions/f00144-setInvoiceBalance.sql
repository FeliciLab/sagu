CREATE OR REPLACE FUNCTION setInvoiceBalance()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: setinvoicebalance
  DESCRIPTION: Trigger que atualiza o campo fininvoice.balance com o saldo do titulo,
               de acordo com os lancamentos.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       30/09/2011 Leovan T. da Silva 1. Trigger criada.
******************************************************************************/
DECLARE
    v_balance NUMERIC;
    v_invoiceid INTEGER;
BEGIN
    IF TG_OP = 'INSERT' THEN
        v_invoiceid = NEW.invoiceId;
    END IF;

    IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
        v_invoiceid = OLD.invoiceId;
    END IF;
    
    SELECT INTO v_balance COALESCE(SUM( CASE WHEN B.operationTypeId = 'D' THEN ( 1 * A.value ) 
                                    WHEN B.operationTypeId = 'C' THEN ( -1 * A.value ) 
                               END ), 0)
      FROM finEntry A
     INNER JOIN finOperation B ON (B.operationId = A.operationId) 
     WHERE A.invoiceId = v_invoiceId;

    UPDATE finInvoice SET balance = v_balance WHERE invoiceId = v_invoiceId;

    IF TG_OP = 'INSERT' THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
        RETURN OLD;
    END IF;
END;
$BODY$
  LANGUAGE 'plpgsql';
--

DROP TRIGGER IF EXISTS setinvoicebalance ON finentry;
CREATE TRIGGER setinvoicebalance
  AFTER INSERT OR UPDATE OR DELETE
  ON finentry
  FOR EACH ROW
  EXECUTE PROCEDURE setinvoicebalance();
