CREATE OR REPLACE FUNCTION obterStatusAtualDoCheque( p_chequeId int )
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterStatusAtualDoCheque
  PURPOSE: Retorna o status da movimentação atual do cheque.
**************************************************************************************/
DECLARE
BEGIN
	RETURN ( SELECT SC.descricao
                   FROM finMovimentacaoCheque MC
             INNER JOIN finStatusCheque SC
                     ON MC.statusChequeId = SC.statusChequeId
                  WHERE MC.chequeId = p_chequeId
                    AND MC.dateTime = ( SELECT MAX(A.dateTime)
                                       FROM finMovimentacaoCheque A
                                 INNER JOIN finStatusCheque B
                                         ON B.statusChequeId = A.statusChequeId
                                      WHERE A.chequeId = p_chequeId
                                        AND A.data = ( SELECT MAX(data)
                                                         FROM finMovimentacaoCheque
                                                        WHERE chequeId = A.chequeId ) ) );
END;
$BODY$
LANGUAGE 'plpgsql';
--
