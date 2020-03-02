CREATE OR REPLACE FUNCTION balancewithpoliciesdated (p_invoiceid integer, p_date date)
  RETURNS NUMERIC AS
$BODY$
  /*************************************************************************************
  NAME: balanceWithPoliciesDated
  PURPOSE: Calcula o saldo de um título, se fosse baixado em p_data. Leva em
  consideração as políticas que regem o título.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       22/11/2010 AlexSmith         1. Função criada.
  1.1       01/12/2010 AlexSmith         1. Adicionado arredondamento em duas casas
                                            decimais, já que é o padrão utilizado.
  1.2       07/12/2010 Leovan            1. Adicionado controle dos convênios.
  1.3       22/07/2014 Bruno Fuhr        1. Retornar zero se a soma de todos os valores for nulo.
**************************************************************************************/
DECLARE
    v_policyInfo RECORD;
    v_invoiceInfo RECORD;
    v_balance NUMERIC;
    V_actualDate DATE;
    v_interestValue NUMERIC;
    v_fineValue NUMERIC;
    v_discountValue NUMERIC;
    v_convenantValue NUMERIC;
    v_numDays NUMERIC;
    v_interestPercent NUMERIC;
    v_retVal NUMERIC;
    v_decimals INTEGER;
    v_return NUMERIC;
BEGIN
    -- Inicialização de variáveis
    SELECT INTO v_decimals value::integer FROM basConfig WHERE parameter LIKE 'REAL_ROUND_VALUE';

    v_discountValue := ROUND(getInvoiceDiscountValue(p_invoiceId, p_date), v_decimals);
    v_fineValue := ROUND(getInvoiceFineValue(p_invoiceId, p_date), v_decimals);
    v_interestValue := ROUND(getInvoiceInterestValue(p_invoiceId, p_date), v_decimals);
    v_convenantValue := ROUND(getInvoiceConvenantValue(p_invoiceId, p_date), v_decimals);
    v_balance := TRUNC(balance(p_invoiceId), v_decimals);
    v_actualDate := p_date;
    v_retVal := 0;

    -- Dados do título
    SELECT INTO v_invoiceInfo *
      FROM ONLY finReceivableInvoice
     WHERE invoiceId = p_invoiceId;

    -- Dados da política
    SELECT INTO v_policyInfo *
      FROM finPolicy
     WHERE policyId = v_invoiceInfo.policyId;

    IF ( v_balance = 0 ) THEN
    BEGIN
        v_return := 0;
    END;
    ELSE
    BEGIN
        IF v_invoiceInfo.parcelnumber = 0 
        THEN
            v_return := v_balance + v_interestValue + v_fineValue;
                
	ELSE
            v_return := v_balance + v_interestValue + v_fineValue - v_convenantValue - abs(v_discountValue);
        END IF;
        IF v_return IS NULL THEN
          v_return := 0;
        END IF;
    END;
    END IF;

    RETURN v_return;
END;
$BODY$
  LANGUAGE 'plpgsql';
