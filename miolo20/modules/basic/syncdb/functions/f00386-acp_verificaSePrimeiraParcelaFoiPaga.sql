CREATE OR REPLACE FUNCTION acp_verificaSePrimeiraParcelaFoiPaga(p_inscricaoId INT)
RETURNS BOOLEAN AS
$BODY$
BEGIN
    RETURN (
        (COALESCE((SELECT (balance(RI.invoiceId) <= 0)
		     FROM prcTituloInscricao TI
	  INNER JOIN ONLY finReceivableInvoice RI
		       ON RI.invoiceId = TI.invoiceId
		    WHERE TI.inscricaoId = p_inscricaoId
		      AND RI.isCanceled IS FALSE
		      AND TI.tipo = 'M'
		      AND RI.parcelNumber = 1), FALSE))
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;