CREATE OR REPLACE FUNCTION getInvoiceInterestValue(p_invoiceid integer, p_date date)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceInterestValue
  PURPOSE: Calcula os juros que um título teria, se baixado em p_date.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       30/11/2010 AlexSmith         1. FUNÇÃO criada.
  1.1       06/12/2010 Leovan            1. Considerar liberações de juro e multa
  1.2       14/06/2012 Jonas e Leovan    1. Alteração no balance.
  1.3       07/11/2012 Jonas Diel        1. Adicionada regra da data de ocorrencia
                                            para finais de semana (ENABLE_BUSINESS_USER = 1)
**************************************************************************************/
DECLARE
    v_policyInfo RECORD;
    v_invoiceInfo RECORD;
    v_release BOOLEAN;
    v_interestValue NUMERIC;
    v_balanceValue NUMERIC;
    v_interestPercent NUMERIC;
    v_numDays NUMERIC;
    v_date DATE;
    v_maturityMonth INTEGER;
    v_maturityYear INTEGER;
    v_nowMonth INTEGER;  
    v_to_date DATE;
    v_to_dateMonth INTEGER;
    v_index INTEGER;
    v_referenceDate DATE;
BEGIN
    v_interestValue = 0;
    v_date := p_date;

    -- Invoice data
    SELECT INTO v_invoiceInfo *
      FROM ONLY finReceivableInvoice
     WHERE invoiceId = p_invoiceId;

    FOR v_release IN SELECT releaseInterest
                       FROM finRelease
                      WHERE invoiceid = p_invoiceId
                        AND v_date BETWEEN beginDate AND endDate
    LOOP
        IF v_release = true THEN RETURN 0; END IF;
    END LOOP;

    -- Policy's invoice data
    SELECT INTO v_policyInfo *
      FROM finPolicy
     WHERE policyId = v_invoiceInfo.policyId;

    SELECT INTO v_balanceValue *
      FROM getBalanceInterests(p_invoiceId);

    IF getParameter('FINANCE', 'DESCARTAR_DIA_31') = 'YES' THEN
        v_maturityMonth := EXTRACT(MONTH FROM v_invoiceInfo.referenceMaturityDate::date);
        v_maturityYear := EXTRACT(YEAR FROM v_invoiceInfo.referenceMaturityDate::date);
        v_nowMonth := EXTRACT(MONTH FROM p_date::date);
        v_to_date := TO_DATE('31/' || v_maturityMonth || '/' || v_maturityYear, 'dd/mm/yyyy');
        v_to_dateMonth := EXTRACT(MONTH FROM v_to_date);

        v_index = 0;

        IF ( (v_maturityMonth <> v_nowMonth) AND (v_maturityMonth = v_to_dateMonth) ) THEN
            v_index := 1;
        END IF;

        v_referenceDate := ((v_index||' days')::interval+v_invoiceInfo.referenceMaturityDate+(v_policyInfo.daysToFine||' days')::interval)::date;
    ELSE
            v_referenceDate := (v_invoiceInfo.referenceMaturityDate+(v_policyInfo.daysToInterest||' days')::interval)::date;
    END IF;

    IF getParameter('BASIC', 'ENABLE_BUSINESS_USER') = '1'
        THEN
            IF ( EXTRACT('DOW' FROM v_referenceDate) = 6 ) THEN
                v_referenceDate := v_referenceDate+'2 DAYS'::interval;
            ELSEIF ( EXTRACT('DOW' FROM v_referenceDate) = 0 ) THEN    
                v_referenceDate := v_referenceDate+'1 DAY'::interval;
            END IF;
        END IF;

    IF v_policyInfo.applyInterest = TRUE THEN
        -- Check if maturity date is in interest period
        IF v_date > v_referenceDate THEN
            -- function that returns the difference in months of 2 dates
            v_numDays := v_date::date - v_invoiceInfo.referenceMaturityDate::date;
            v_interestPercent := (v_policyInfo.monthlyInterestPercent/30)/100;
            IF v_policyInfo.interestType = 'S' THEN
                -- Apply simple interest formula
                v_interestValue := (v_balanceValue * v_interestPercent) * v_numDays;
            ELSE
                -- Apply compound interest formula
                v_interestValue := (v_balanceValue * ( POWER(1 + (v_interestPercent) , v_numDays) ) - v_balanceValue);
            END IF;
        END IF;
    END IF;

    --Caso for segunda feira desconta 1 dia extendendo o desconto de domingo
    IF getParameter('FINANCE', 'EXTENDER_DESCONTOS_NA_SEGUNDA') = 'YES'
    THEN
        --Verifica se ira cobrar multa
        IF v_date > v_referenceDate THEN
            --Se A data de referencia for uma segunda
            IF ( EXTRACT('DOW' FROM v_date) = 1 ) THEN
                --Caso não cobrou a multa no dia anterior (domingo) então não cobra na segunda
                IF ( v_date-('1 days')::interval <= v_referenceDate ) THEN
                    v_interestValue := 0;
                END IF;               
            END IF;
        END IF;
    END IF;

    RETURN v_interestValue;
END
$BODY$
  LANGUAGE plpgsql;
