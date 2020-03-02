CREATE OR REPLACE FUNCTION obterSaldoDeChequesPorStatus(p_invoiceId INT, p_statusChequeId INT)
RETURNS NUMERIC AS
$BODY$
BEGIN
    RETURN (
        COALESCE((SELECT SUM(C.valorcheque)
		    FROM finCounterMovement CM
	      INNER JOIN finCounterMovementCheque CMC
		      ON CMC.counterMovementId = CM.counterMovementId
	      INNER JOIN finMovimentacaoCheque MVC
		      ON MVC.chequeId = CMC.chequeId
		     AND MVC.statuschequeid = p_statusChequeId
	      INNER JOIN finCheque C
		      ON C.chequeId = MVC.chequeId
		   WHERE CM.invoiceId = p_invoiceId), 0.00)
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
