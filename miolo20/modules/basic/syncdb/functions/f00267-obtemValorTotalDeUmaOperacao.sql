CREATE OR REPLACE FUNCTION obtemvalortotaldeumaoperacao(p_invoiceid integer, p_operationid integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: obtemValorTotalDeUmaOperacao
  PURPOSE: Obtem a soma de lançamento para um título e operaação específica
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.3       23/11/2013 ftomasini         1.Função criada                                      
**************************************************************************************/
DECLARE
    v_sum NUMERIC;
BEGIN
    SELECT INTO v_sum ROUND(sum(B.value)::numeric, 2)
      FROM ONLY finreceivableinvoice A
     INNER JOIN finEntry B
             ON B.invoiceId = A.invoiceId
          WHERE A.invoiceid = p_invoiceid 
            AND B.operationid = p_operationid;

    IF v_sum IS NULL
    THEN
        v_sum := 0.00::numeric;
    END IF;
    
    RETURN v_sum;
END;
$BODY$
LANGUAGE plpgsql;
