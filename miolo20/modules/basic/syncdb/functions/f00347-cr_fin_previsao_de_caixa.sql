CREATE OR REPLACE FUNCTION cr_fin_previsao_de_caixa(p_data_inicial DATE, p_data_final DATE)
RETURNS TABLE(data DATE, total_a_pagar NUMERIC, total_a_receber NUMERIC, total_a_receber_atualizado NUMERIC, total_a_receber_incentivos NUMERIC)
AS $BODY$
/**************************************************************************************
NOME: cr_fin_previsao_de_caixa
PURPOSE: Obtém a previsão de caixa para o período de vencimento informado.

REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        20/02/2015 Jonas G. Diel               Função criada.

**************************************************************************************/
DECLARE
    v_sql TEXT;

BEGIN
    v_sql := '
       SELECT x.maturitydate as data,
       ROUND(COALESCE(SUM(x.pagar),0),2) as total_a_pagar, 
       ROUND(COALESCE(SUM(x.receber),0),2) as total_a_receber,
       ROUND(COALESCE(SUM(x.atualizado),0),2) as total_a_receber_atualizado,
       ROUND(COALESCE(SUM(x.receber_incentivos),0),2) as total_a_receber_incentivos
       FROM (SELECT titulo_receber.maturitydate,
                   null::numeric as pagar,
                   titulo_receber.balance as receber,
                   balancewithpoliciesdated(titulo_receber.invoiceid, titulo_receber.maturitydate) as atualizado,
                   obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(titulo_receber.invoiceid, getparameter(''FINANCE'', ''INCENTIVE_OPERATION_GROUP_ID''), TRUE) as receber_incentivos
              FROM finreceivableinvoice titulo_receber
              WHERE titulo_receber.balance != 0
                AND titulo_receber.maturitydate BETWEEN '''||p_data_inicial||''' AND '''||p_data_final||'''
                AND titulo_receber.isCanceled IS FALSE
             UNION ALL
            SELECT titulo_pagar.vencimento,
                   titulo_pagar.valoraberto as pagar,
                   null::numeric as receber,
                   null::numeric as atualizado,
                   null::numeric as receber_incentivos
              FROM captitulo titulo_pagar
              WHERE titulo_pagar.tituloaberto IS TRUE 
                AND titulo_pagar.vencimento BETWEEN '''||p_data_inicial||''' AND '''||p_data_final||'''
               ) AS x
GROUP BY x.maturitydate
ORDER BY x.maturitydate';

    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql';
