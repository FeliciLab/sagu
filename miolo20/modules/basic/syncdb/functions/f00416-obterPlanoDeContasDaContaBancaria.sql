CREATE OR REPLACE FUNCTION obterPlanoDeContasDaContaBancaria(p_bankaccountid integer)
  RETURNS TABLE(accountschemeid VARCHAR) AS
$BODY$
/*************************************************************************************
  NAME: obterPlanoDeContasDaContaBancaria
  PURPOSE: Retorna o plano de contas vinculado a conta bancária
 REVISIONS:
  Ver       Date        Author                  Description
  --------- ----------  -----------------       ------------------------------------
  1.0       11/06/2015  Nataniel I da Silva     1. FUNÇÂO criada.
**************************************************************************************/
DECLARE
    v_bankAccount VARCHAR;
BEGIN
    RETURN QUERY ( SELECT A.accountschemeid
                     FROM finbankaccount A
                    WHERE A.bankaccountid = p_bankaccountid );
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
