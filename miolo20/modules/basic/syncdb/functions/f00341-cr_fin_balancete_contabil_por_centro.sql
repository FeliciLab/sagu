CREATE OR REPLACE FUNCTION cr_fin_balancete_contabil_por_centro(tipo INT, p_data_inicial DATE, p_data_final DATE)
RETURNS TABLE(
    cod_plano_de_contas VARCHAR, 
    plano_de_contas TEXT, 
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
NOME: cr_fin_balancete_contabil_por_centro
PURPOSE: Retorna os dados para balancete contábil e por centro de custo, informando
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
    v_sql := 'SELECT Z.accountschemeid,
                     Z.plano_de_contas,
                     Z.costcenterid,
                     Z.centro_de_custo,
                     Z.credito,
                     Z.debito,
                     Z.saldo_da_conta,       
                     ROUND((SUM((CASE WHEN Z.codigo_centro_de_custo_pai IS NULL 
                                      THEN 
                                           Z.saldo_da_conta
                                      ELSE 
                                           0
                                 END)) OVER()), getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT) AS saldo_total,
                     
                     ROUND((CASE WHEN Z.saldo_da_conta > 0
                                 THEN 
                                      ((Z.saldo_da_conta * 100) / (SUM((CASE WHEN Z.codigo_centro_de_custo_pai IS NULL 
                                                                             THEN 
                                                                                  Z.saldo_da_conta
                                                                             ELSE 
                                                                                  0
                                                                        END)) OVER()))
                                 ELSE
                                      0
                            END), getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT) AS percentual_conta_baseado_no_total
                FROM (SELECT accaccountscheme.accountschemeid, 
                             accaccountscheme.description as plano_de_contas,
                             acccostcenter.costcenterid, 
                             acccostcenter.description as centro_de_custo,
                             acccostcenter.parentcostcenterid AS codigo_centro_de_custo_pai,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', accaccountscheme.accountschemeid, acccostcenter.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', ''C'')
                                   ELSE
                                        0.0
                              END) as credito,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', accaccountscheme.accountschemeid, acccostcenter.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', ''D'')
                                   ELSE
                                        0.0
                              END) as debito,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', accaccountscheme.accountschemeid, acccostcenter.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', null)
                                   ELSE
                                        0.0
                              END) as saldo_da_conta
                        FROM accaccountscheme
                   LEFT JOIN acccostcenter 
                          ON 1 = 1 --Cruza todas contas com todos centros
                   LEFT JOIN (SELECT TRUE AS possui_lancamentos,
                                     cod_plano_de_contas,
                                     cod_centrodecusto
                                FROM cr_fin_lancamentos_caixa_competencia('''||tipo||''', '''||p_data_inicial||''', '''||p_data_final||''', NULL, NULL, NULL, NULL)) X
                          ON X.cod_plano_de_contas = accaccountscheme.accountschemeid
                         AND EXISTS (SELECT costcenter.costcenterid
                                       FROM connectby(''acccostcenter'', ''costcenterid'', ''parentcostcenterid'', ''costcenterid'', acccostcenter.costcenterid, 0, '' > '') AS
                                            costcenter(costcenterid text, parentcostcenterid text, level int, branch text, pos int)
                                      WHERE costcenter.costcenterid = X.cod_centrodecusto)
                    GROUP BY accaccountscheme.accountschemeid, 
                             accaccountscheme.description,
                             acccostcenter.costcenterid, 
                             acccostcenter.description, 
                             acccostcenter.parentcostcenterid, 
                             X.possui_lancamentos
                             
                   UNION ALL

                      --Lançamentos sem planos atrelados, somente atrelados a centros de custos
                      SELECT NULL AS accountschemeid,
                             NULL AS plano_de_contas,
                             CC.costcenterid,
                             CC.description AS centro_de_custo,
                             CC.parentcostcenterid AS codigo_centro_de_custo_pai,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', NULL, CC.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', ''C'')
                                   ELSE
                                        0.0
                              END) as credito,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', NULL, CC.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', ''D'')
                                   ELSE
                                        0.0
                              END) as debito,
                             (CASE WHEN X.possui_lancamentos
                                   THEN 
                                        cr_fin_saldo_da_conta('''||tipo||''', NULL, CC.costcenterid,'''||p_data_inicial||''', '''||p_data_final||''', NULL)
                                   ELSE
                                        0.0
                              END) as saldo_da_conta
                        FROM accCostCenter CC
                   LEFT JOIN (SELECT MAX(cod_lancamento),
                                     TRUE AS possui_lancamentos,
                                     cod_plano_de_contas,
                                     cod_centrodecusto
                                FROM cr_fin_lancamentos_caixa_competencia('''||tipo||''', '''||p_data_inicial||''', '''||p_data_final||''', NULL, NULL, NULL, NULL)
                            GROUP BY possui_lancamentos,
                                     cod_plano_de_contas,
                                     cod_centrodecusto) X
                          ON X.cod_centrodecusto = CC.costcenterId
                         AND X.cod_plano_de_contas IS NULL
                         AND EXISTS (SELECT costcenter.costcenterid
                                       FROM connectby(''acccostcenter'', ''costcenterid'', ''parentcostcenterid'', ''costcenterid'', CC.costcenterid, 0, '' > '') AS
                                            costcenter(costcenterid text, parentcostcenterid text, level int, branch text, pos int)
                                      WHERE costcenter.costcenterid = CC.costcenterId)) Z
            GROUP BY Z.accountschemeid,
                     Z.plano_de_contas,
                     Z.costcenterid,
                     Z.centro_de_custo,
                     Z.credito,
                     Z.debito,
                     Z.saldo_da_conta,
                     Z.codigo_centro_de_custo_pai
            ORDER BY Z.accountschemeid, 
                     Z.costcenterid';

    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql'; 
