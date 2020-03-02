CREATE OR REPLACE FUNCTION getDaysToDiscount(p_invoiceid int, v_discountid int)
RETURNS integer AS
$BODY$
/**
* Obtém o número de dias a conceder desconto de um título.
**/
DECLARE
     v_calcNumberDays INTEGER;
     v_invoiceInfo RECORD;
     v_policyDiscountInfo RECORD;
     v_numberDays INTEGER;
     v_maturityMonth INTEGER;
     v_maturityYear INTEGER;
     v_nowMonth INTEGER;
     v_to_date DATE;
     v_to_dateMonth INTEGER;
BEGIN

    -- Invoice data
    SELECT INTO v_invoiceInfo *
      FROM ONLY finReceivableInvoice
     WHERE invoiceId = p_invoiceid;

    -- Policy's invoice data
    SELECT INTO v_policyDiscountInfo *
      FROM finPolicyDiscount
     WHERE discountid = v_discountid;
     
    v_numberDays := v_policyDiscountInfo.daysToDiscount;

    IF v_policyDiscountInfo.beforeAfter = 'A' THEN --Depois
    
        IF getParameter('FINANCE', 'DESCARTAR_DIA_31') = 'YES' 
        THEN		
            v_maturityMonth := EXTRACT(MONTH FROM v_invoiceInfo.referenceMaturityDate);
            v_maturityYear := EXTRACT(YEAR FROM v_invoiceInfo.referenceMaturityDate);
            v_nowMonth := EXTRACT(MONTH FROM v_invoiceInfo.referenceMaturityDate + v_numberDays);
            v_to_date := TO_DATE('31/' || v_maturityMonth || '/' || v_maturityYear, 'dd/mm/yyyy');
            v_to_dateMonth := EXTRACT(MONTH FROM v_to_date);

            IF ( (v_maturityMonth <> v_nowMonth) AND (v_maturityMonth = v_to_dateMonth) ) THEN
                v_numberDays := v_numberDays + 1;
            END IF;		
        END IF;

        --Caso for segunda feira desconta 1 dia extendendo o desconto de domingo
        IF getParameter('FINANCE', 'EXTENDER_DESCONTOS_NA_SEGUNDA') = 'YES'
        THEN
            IF ( EXTRACT('DOW' FROM (v_invoiceInfo.referenceMaturityDate::date+v_numberDays)) = 0 ) THEN
                v_numberDays := v_numberDays+1;
                RAISE NOTICE 'CONDICAO 1, NUMERO DE DIAS %', v_numberDays;
            END IF;
            IF ( EXTRACT('DOW' FROM (v_invoiceInfo.referenceMaturityDate::date+v_numberDays)) = 6 ) THEN
                v_numberDays := v_numberDays+2;
                RAISE NOTICE 'CONDICAO 2, NUMERO DE DIAS %', v_numberDays;
            END IF;
        END IF;
 END IF;

    RETURN v_numberDays;
END;
$BODY$
  LANGUAGE plpgsql;
