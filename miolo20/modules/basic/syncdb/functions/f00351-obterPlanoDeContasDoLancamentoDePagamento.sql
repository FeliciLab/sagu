CREATE OR REPLACE FUNCTION obterPlanoDeContasDoLancamentoDePagamento(p_invoiceid INT)
RETURNS VARCHAR AS
$BODY$
/*************************************************************************************
  NAME: obterPlanoDeContasDoLancamentoDePagamento
  PURPOSE: Retorna o plano de contas do lançamento de pagamento do título.
  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- -----------------         ------------------------------------
  1.0       09/03/2015 Luís Felipe Wermann       1. Função criada.
**************************************************************************************/
BEGIN

    RETURN (
    SELECT DISTINCT (COALESCE((CASE WHEN char_length(ACC.accountSchemeId) = 0
                                         THEN
                                             NULL
                                         ELSE
                                             ACC.accountSchemeId
                                    END),
                                   (CASE WHEN char_length(O.accountSchemeId) = 0
                                         THEN
                                             NULL
                                         ELSE
                                             O.accountSchemeId
                                    END),
                                    (CASE WHEN CM.counterMovementId IS NOT NULL
                                          THEN
                                              CO.accountSchemeId
                                          ELSE
                                          (CASE WHEN BK.bankMovementId IS NOT NULL
                                                THEN
                                                    BA.accountSchemeId
                                                ELSE
                                                    NULL
                                           END)
                                     END)) ) AS codigo_conta_contabil
              FROM finReceivableInvoice A
        INNER JOIN finEntry B
                ON (A.invoiceId = B.invoiceId)
        INNER JOIN finOperation O
                ON (B.operationId = O.operationId)
         LEFT JOIN acdContract C
                ON (C.contractId = B.contractId)
         LEFT JOIN accCourseAccount ACC
                ON (ACC.courseId,
                    ACC.courseVersion,
                    ACC.unitId) = (C.courseId,
                                   C.courseVersion,
                                   C.unitId)
         LEFT JOIN fin.BankMovement BK
                ON (B.bankMovementId = BK.bankMovementId)
         LEFT JOIN finBankAccount BA
                ON (A.bankAccountId = BA.bankAccountId)
         LEFT JOIN finCounterMovement CM
                ON (CM.invoiceId = A.invoiceId)
               AND (CM.value = B.value)
         LEFT JOIN finOpenCounter OC
                ON (CM.openCounterId = OC.openCounterId)
         LEFT JOIN finCounter CO
                ON (CO.counterId = OC.counterId)
             WHERE A.invoiceId = p_invoiceid
               AND B.operationId = (SELECT paymentOperation FROM finDefaultOperations LIMIT 1)
               ); --Quando a operação for de pagamento.
            
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
