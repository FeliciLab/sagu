CREATE OR REPLACE FUNCTION obterTitulosDaMatricula(p_contractid integer, p_learningperiodid integer)
RETURNS SETOF integer AS
$BODY$
/*************************************************************************************
  NAME: obterTitulosDaMatricula
  PURPOSE: Retorna os títulos gerados na matrícula.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       23/10/13   Augusto A. Silva   1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
	v_periodo record;
BEGIN
	-- Retorna os títulos
	SELECT INTO v_periodo * FROM acdlearningperiod WHERE learningperiodid = p_learningperiodid;
	RETURN QUERY SELECT invoiceid
		       FROM finreceivableinvoice A
		      WHERE A.iscanceled IS FALSE
                 AND EXISTS (SELECT 1
                               FROM finentry
                              WHERE invoiceid = A.invoiceid
                                AND contractid = p_contractId)
                 AND EXISTS (SELECT 1
                               FROM finentry AA
                         INNER JOIN acdlearningperiod BB USING (learningperiodid)
                              WHERE AA.invoiceid = A.invoiceid
                                AND BB.periodid = v_periodo.periodid) 
                   ORDER BY parcelnumber;
END;
$BODY$
LANGUAGE plpgsql;
--
