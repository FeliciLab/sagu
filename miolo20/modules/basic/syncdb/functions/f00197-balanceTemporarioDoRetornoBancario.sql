CREATE OR REPLACE FUNCTION balanceTemporarioDoRetornoBancario(p_invoiceId integer)
RETURNS numeric AS
$BODY$
DECLARE
    v_balance numeric := ( SELECT balance(p_invoiceId) );
    v_balance_temporario numeric := 0;
BEGIN
    SELECT INTO v_balance_temporario 
		ROUND( SUM( CASE WHEN A.operationTypeId = 'D' 
			         THEN 
				      ( 1 * B.value ) 
			         WHEN A.operationTypeId = 'C' 
			         THEN 
				      ( -1 * B.value ) 
			    END ), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT )
	  FROM finOperation A, 
	       temp_bank_movement_entries B 
	 WHERE A.operationId = B.operationId 
	   AND B.invoiceId = p_invoiceId;

     RETURN v_balance + v_balance_temporario;
END;
$BODY$
LANGUAGE 'plpgsql';
