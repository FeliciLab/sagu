CREATE OR REPLACE FUNCTION obtemMovimentacaoAnteriorDoCheque(p_movimentacaoChequeId integer, p_chequeId integer)
  RETURNS INTEGER AS
$BODY$
/*************************************************************************************
  NAME: obtemMovimentacaoAnteriorDoCheque
  PURPOSE: Retorna o código do status da movimentação do cheque anterior a específicada.
 REVISIONS:
  Ver       Date        Author                  Description
  --------- ----------  -----------------       ------------------------------------
  1.0       12/06/2015  Nataniel I da Silva     1. FUNÇÂO criada.
**************************************************************************************/
DECLARE
    v_status INTEGER;
BEGIN
    SELECT INTO v_status statuschequeid
      FROM finMovimentacaoCheque A
     WHERE A.chequeid = p_chequeId
       AND A.movimentacaoChequeId < p_movimentacaoChequeId  
  ORDER BY movimentacaochequeid DESC
     LIMIT 1;

    RETURN v_status;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;