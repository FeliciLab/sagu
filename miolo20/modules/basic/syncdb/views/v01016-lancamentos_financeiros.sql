CREATE OR REPLACE VIEW lancamentos_financeiros AS
/*************************************************************************************
  NOME: view_invoice_values
  PROPOSTA:  Extratifica os valores contidos nos títulos conforme operação
  DESCRIÇÃO: Visão que seleciona os valores dos títulos extratificados por operação
  facilitando a visualização da informação e possibilidade de centralizar o relatório.

  REVISÕES:
  Ver       Data       Autor             Descrição
  --------- ---------- ----------------- ------------------------------------
  1.0       25/10/2012 Samuel Koch 1. Função criada.
**************************************************************************************/
--Obtem os lançamentos de Mensalidades, pagamento, juros e multa
    SELECT E.courseId,
           E.courseVersion,
           E.turnId,
           E.unitId,
           A.invoiceId,
           P.personid,
           P.name,
           C.operationtypeid,
           C.operationid,
           C.description AS operation,
           ROUND(B.value,2) AS value,
           A.maturityDate,
           B.entryDate,
           E.contractId,
           getcontractclassid(E.contractId) as classId,
           B.incentivetypeId
 FROM ONLY finReceivableInvoice A
INNER JOIN basPhysicalPerson P
        ON P.personId = A.personId
INNER JOIN finEntry B
        ON (A.invoiceId = B.invoiceId)
INNER JOIN finOperation C
        ON (B.operationId = C.operationId)
 LEFT JOIN acdContract E
        ON (E.contractId = COALESCE(B.contractid,getinvoicecontract(A.invoiceid)))
     WHERE A.isCanceled IS FALSE
       AND A.invoiceIdDEpendence IS NULL
  ORDER BY 1,5;
