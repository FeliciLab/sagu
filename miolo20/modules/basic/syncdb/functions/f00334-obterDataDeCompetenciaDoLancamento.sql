CREATE OR REPLACE FUNCTION obterdatadecompetenciadolancamento(p_entryid integer)
  RETURNS date AS
$BODY$
/******************************************************************************
  NAME: obterDataDeCompetenciaDoLancamento
  DESCRIPTION: Retorna a data de competência referente ao 
               lançamento recebido por parâmetro, conforme definições.
               
               Se o título tem um título de referência, 
               pega o vencimento deste título de referência, ou seja, a data da
               competência será o vencimento do título original. 

               Caso o título seja de negociação, pega a data do lancamento.

  REVISIONS:
  Ver       Date        Author                Description
  --------- ----------  ------------------    ---------------------------------
  1.0       25/03/2015  Augusto A. Silva      1. Função criada.
  1.1       31/03/2015  Maurício de Castro    1. Ajuste na data de competência
                                                 para títulos que tem referência
  1.2       13/04/2015  Luís Felipe Wermann   1. Ajustada data da negociação.
******************************************************************************/
BEGIN
    RETURN (
	   SELECT (CASE WHEN (SELECT RIF.titulodereferencia 
                           FROM ONLY finReceivableInvoice RIF
                          INNER JOIN finEntry EF
                                  ON (EF.invoiceId = RIF.invoiceId)
                               WHERE EF.entryId = E.entryId
                               AND RIF.titulodereferencia = RI.invoiceId) IS NOT NULL
			THEN 
                            (SELECT (CASE WHEN RIF.maturitydate > E.entrydate
                                          THEN
                                               RIF.maturitydate
                                          ELSE
                                               E.entrydate
                                     END)
                          FROM ONLY finReceivableInvoice RIF
                         INNER JOIN finEntry EF
                                 ON (EF.invoiceId = RIF.invoiceId)
                              WHERE RIF.titulodereferencia = RI.invoiceId
                                AND EF.entryId = E.entryId)
			WHEN ( (SELECT invoiceId FROM  obtemTitulosNegociadoDoLancamento(E.entryId) LIMIT 1) > 0 )
                        THEN 
                            E.entryDate
			WHEN (E.entrydate < RI.maturityDate)
			       AND ((COALESCE((SELECT movementdate
						 FROM obterMovimentacaoDeCaixaDoLancamento(E.entryid, 1))::DATE,
					      (SELECT occurrencedate
						 FROM obterMovimentacaoBancariaDoLancamento(E.entryid, 1))::DATE)) IS NULL)
			THEN 
			     RI.maturityDate
			ELSE
			     E.entrydate
			END)
		   FROM finEntry E
	INNER JOIN ONLY finReceivableInvoice RI
		     ON RI.invoiceId = E.invoiceId
          WHERE E.entryId = p_entryId
    );
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE;