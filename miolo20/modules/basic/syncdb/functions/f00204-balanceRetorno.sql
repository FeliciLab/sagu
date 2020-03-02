CREATE OR REPLACE FUNCTION balanceRetorno(p_invoiceid integer, p_date date)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: balanceretorno
  PURPOSE: Calcula o saldo de um tï¿½tulo, se fosse baixado em p_data. Leva em
  consideraï¿½ï¿½o as polï¿½ticas que regem o tï¿½tulo.
  DESCRIPTION: vide "PURPOSE".

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
BEGIN
    -- Inicialização de variáveis
    SELECT INTO v_decimals value::integer FROM basConfig WHERE parameter LIKE 'REAL_ROUND_VALUE';

    v_discountValue := ROUND(tmp_getInvoiceDiscountValue(p_invoiceId), v_decimals);
    v_fineValue := ROUND(tmp_getInvoiceFineValue(p_invoiceId, p_date), v_decimals);
    v_interestValue := ROUND(tmp_getInvoiceInterestValue(p_invoiceId, p_date), v_decimals);
    v_convenantValue := ROUND(tmp_getInvoiceConvenantValue(p_invoiceId), v_decimals);
    v_actualDate := p_date;
    v_retVal := 0;

    SELECT INTO v_balance ROUND(SUM( CASE WHEN A.operationTypeId = 'D' THEN ( 1 * B.value ) END ), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT )             
           FROM finOperation A,                                                    
                finEntry B                                                         
          WHERE A.operationId = B.operationId                                      
            AND B.invoiceId = p_invoiceId;

    -- Dados do título
    SELECT INTO v_invoiceInfo *
      FROM ONLY finReceivableInvoice
     WHERE invoiceId = p_invoiceId;

    -- Dados da política
    SELECT INTO v_policyInfo *
      FROM finPolicy
     WHERE policyId = v_invoiceInfo.policyId;

    IF v_invoiceInfo.parcelnumber = 0 THEN
        RETURN v_balance + v_interestValue + v_fineValue;
    END IF;

    --RAISE NOTICE 'BALANCE % JUROS % MULTAS % CONVENIO % DESCONTO %', v_balance, v_interestValue, v_fineValue, v_convenantValue, v_discountValue;
	
    RETURN v_balance + v_interestValue + v_fineValue - v_convenantValue - v_discountValue;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION balanceretorno(integer, date)
  OWNER TO postgres;
