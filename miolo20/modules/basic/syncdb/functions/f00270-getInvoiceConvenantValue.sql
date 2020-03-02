CREATE OR REPLACE FUNCTION getinvoiceconvenantvalue(p_invoiceid integer, p_date date)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceConvenantValue
  PURPOSE: Obtém o valor total de convê­nios a ser descontado de um título
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       07/12/2010 Leovan            1. FUNÇÃO criada.
  1.1       23/11/2012 Jonas Diel        1. Ajustes para calcular convê­nios somente
                                         para lançamentos com opção 
                                         'Considerar em descontos' habilitada
  1.2       01/11/2013 Nataniel          1. Verifica se a pessoa está inadimplente e
                                         se o convênio está no paramêtro de verificação 
                                         e cancelamento do convênio.
  1.3       23/11/2013 ftomasini         1.Valor de convênios condicionais proporcional                                      
**************************************************************************************/
DECLARE
    v_convenant RECORD;
    v_convenantValue NUMERIC;
    v_balance NUMERIC;
    v_personid INT;
    v_parcel_number NUMERIC;
BEGIN
    v_convenantValue := 0;
    v_balance := getBalanceDiscounts(p_invoiceId);

    SELECT INTO v_personid
    personid
      FROM ONLY finInvoice
          WHERE invoiceid = p_invoiceId;

    FOR v_convenant IN SELECT * FROM getinvoiceconvenants(p_invoiceid, p_date) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)
    LOOP
        IF ( ( SELECT isDefaulter(v_personid)) = 't' AND
         v_convenant.convenantId::TEXT IN ( select regexp_split_to_table(getParameter('FINANCE','CODIGO_CONVENIO_PARA_VERIFICACAO_DE_INADIMPLENCIA'), E'\\,')) )
        THEN
           UPDATE finconvenantperson SET observacao = 'CONVÊNIO CANCELADO POR INADIMPLÊNCIA', enddate = now()::date WHERE convenantId = v_convenant.convenantId; 
        ELSE
            v_convenantValue := v_convenantValue + v_convenant.value;
        END IF;
    END LOOP;

    IF v_convenantValue > v_balance THEN v_convenantValue := v_balance; END IF;
    
    SELECT INTO v_parcel_number
    parcelnumber
      FROM ONLY finInvoice
          WHERE invoiceid = p_invoiceId;

    IF v_parcel_number = 0 THEN
        RETURN 0;
    END IF;

    RETURN v_convenantValue;
END;
$BODY$
  LANGUAGE 'plpgsql';
