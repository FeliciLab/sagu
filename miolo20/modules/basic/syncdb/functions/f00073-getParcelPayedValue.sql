CREATE OR REPLACE FUNCTION getParcelPayedValue(bigint, integer, integer) RETURNS numeric
    LANGUAGE sql
    AS $_$
-- FUNÇÃO getparcelpayedvalue
-- Obtém o valor pago de uma parcela de uma pessoa de um peréodo letivo
-- Parémetros: personid bigint, parcelnumber integer, learningperiodid integer
-- Retorno: valor pago
    SELECT ROUND( (SELECT sum(X.value) 
                   FROM finentry X 
                   WHERE X.operationid = (SELECT paymentoperation FROM findefaultoperations LIMIT 1)
                   AND EXISTS (SELECT AA.invoiceid 
                                 FROM finReceivableInvoice AA 
                                WHERE AA.invoiceId = X.invoiceId
                                  AND AA.personId = $1 
                                  AND AA.parcelnumber = $2 
                                  AND EXISTS (SELECT BB.entryid 
                                                FROM finEntry BB
                                          INNER JOIN finOperation CC
                                                  ON (CC.operationId = BB.operationId) 
                                               WHERE BB.invoiceId = AA.invoiceId 
                                                 AND BB.learningperiodid = $3 
                                                 AND CC.operationgroupid = (SELECT value 
                                                                              FROM basconfig 
                                                                             WHERE parameter LIKE 'MONTHLY_FEE_OPERATION_GROUP_ID') ) ) 
                  ), 
                   (SELECT value FROM basconfig WHERE parameter LIKE 'REAL_ROUND_VALUE')::int )
$_$;
