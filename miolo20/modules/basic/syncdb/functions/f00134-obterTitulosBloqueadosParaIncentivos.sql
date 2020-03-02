CREATE OR REPLACE FUNCTION obtertitulosbloqueadosparaincentivos(IN p_contractid integer, IN p_learningperiodid integer, IN p_incentivetypeid integer, p_begindate date, p_enddate date)
  RETURNS TABLE(invoiceid integer, parcelnumber integer, nominalvalue numeric, incentivevalue numeric) AS
$BODY$
/*************************************************************************************
  NAME: obterTitulosBloqueadosParaIncentivos
  PURPOSE: Obtém os títulos que não podem sofrer alterção, ou seja, títulos pagos e/ou vencidos, com 
           o valor de incentivo concedido em cada um.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       28/05/2013 Leovan Tavares    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE  
    v_tipo_incentivo finincentivetype;
BEGIN
    SELECT INTO v_tipo_incentivo * FROM ONLY finincentivetype WHERE incentivetypeid = p_incentivetypeid;
    
    RETURN QUERY SELECT X.invoiceid, 
                        X.parcelnumber, 
                        X.nominalvalue,
                        (
                         SELECT COALESCE(SUM( CASE WHEN B.operationTypeId = 'C' THEN ( 1 * A.value ) 
                                                   WHEN B.operationTypeId = 'D' THEN ( -1 * A.value ) 
                                               END ), 0)
                           FROM finEntry A 
                          INNER JOIN finOperation B ON (B.operationid = A.operationid)
                          WHERE A.invoiceid = X.invoiceid
                            AND B.operationid IN (v_tipo_incentivo.operationid, v_tipo_incentivo.paymentoperation, v_tipo_incentivo.repaymentoperation)
                        )
                   FROM obtertitulosbloqueados(p_contractid, p_learningperiodid) X
                   INNER JOIN ONLY finReceivableInvoice titulo ON X.invoiceid = titulo.invoiceid
                   WHERE titulo.referencematuritydate BETWEEN p_begindate AND p_enddate;
END;   
$BODY$
  LANGUAGE plpgsql;
--
