--
CREATE OR REPLACE FUNCTION verificaSePrimeiraParcelaRealmenteFoiPaga(p_contractid integer, p_learningperiodid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaseprimeiraparcelarealmentefoipaga
  PURPOSE: Retorna TRUE quando primeira parcela foi paga.
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/12/2013 Augusto A. Silva  1. Função criada.
**************************************************************************************/
DECLARE
    v_pagou BOOLEAN;
BEGIN
    SELECT INTO v_pagou (ROUND(balance(A.invoiceId)::numeric, getParameter('BASIC', 'REAL_ROUND_VALUE')::int) <= 0.0)
      FROM ONLY finreceivableinvoice A
     INNER JOIN finEntry B
             ON B.invoiceId = A.invoiceId
     INNER JOIN acdLearningPeriod C
	     ON C.learningPeriodid = B.learningPeriodId
          WHERE A.iscanceled = FALSE
            AND A.parcelnumber = 1
            AND B.contractId = p_contractid
            AND C.periodId = ( SELECT periodId
				 FROM acdLearningPeriod
				WHERE learningPeriodId = p_learningperiodid );

    IF v_pagou IS NULL
    THEN
        v_pagou := FALSE;
    END IF;
    
    RETURN v_pagou;
END;
$BODY$
LANGUAGE plpgsql;

