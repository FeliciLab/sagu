CREATE OR REPLACE FUNCTION obtem_valor_inicial_dos_lancamentos(p_datacontabil date, p_local_pagamento character varying)
  RETURNS numeric AS
$BODY$

/*************************************************************************************
  NAME: obtem_valor_inicial_dos_lancamentos
  PURPOSE: Retorna informações contábeis de lançamentos.
           Recebe os filtros de data contabil e o local do 
	   pagamento counterid ou bankid

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- -----------------         ------------------------------------
  1.0       21/01/2015 Nataniel I. da silva    1. Função criada.
**************************************************************************************/
DECLARE
    v_saldo NUMERIC;

    v_number VARCHAR;

    v_tipo VARCHAR;
BEGIN

    SELECT INTO v_number substring(p_local_pagamento from position('-' in p_local_pagamento)+2);

    SELECT INTO v_tipo substring(p_local_pagamento from 1 for 1);

    SELECT INTO v_saldo COALESCE((CASE WHEN v_tipo = 'C' THEN
	    (SELECT ROUND(SUM(CASE WHEN fincountermovement.operation = 'C'
		             THEN fincountermovement.value
		             ELSE fincountermovement.value*-1
		             END),2)
		    FROM fincountermovement
	      INNER JOIN finopencounter USING(opencounterid)
		   WHERE finopencounter.counterid = v_number::INT
		     AND fincountermovement.movementdate < p_datacontabil )
	      WHEN v_tipo = 'B' THEN
	      (SELECT ROUND(SUM(CASE WHEN finoperation.operationtypeid = 'C'
		             THEN finentry.value
		             ELSE finentry.value *-1
		             END),2) as saldo
		     FROM finentry
	       INNER JOIN fin.bankmovement USING(bankmovementid)
	       INNER JOIN finoperation ON (finentry.operationid = finoperation.operationid)
	       INNER JOIN finbankaccount 
		       ON (fin.bankmovement.bankid = finbankaccount.bankid)	
		    WHERE finbankaccount.accountnumber = v_number
		      AND fin.bankmovement.occurrencedate < p_datacontabil
	      UNION ALL
              SELECT ROUND(SUM(A.valor *-1),2) as saldo
		     FROM caplancamento A
	       INNER JOIN fin.bankmovement B 
		       ON (B.invoiceid = A.tituloid)
	       INNER JOIN finbankaccount C 
		       ON (C.bankid = B.bankid)	
		    WHERE C.accountnumber = v_number
		      AND B.occurrencedate < p_datacontabil
	      UNION ALL
	      SELECT ROUND(SUM(CASE WHEN D.operationTypeId = 'C'
                                    THEN A.valor
				    ELSE A.valor*-1
 				END),2) as saldo
		     FROM finlancamentosemvinculo A
	       INNER JOIN finbankaccount C 
		       ON (A.bankaccountid = C.bankaccountid)
	       INNER JOIN finoperation D
                       ON (A.operationid = D.operationid)	
		    WHERE C.accountnumber = v_number
		      AND A.datadecaixa < p_datacontabil
		LIMIT 1)
	ELSE NULL END),'0');

    RETURN v_saldo;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION obtem_valor_inicial_dos_lancamentos(date, character varying)
  OWNER TO postgres;
