CREATE OR REPLACE FUNCTION cr_acc_lancamentos_individuais(p_data_inicial_contabil date, p_data_final_contabil date)
RETURNS setof DadosContabeis 
AS $BODY$
/**************************************************************************************
NOME: cr_lancamentos_individuais
:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        20/02/2015 ftomasini                   #38683

**************************************************************************************/

BEGIN
RETURN QUERY (
    SELECT data_contabil_lancamento,
           data_caixa,
           data_competencia,
           codigo_aluno,
           nome_aluno,
           titulo,
           parcela,
           vencimento,
           valor_lancamento,
           operacao,
           'D'::char(1) AS tipo_operacao, 
           codigo_centro_de_custo, 
           centro_de_custo,
           codigo_conta_contabil,
           codigo_contra_partida,
           observacoes_titulo,
           origem,
           codigo_local_pagamento,
           local_pagamento,
           usuario_do_lancamento,
           lancamento_de_caixa,
           nosso_numero,
           data_envio_ao_banco,
           ocorrencia_bancaria,
           modulo_de_vinculo,
           curso,
           periodo_academico,
           data_inicial,
           mes_ano_inicial,
           data_final,
           mes_ano_final,
           codigo_do_centro,
           nome_do_centro,
           codigo_do_lancamento,
           codigo_da_operacao,
           codigo_do_retorno_bancario,
           codigo_unidade,
           unidade,
           data_inicial_competencia,
           codigo_interno_nota_fiscal,
           numero_nota_fiscal,
           titulo_possui_atrelamento_com_nfe,
           observacao_da_operacao,
           movimentacaochequeid
      FROM cr_fin_lancamentos_contasreceber(p_data_inicial_contabil::date,  p_data_final_contabil::date, 'CO'::bpchar) 
     UNION
    SELECT data_contabil_lancamento,
           data_caixa,
           data_competencia,
           codigo_aluno,
           nome_aluno,
           titulo,
           parcela,
           vencimento,
           valor_lancamento,
           operacao,
           'C'::char(1) AS tipo_operacao, 
           codigo_centro_de_custo, 
           centro_de_custo,
           codigo_conta_contabil,
           codigo_contra_partida,
           observacoes_titulo,
           origem,
           codigo_local_pagamento,
           local_pagamento,
           usuario_do_lancamento,
           lancamento_de_caixa,
           nosso_numero,
           data_envio_ao_banco,
           ocorrencia_bancaria,
           modulo_de_vinculo,
           curso,
           periodo_academico,
           data_inicial,
           mes_ano_inicial,
           data_final,
           mes_ano_final,
           codigo_do_centro,
           nome_do_centro,
           codigo_do_lancamento,
           codigo_da_operacao,
           codigo_do_retorno_bancario,
           codigo_unidade,
           unidade,
           data_inicial_competencia,
           codigo_interno_nota_fiscal,
           numero_nota_fiscal,
           titulo_possui_atrelamento_com_nfe,
           observacao_da_operacao,
           movimentacaochequeid
      FROM cr_fin_lancamentos_contasreceber(p_data_inicial_contabil::date,  p_data_final_contabil::date, 'CO'::bpchar)
    );
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
