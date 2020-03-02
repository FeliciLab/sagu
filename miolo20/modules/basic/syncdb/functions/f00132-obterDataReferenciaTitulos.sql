CREATE OR REPLACE FUNCTION obterDataReferenciaTitulos(p_contractid integer, p_learningperiodid integer)
  RETURNS date AS
$BODY$
/*************************************************************************************
  NAME: obterDataReferenciaTitulos
  PURPOSE: Retorna a data de referência para a geração de mensalidades. Se já existem títulos
           é a data de emissão do primeiro título. Caso contrário é a data atual.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- -----------------  ------------------------------------
  1.0       05/03/13   Leovan T. da Silva 1. FUNÇÃO criada.
**************************************************************************************/
    SELECT COALESCE(MIN(emissiondate), now()::date) 
      FROM finreceivableinvoice A
     WHERE iscanceled IS FALSE
       AND parcelnumber <> 0
       AND EXISTS (SELECT contractid FROM finentry
                    WHERE invoiceid = A.invoiceid
                      AND contractid = $1)
       AND EXISTS (SELECT E.learningperiodid 
                     FROM finentry E
               INNER JOIN acdlearningperiod F
                       ON E.learningperiodid = F.learningperiodid
                    WHERE invoiceid = A.invoiceid
                      AND F.periodid IN (SELECT periodid 
                                           FROM acdlearningperiod
                                          WHERE learningperiodid = $2));
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
