CREATE OR REPLACE FUNCTION getParcelOpenValue(bigint, integer, integer) RETURNS numeric
    LANGUAGE sql
    AS $_$
-- FUNÇÃO getparcelpayedvalue
-- Obtém o valor em aberto de uma parcela de uma pessoa de um peréodo letivo
-- Parémetros: personid bigint, parcelnumber integer, learningperiodid integer
-- Retorno: valor em aberto
    SELECT ROUND( (SELECT sum(balanceWithPoliciesDated(AA.invoiceId, now()::date))
                                 FROM finReceivableInvoice AA 
                                WHERE AA.personId = $1 
                                  AND AA.parcelnumber = $2 
                                  AND EXISTS (SELECT BB.entryid 
                                                FROM finEntry BB
                                          INNER JOIN finOperation CC
                                                  ON (CC.operationId = BB.operationId) 
                                               WHERE BB.invoiceId = AA.invoiceId 
                                                 AND BB.learningperiodid = $3 
                                                 AND CC.operationgroupid = (SELECT value 
                                                                              FROM basconfig 
                                                                             WHERE parameter LIKE 'MONTHLY_FEE_OPERATION_GROUP_ID') ) 
                  ), 
                   (SELECT value FROM basconfig WHERE parameter LIKE 'REAL_ROUND_VALUE')::int )
$_$;
