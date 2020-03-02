CREATE OR REPLACE FUNCTION obterTitulosBloqueados(p_contractId integer, p_learningPeriodId integer)
  RETURNS TABLE (invoiceid fininvoice.invoiceid%TYPE, 
                 parcelnumber fininvoice.parcelnumber%TYPE, 
                 nominalvalue fininvoice.nominalvalue%TYPE) AS
$BODY$
/*************************************************************************************
  NAME: obterTitulosBloqueados
  PURPOSE: Obtém os títulos que não podem sofrer alterção, ou seja, títulos pagos e/ou vencidos

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       05/03/2013 Leovan Tavares    1. FUNÇÃO criada.
**************************************************************************************/
    SELECT invoiceid, parcelnumber, nominalvalue
      FROM finreceivableinvoice A
     WHERE titulobloqueado(A.invoiceid, true)
       AND iscanceled IS FALSE
       AND EXISTS (SELECT entryid 
                     FROM finentry 
                    WHERE invoiceid = A.invoiceid 
                      AND contractid = $1)
       AND EXISTS (SELECT entryid
                     FROM finentry AA
                    INNER JOIN acdlearningperiod BB ON (BB.learningperiodid = AA.learningperiodid)
                    WHERE invoiceid = A.invoiceid
                      AND BB.periodid IN (SELECT periodid 
                                            FROM acdlearningperiod 
                                           WHERE learningperiodid = $2))
       
$BODY$
  LANGUAGE sql;
--
