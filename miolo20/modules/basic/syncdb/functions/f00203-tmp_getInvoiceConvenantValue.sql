CREATE OR REPLACE FUNCTION tmp_getInvoiceConvenantValue(p_invoiceid integer)
  RETURNS numeric AS
$BODY$
DECLARE
    v_convenant RECORD;
    v_convenantValue NUMERIC;
    v_balance NUMERIC;
    v_invoice RECORD;
    v_date DATE;
BEGIN
    v_convenantValue := 0;
    v_balance := tmp_getBalanceDiscounts(p_invoiceId);

    SELECT INTO v_invoice * FROM finreceivableinvoice WHERE invoiceid = p_invoiceid;

    v_date := v_invoice.maturitydate - interval '2 day';
  
    IF v_invoice.parcelnumber = 0 THEN RETURN 0; END IF;

    FOR v_convenant IN SELECT * FROM tmp_getinvoiceconvenants(p_invoiceid) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int)
    LOOP
        v_convenantValue := v_convenantValue + v_convenant.value;
    END LOOP;

    IF v_convenantValue > v_balance THEN v_convenantValue := v_balance; END IF;

    RETURN v_convenantValue;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tmp_getinvoiceconvenantvalue(integer)
  OWNER TO postgres;
