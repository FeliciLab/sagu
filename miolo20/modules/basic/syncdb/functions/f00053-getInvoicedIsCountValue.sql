CREATE OR REPLACE FUNCTION getInvoicedIsCountValue(p_invoiceid integer, p_date date)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceDiscountValue
  PURPOSE: Calcula os descontos que um título teria, se baixado na data p_date.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       30/11/2010 AlexSmith         1. FUNÇÃO criada.
  1.1       06/12/2010 Leovan            1. Considerar descontos avulsos concedidos para o aluno
  1.2       14/06/2012 Jonas e Leovan    1. Alteração no balance.
  1.3       17/10/2012 Samuel Koch       1. Se o saldo for 0 (zero) retorna 0 (zero) não
                                            calculando a existência de descontos
**************************************************************************************/
DECLARE
    v_policyInfo RECORD;
    v_invoiceInfo RECORD;
    v_policyDiscountInfo finPolicyDiscount%ROWTYPE;
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

    v_balance := balance(p_invoiceid);
    IF v_balance = 0 THEN
        RETURN 0;
    END IF;

    v_balanceValue := getBalanceDiscounts(p_invoiceid);
    v_date := p_date;
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

    IF v_invoiceInfo.parcelnumber = 0 THEN
        RETURN 0;
    END IF;

    -- Policy's invoice data
    SELECT INTO v_policyInfo *
      FROM finPolicy
     WHERE policyId = v_invoiceInfo.policyId;

    v_referenceMaturityDate := v_invoiceInfo.referenceMaturityDate::date;
    v_calcNumberDays := 0;

    --Antes do vencimento
    IF v_date <= v_invoiceInfo.referenceMaturityDate::date THEN
        v_numberDays := v_invoiceInfo.referenceMaturityDate::date - v_date::date;

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

            v_numberDays := v_date::date - v_invoiceInfo.referenceMaturityDate::date;
            

            --Caso for segunda feira desconta 1 dia extendendo o desconto de domingo
            IF getParameter('FINANCE', 'EXTENDER_DESCONTOS_NA_SEGUNDA') = 'YES'
            THEN
                IF ( EXTRACT('DOW' FROM (v_invoiceInfo.referenceMaturityDate::date+v_numberDays)) = 0 ) THEN
                    v_numberDays := v_numberDays - 1;
                    RAISE NOTICE 'CONDICAO 1, NUMERO DE DIAS %', v_numberDays;
                END IF;
                IF ( EXTRACT('DOW' FROM (v_invoiceInfo.referenceMaturityDate::date+v_numberDays)) = 6 ) THEN
                    v_numberDays := v_numberDays - 2;
                    RAISE NOTICE 'CONDICAO 2, NUMERO DE DIAS %', v_numberDays;
                END IF;
            END IF;

            --Obtem o primeiro desconto para depois do vencimento
            SELECT INTO v_policyDiscountInfo *
              FROM finPolicyDiscount
             WHERE beforeafter = 'A'
               AND getDaysToDiscount(p_invoiceid, discountId) >= v_numberDays
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
ALTER FUNCTION getinvoicediscountvalue(integer, date)
  OWNER TO postgres;
