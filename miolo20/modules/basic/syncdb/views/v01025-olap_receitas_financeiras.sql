CREATE OR REPLACE VIEW olap_receitas_financeiras AS (
SELECT A.invoiceId AS COD_TITULO,
           A.personId AS COD_PESSOA,
           B.name AS PESSOA,
           (SELECT getContractCourseId(AA.contractId)
         FROM ONLY finEntry AA
             WHERE AA.invoiceId = A.invoiceId LIMIT 1) AS CURSO,
           TO_CHAR(A.maturityDate, 'MM/YYYY') AS COMPETENCIA,
           A.parcelNumber AS PARCELA,
           TO_CHAR(A.maturityDate, 'DD/MM/YYYY') AS DT_VENCIMENTO,
           (SELECT TO_CHAR(MAX(AA.entryDate), 'DD/MM/YYYY')
              FROM finEntry AA
             WHERE AA.invoiceId = A.invoiceId
               AND AA.operationId = (SELECT paymentoperation FROM findefaultoperations LIMIT 1) )AS DT_PAGAMENTO,
           ROUND(A.nominalvalue,2) AS VL_NOMINAL,
           (SELECT ROUND(SUM(BB.value),2)
              FROM finEntry BB
        INNER JOIN finOperation CC
                ON (BB.operationId = CC.operationId)
             WHERE BB.invoiceId = A.invoiceId
               AND CC.operationGroupId = 'J' ) AS VL_MULTAJUROS,
           (SELECT ROUND(SUM(DD.value),2)
              FROM finEntry DD
        INNER JOIN finOperation EE
                ON (DD.operationId = EE.operationId)
             WHERE DD.invoiceId = A.invoiceId
               AND EE.operationGroupId = 'D' ) AS VL_DESCONTO,
           (SELECT ROUND(SUM(FF.value),2)
              FROM finEntry FF
        INNER JOIN finOperation GG
                ON (FF.operationId = GG.operationId)
             WHERE FF.invoiceId = A.invoiceId
               AND GG.operationGroupId = 'P' ) AS VL_PAGO,
           ROUND(balance(A.invoiceId), 2) AS VL_DEVIDO,
           ROUND(balancewithpoliciesdated(A.invoiceId, now()::date),2) AS VL_CORRIGIDO
      FROM finReceivableInvoice A
INNER JOIN basPhysicalPerson B
        ON (A.personId = B.personId)
     WHERE A.isCanceled = 'f'
       AND EXISTS (SELECT 1
                     FROM finEntry HH
                    WHERE HH.invoiceId = A.invoiceId
                      AND HH.contractId IS NOT NULL)
  GROUP BY 1,2,3,4,5,6,7,8,9,10
ORDER BY 3 );
