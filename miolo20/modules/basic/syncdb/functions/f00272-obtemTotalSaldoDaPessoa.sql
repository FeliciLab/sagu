CREATE OR REPLACE FUNCTION obtemTotalSaldoDaPessoa(p_personid bigint, p_date date)
RETURNS FLOAT AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceConvenantValue
  PURPOSE: Obtém o valor total aberto de títulos do aluno
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       03/10/2014   Nataniel          1. FUNÇÃO criada.                                      
**************************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT SUM(CASE C.operationtypeid 
			   WHEN 'C' THEN
			       ROUND(A.value, 2)   
			   WHEN 'D' THEN
			       ROUND((A.value * (-1)), 2)
		       END)
	      FROM finEntry A
   INNER JOIN ONLY finInvoice B
                ON (A.invoiceId = B.invoiceId)
	INNER JOIN finOperation C
                ON (A.operationId = C.operationId)
             WHERE B.personId = p_personid
	       AND A.entryDate < p_date);
END;     
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
