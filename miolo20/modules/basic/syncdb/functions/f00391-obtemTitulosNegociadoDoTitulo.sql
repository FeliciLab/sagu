CREATE OR REPLACE FUNCTION obtemTitulosNegociadoDoTitulo(p_invoiceId integer)
  RETURNS TABLE (invoiceId INTEGER) AS
$BODY$
/******************************************************************************
  NAME: obtemTitulosNegociadoDoTitulo
  DESCRIPTION: Retorna o número do título que foi negociado, caso o título
  tenha algum lançamento originário de negociação.

  REVISIONS:
  Ver       Date        Author                Description
  --------- ----------  ------------------    ---------------------------------
  1.0       13/04/2015  Luís Felipe Wermann   1. Função criada.
******************************************************************************/
DECLARE
v_sql TEXT;

BEGIN

    v_sql := 'SELECT obtemTitulosNegociadoDoLancamento(A.entryId) AS invoiceId
                FROM finEntry A
               WHERE A.invoiceId = ' || p_invoiceId;

RETURN QUERY EXECUTE v_sql;

END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE;