CREATE OR REPLACE FUNCTION cr_fin_saldo_da_conta(tipo int, p_accountschemeid varchar, p_costcenterid varchar, p_data_inicial date, p_data_final date, p_operacao CHAR)
RETURNS NUMERIC
AS $BODY$
/**************************************************************************************
NOME: cr_fin_saldo_da_conta
PURPOSE: Retorna o saldo da conta contábil somando todos os lançamentos das contas
filhos da mesma.
Recebe os filtros de tipo (1 - caixa, 2 - competencia), código da conta, data inicial, final e a operação que deve ser
D-Débito, C-credito ou NULL ambos, retornando o saldo da conta

REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        20/02/2015 Jonas G. Diel               Função criada.

**************************************************************************************/
DECLARE
    v_saldo_lancado NUMERIC;
    v_saldo_filhos NUMERIC;
    v_saldo NUMERIC;
    v_centro_de_custo RECORD;
BEGIN
    v_saldo_filhos := 0;
    v_saldo_lancado := 0;
    v_saldo := 0;

    FOR v_centro_de_custo IN 
        (SELECT costcenterid
           FROM accCostCenter 
          WHERE parentCostCenterId = p_costcenterid)
    LOOP
        --Calcula saldo das contas filhos
        v_saldo_filhos := v_saldo_filhos + cr_fin_saldo_da_conta(tipo, p_accountschemeid, v_centro_de_custo.costcenterid, p_data_inicial, p_data_final, p_operacao);
    END LOOP;

    --Calcula saldo lancado na conta
    SELECT INTO v_saldo_lancado
                CASE WHEN p_operacao IS NOT NULL THEN
                    COALESCE(SUM(lancamentos.valor_lancamento), 0)
                ELSE
                    COALESCE(SUM(lancamentos.valor_lancamento*(CASE WHEN lancamentos.cod_tipo_operacao_lancamento = 'D' THEN -1 ELSE 1 END)), 0)
                END
           FROM cr_fin_lancamentos_caixa_competencia(tipo, p_data_inicial, p_data_final, NULL, p_accountschemeid, p_costcenterid, p_operacao) as lancamentos
          WHERE (CASE WHEN p_accountschemeid IS NULL
                      THEN
                           cod_plano_de_contas IS NULL
                      ELSE
                           TRUE
                 END);

    v_saldo := v_saldo + v_saldo_filhos + v_saldo_lancado;

    RETURN v_saldo;
END;
$BODY$
LANGUAGE 'plpgsql';
