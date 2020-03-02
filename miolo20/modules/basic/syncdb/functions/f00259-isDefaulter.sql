CREATE OR REPLACE FUNCTION isDefaulter(p_personid bigint)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: isDefaulter
  PURPOSE: Retorna TRUE (FALSE) se a pessoa informada (não) esté em débito.
  DESCRIPTION:
  Estar em débito significa ter tétulos jé vencidos com valor maior do que zero.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       08/12/2010 Alexandre Schmidt 1. FUNÇÃO criada.
  1.1       18/09/2014 ftomasini         1. Alterada função para verificar apenas
                                            títulos que não estão cancelados
******************************************************************************/
    -- verifica se a pessoa informada possui algum
    -- tétulo com data de vencimento jé passada e com
    -- saldo maior do que zero.
    SELECT COUNT(*) > 0
      FROM finInvoice X
     WHERE X.personId = $1
       AND X.maturityDate < now()
       AND X.balance > 0
       AND x.iscanceled = FALSE
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION isdefaulter(bigint)
  OWNER TO postgres;
