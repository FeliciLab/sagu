CREATE OR REPLACE FUNCTION obtemPlanoDeContasRetornoDeTitulos(p_branch varchar, p_branchNumber varchar, p_operationId integer )
  RETURNS varchar AS
$BODY$
/******************************************************************************
  NAME: obtemPlanoDeContasRetornoDeTitulos
  DESCRIPTION: Obtém o plano de contas da conta bancária e se não encontrar 
  obtém da operação
  PARAMETROS: p_branchNumber Numero da conta bancaria (classe FinBankMovement)
              p_branch Numero da agencia (classe FinBankMovement)

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ------------------        ---------------------------------
  1.0       23/04/15   Nataniel I. da Silva      1. Trigger criada.
******************************************************************************/
DECLARE
    v_planoDeContas varchar;
BEGIN
    -- Tenta obter o plano de contas da conta bancária
    SELECT INTO v_planoDeContas accountschemeid
      FROM finbankaccount
     WHERE accountnumber = p_branchNumber
       AND (branchnumber = p_branch OR branchnumber = substring(p_branch, 2, 4)); --Ajustado para que pegue o que vem do retorno, ou dos quatro últimos dígitos

    -- Obtém o plano de contas da operação, caso não encontrou na conta bancária
    IF v_planoDeContas IS NULL
    THEN
        SELECT INTO v_planoDeContas accountschemeid
          FROM finoperation
         WHERE operationid = p_operationId;
    END IF;

    IF v_planoDeContas IS NULL
    THEN
        RAISE EXCEPTION 'Necessário cadastrar um plano de contas junto a conta bancária % e agência % ou na operação % para importar o retorno bancário.', p_branchNumber, p_branch, p_operationId;
    END IF;

    RETURN v_planoDeContas;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;