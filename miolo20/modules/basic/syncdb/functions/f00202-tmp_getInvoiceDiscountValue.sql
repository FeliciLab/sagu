CREATE OR REPLACE FUNCTION tmp_getInvoiceDiscountValue(p_invoiceid integer)
  RETURNS numeric AS
$BODY$

DECLARE
    v_policyInfo RECORD;
    v_invoiceInfo RECORD;
    v_policyDiscountInfo RECORD;
    v_release RECORD;
    v_releaseValue NUMERIC;
    v_discountValue NUMERIC;
    v_balanceValue NUMERIC;
    v_balance NUMERIC;
    v_numberDays INTEGER;   
    v_applyDiscounts BOOLEAN;
    v_date DATE;
    v_maturityMonth INTEGER;
    v_maturityYear INTEGER;
    v_maturityDay INTEGER;
    v_nowMonth INTEGER;  
    v_to_date DATE;
    v_to_dateMonth INTEGER;
    v_hasFinancialAid BOOLEAN;
    v_referenceMaturityDate DATE;
    v_calcNumberDays INTEGER;
BEGIN
    v_discountValue := 0;
    v_releaseValue := 0;

    v_balanceValue := tmp_getBalanceDiscounts(p_invoiceid);
    
    --Caso o título possuir um lançamento que seu tipo de incentivo (incentivetypeid) nao aplique descontos (finincentivetype.applydiscounts) retorna o desconto como 0
    v_applyDiscounts := FALSE;
    
    SELECT INTO v_applyDiscounts COUNT(*) > 0
      FROM finEntry E
INNER JOIN finIncentiveType I
        ON E.incentivetypeid = I.incentivetypeid
     WHERE E.invoiceId = p_invoiceid AND I.applydiscounts = 'f';

    IF v_applyDiscounts THEN
	    RETURN 0;
    END IF;

    -- Invoice data
    SELECT INTO v_invoiceInfo *
      FROM ONLY finReceivableInvoice
     WHERE invoiceId = p_invoiceid;
     
    v_date := v_invoiceInfo.maturityDate - interval '2 day';

    IF EXISTS (SELECT convenantid FROM (SELECT * FROM getinvoiceconvenants(v_invoiceinfo.invoiceid, v_invoiceinfo.maturitydate) AS A(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)) A INNER JOIN finconvenant B USING (convenantid) WHERE B.aplica_descontos IS FALSE AND A.value > 0 AND A.convenantid <> 18) THEN
      RETURN 0;
    END IF;
     
    -- Policy's invoice data
    SELECT INTO v_policyInfo *
      FROM finPolicy
     WHERE policyId = v_invoiceInfo.policyId;

    --Antes do vencimento
    IF v_date <= v_invoiceInfo.maturityDate THEN
        v_numberDays := v_invoiceInfo.maturityDate::date - v_date::date;

        --Obtem o primeiro desconto para dias antes do vencimento que seja menor que o numero de dias
        SELECT INTO v_policyDiscountInfo *
          FROM finPolicyDiscount
         WHERE beforeafter LIKE 'B'
           AND daystodiscount <= v_numberDays
           AND policyId = v_policyInfo.policyId
      ORDER BY daystodiscount DESC
         LIMIT 1;

        --Se nao ha configuracao para descontos antes do vencimento, obtem o primeiro desconto depois do vencimento
        IF v_policyDiscountInfo IS NULL THEN
            SELECT INTO v_policyDiscountInfo *
              FROM finPolicyDiscount
             WHERE beforeafter LIKE 'A'
               AND policyId = v_policyInfo.policyId
          ORDER BY daystodiscount
             LIMIT 1;
        END IF;
    --Depois do vencimento
    ELSE
        IF v_date > v_invoiceInfo.maturityDate THEN

            IF getParameter('FINANCE', 'DESCARTAR_DIA_31') = 'YES' THEN
                v_numberDays := p_date::date - v_referenceMaturityDate;
                v_numberDays := v_numberDays - v_calcNumberDays;

                v_maturityMonth := EXTRACT(MONTH FROM v_referenceMaturityDate);
                v_maturityYear := EXTRACT(YEAR FROM v_referenceMaturityDate);
                v_nowMonth := EXTRACT(MONTH FROM p_date::date);
                v_to_date := TO_DATE('31/' || v_maturityMonth || '/' || v_maturityYear, 'dd/mm/yyyy');
                v_to_dateMonth := EXTRACT(MONTH FROM v_to_date);

                IF ( (v_maturityMonth <> v_nowMonth) AND (v_maturityMonth = v_to_dateMonth) ) THEN
                    v_numberDays := v_numberDays - 1;
                END IF;
            ELSE
                v_numberDays := v_date::date - v_invoiceInfo.referenceMaturityDate::date;
            END IF;

            --Caso for segunda feira desconta 1 dia extendendo o desconto de domingo
            IF getParameter('FINANCE', 'EXTENDER_DESCONTOS_NA_SEGUNDA') = 'YES'
            THEN
                IF ( EXTRACT('DOW' FROM (v_invoiceInfo.referenceMaturityDate::date+v_numberDays)) = 1 ) THEN
                    v_numberDays := v_numberDays-1;
                END IF;
            END IF;

            --Obtem o primeiro desconto para depois do vencimento
            SELECT INTO v_policyDiscountInfo *
              FROM finPolicyDiscount
             WHERE beforeafter = 'A'
               AND daystodiscount >= v_numberDays
               AND policyId = v_policyInfo.policyId
          ORDER BY daystodiscount
             LIMIT 1;
        END IF;
    END IF;
    IF v_policyDiscountInfo.discountValue > 0 THEN
        IF v_policyDiscountInfo.isPercent = TRUE THEN
            v_discountValue := v_balanceValue*(v_policyDiscountInfo.discountValue/100);
        ELSE
            v_discountValue := v_policyDiscountInfo.discountValue;
        END IF;
    END IF;

    --Descontos concedidos - finRelease
    FOR v_release IN SELECT discountValue, isPercent
                       FROM finRelease
                      WHERE invoiceid = p_invoiceId
                        AND v_date BETWEEN beginDate AND endDate
    LOOP
        IF v_release.discountValue > 0 THEN
            IF v_release.isPercent = TRUE THEN
                v_releaseValue := v_releaseValue + (v_balanceValue - v_discountValue) * (v_release.discountValue/100);
            ELSE
                v_releaseValue := v_releaseValue + v_release.discountValue;
            END IF;
    END IF;
    END LOOP;

    IF (v_discountValue + v_releaseValue) > v_balanceValue THEN
        RETURN v_balanceValue;
    ELSE
        RETURN (v_discountValue + v_releaseValue);
    END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tmp_getinvoicediscountvalue(integer)
  OWNER TO postgres;
