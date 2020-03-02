CREATE OR REPLACE FUNCTION cr_fin_balancete_contabil(tipo int, p_data_inicial date, p_data_final date)
RETURNS TABLE(cod_plano_de_contas varchar, plano_de_contas text, credito numeric, debito numeric, saldo numeric)
AS $BODY$
/**************************************************************************************
NOME: cr_fin_balancete_contabil
PURPOSE: Retorna os dados para balancete contábil, informando
totais de crédito, débito, saldo.
Recebe os filtros de data inicial, final e o tipo deve ser pela data de caixa ou
competência: 1 - De Caixa ou 2 - De competência, NULL-Ambos.

REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        20/02/2015 Jonas G. Diel               Função criada.

**************************************************************************************/
DECLARE
    v_sql text;
BEGIN
    v_sql := 'SELECT lancamentos.cod_plano_de_contas, 
           lancamentos.plano_de_contas,
           SUM(lancamentos.credito) AS credito,
           SUM(lancamentos.debito) AS debito,
           SUM(lancamentos.saldo) as saldo
      FROM cr_fin_balancete_contabil_por_centro('''||tipo||''', '''||p_data_inicial||''', '''||p_data_final||''') lancamentos
      GROUP BY 1,2
      ORDER BY 1';

      RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
