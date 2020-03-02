CREATE OR REPLACE FUNCTION cr_fin_balancete_por_centro(tipo INT, p_data_inicial DATE, p_data_final DATE)
RETURNS TABLE(
    cod_centro_de_custo VARCHAR, 
    centro_de_custo TEXT, 
    credito NUMERIC, 
    debito NUMERIC, 
    saldo NUMERIC,
    saldo_total NUMERIC,
    percentual_conta_baseado_no_total NUMERIC
) AS 
$BODY$
/**************************************************************************************
NOME: cr_fin_balancete_por_centro
PURPOSE: Retorna os dados para balancete por centro de custo, informando
totais de crédito, débito, saldo.
Recebe os filtros de data inicial, final e o tipo deve ser pela data de caixa ou
competência: 1 - De Caixa ou 2 - De competência, NULL-Ambos.

REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        20/02/2015 Jonas G. Diel               Função criada.
2.0        19/06/2015 Augusto A. Silva            Ajustada performance, 
                                                  e incrementadas informações extras.
**************************************************************************************/
DECLARE
    v_sql TEXT;
BEGIN
    v_sql := 'SELECT lancamentos.cod_centro_de_custo, 
                     lancamentos.centro_de_custo,
                     SUM(lancamentos.credito) AS credito,
                     SUM(lancamentos.debito) AS debito,
                     SUM(lancamentos.saldo) as saldo,
                     lancamentos.saldo_total,
                     SUM(lancamentos.percentual_conta_baseado_no_total) AS percentual_conta_baseado_no_total --Foi necessário utilizar o sum, pois estava trazendo uma linha duplicada com zero (0.0)??
                FROM cr_fin_balancete_contabil_por_centro('''||tipo||''', '''||p_data_inicial||''', '''||p_data_final||''') lancamentos
            GROUP BY lancamentos.cod_centro_de_custo, 
                     lancamentos.centro_de_custo,
                     lancamentos.saldo_total
            ORDER BY lancamentos.cod_centro_de_custo';

    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql';
