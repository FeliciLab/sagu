--
CREATE OR REPLACE FUNCTION verificaPossuiFinanceamentoEstudantil(p_personId INT)
RETURNS INT AS
$BODY$
/*************************************************************************************
  NAME: verificarFormaDeIngressoCursoCenso
  PURPOSE: Obtem os dados da forma de ingresso do aluno no curso.
**************************************************************************************/
DECLARE
    v_invoices RECORD;

BEGIN
    SELECT INTO v_invoices 
                E.invoiceId
           FROM finEntry E
          WHERE E.invoiceId IN ( SELECT invoiceId
                              FROM ONLY finReceivableInvoice
                                  WHERE personId = p_personId )
            AND E.operationId IN ( SELECT operationId
                                     FROM finloan );

     IF v_invoices IS NULL
     THEN
          RETURN 0;
     ELSE
          RETURN 1;
     END IF;
END;
$BODY$
LANGUAGE 'plpgsql';
--
