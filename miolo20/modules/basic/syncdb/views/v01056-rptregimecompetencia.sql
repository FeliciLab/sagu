CREATE OR REPLACE VIEW rptregimecompetencia AS
        SELECT * 
          FROM cr_fin_lancamentos_caixa_competencia(2, '1900-01-01', '2200-01-01', NULL, NULL, NULL, NULL);


COMMENT ON VIEW rptregimecompetencia IS '
NOME:
rptregimecompetencia
DESCRICAO:
No Regime de Competência, o registro do documento se dá na data que o evento aconteceu.
Este evento pode ser uma entrada (venda) ou uma saída (despesas e custos).
A contabilidade define o Regime de Competência como sendo o registro do documento na data do fato gerador (ou seja,
na data do documento, não importando quando vou pagar ou receber).
A Contabilidade utiliza o Regime de Competência, ou seja, as Receitas ou Despesas tem os valores contabilizados
dentro do mês onde ocorreu o fato Gerador, isto é, na data da realização do serviço, compra do material,
da venda, do desconto, não importando para a Contabilidade quando vou pagar ou receber, mas sim quando foi realizado o ato.

REVISÕES:
1.0 - ftomasini - 17/09/2014';

COMMENT ON COLUMN rptregimecompetencia.username IS 'Usuário que efetuou o lançamento';
COMMENT ON COLUMN rptregimecompetencia.datetime IS 'Timestamp em que o usuário efetuou o lançamento';
COMMENT ON COLUMN rptregimecompetencia.ipaddress IS 'Endereço IP do usuário que efetuou o lançamento';
COMMENT ON COLUMN rptregimecompetencia.cod_lancamento IS 'Código do lançamento';
COMMENT ON COLUMN rptregimecompetencia.comentario_lancamento IS 'Comentário do lançamento';
COMMENT ON COLUMN rptregimecompetencia.valor_lancamento IS 'Valor do lançamento';
COMMENT ON COLUMN rptregimecompetencia.data_competencia_lancamento IS 'Data de competência do lançamento';
COMMENT ON COLUMN rptregimecompetencia.data_referencia_lancamento IS 'Data de refencia do lançamento';
COMMENT ON COLUMN rptregimecompetencia.data_efetivacao_lancamento IS 'Data em que o lançamento foi efetivado';
COMMENT ON COLUMN rptregimecompetencia.cod_operacao_lancamento IS 'Codigo da operacao do lançamento';
COMMENT ON COLUMN rptregimecompetencia.cod_tipo_operacao_lancamento IS 'Indica se o lançamento é de débito ou crédito';
COMMENT ON COLUMN rptregimecompetencia.cod_centrodecusto IS 'Codigo do centro de custo do lancamento';
COMMENT ON COLUMN rptregimecompetencia.cod_plano_de_contas IS 'Plano de contas do lançamento';
COMMENT ON COLUMN rptregimecompetencia.origemlancamento IS 'Indica se o lançamento é relacionado ao contas a receber CR, contas a pagar CP, sem vinculo SV';
COMMENT ON COLUMN rptregimecompetencia.cod_pessoa_ref IS 'Código da pessoa a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecompetencia.cod_titulo_ref IS 'Código do título à qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecompetencia.cod_contrato_ref IS 'Código do contrato a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecompetencia.cod_incentivo_ref IS 'Código do incentivo a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecompetencia.cod_periodo_letivo_ref IS 'Código do período letivo a qual o lançamento está vinculado';

