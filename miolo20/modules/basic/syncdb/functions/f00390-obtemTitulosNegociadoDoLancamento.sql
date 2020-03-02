CREATE OR REPLACE FUNCTION obtemTitulosNegociadoDoLancamento(p_entryId integer)
  RETURNS TABLE (invoiceId INTEGER) AS
$BODY$
/******************************************************************************
  NAME: obtemTitulosNegociadoDoLancamento
  DESCRIPTION: Retorna o número do título que foi negociado, caso o lançamento
  tenha sido negociado.

  REVISIONS:
  Ver       Date        Author                Description
  --------- ----------  ------------------    ---------------------------------
  1.0       13/04/2015  Luís Felipe Wermann   1. Função criada.
******************************************************************************/
DECLARE
v_sql TEXT;

BEGIN

    v_sql := 'SELECT C.invoiceId
                FROM finEntry A
          INNER JOIN fin.NegotiationGeneratedEntries B
                  ON (A.entryId = B.entryId)
     INNER JOIN ONLY finReceivableInvoice C
                  ON (A.invoiceId = C.invoiceId)
               WHERE B.generated IS FALSE
                 AND B.negotiationId IN (SELECT negotiationId
                                           FROM fin.NegotiationGeneratedEntries
                                          WHERE entryId = ' || p_entryId || '
                                            AND generated)';

RETURN QUERY EXECUTE v_sql;

END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE;
