CREATE OR REPLACE FUNCTION getblockedinvoice(p_contractid int, p_learningperiodid int, p_parcelnumber int, V_VCONT float, V_MENS float)
RETURNS INT AS
$BODY$
/*********************************************************************************************
  NAME: getblockedinvoice
  PURPOSE: Obtem titulo bloqueado para o contrato + periodo letivo + numero parcela passados
*********************************************************************************************/
DECLARE
BEGIN
    RETURN  (SELECT MIN(I.invoiceId)
               FROM finInvoice I
         INNER JOIN finEntry E
                 ON (I.invoiceId = E.invoiceId)
              WHERE I.isCanceled IS FALSE
                AND I.parcelNumber = p_parcelnumber
              
                AND EXISTS(SELECT 1 FROM finEntry E1 WHERE E1.invoiceID = I.invoiceId AND E1.contractId = p_contractid AND E1.learningPeriodId = p_learningperiodid)
                
                -- Deve existir uma operacao de pagamento
                AND EXISTS(SELECT 1 FROM finEntry E2 WHERE E2.invoiceId = I.invoiceId AND E2.operationId = (SELECT paymentOperation FROM findefaultoperations))

                AND (
                   BALANCE(I.invoiceId) <= 0
                   OR (
                        -- Caso seja PRIMEIRA parcela e aluno for CALOURO e "V_VCONT < V_MENS", conta como bloqueado.
                        ( CASE WHEN I.parcelNumber = 1
                          THEN
                              ( V_VCONT < V_MENS AND isFreshManByPeriod(p_contractId, (SELECT periodId
                                                                                         FROM acdLearningPeriod
                                                                                        WHERE learningPeriodId = p_learningperiodid)::VARCHAR ) )
                          ELSE
                              TRUE
                          END )
                   ) )
                );
END
$BODY$
LANGUAGE 'plpgsql';
