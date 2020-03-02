CREATE OR REPLACE VIEW rptregimecaixa AS
        SELECT * 
          FROM cr_fin_lancamentos_caixa_competencia(1, '1900-01-01', '2200-01-01', NULL, NULL, NULL, NULL);

COMMENT ON VIEW rptregimecaixa IS '
NOME:
rptregimecaixa
DESCRICAO:
No Regime de Caixa, consideramos o registro dos documentos na data que foram pagos ou recebidos,
como se fosse uma conta bancária.

REVISÕES:
1.0 - ftomasini - 17/09/2014
';
COMMENT ON COLUMN rptregimecaixa.username IS 'Usuário que efetuou o lançamento';
COMMENT ON COLUMN rptregimecaixa.datetime IS 'Timestamp em que o usuário efetuou o lançamento';
COMMENT ON COLUMN rptregimecaixa.ipaddress IS 'Endereço IP do usuário que efetuou o lançamento';
COMMENT ON COLUMN rptregimecaixa.cod_lancamento IS 'Código do lançamento';
COMMENT ON COLUMN rptregimecaixa.comentario_lancamento IS 'Comentário do lançamento';
COMMENT ON COLUMN rptregimecaixa.valor_lancamento IS 'Valor do lançamento';
COMMENT ON COLUMN rptregimecaixa.data_competencia_lancamento IS 'Data de competência do lançamento';
COMMENT ON COLUMN rptregimecaixa.data_referencia_lancamento IS 'Data de refencia do lançamento';
COMMENT ON COLUMN rptregimecaixa.data_efetivacao_lancamento IS 'Data em que o lançamento foi efetivado';
COMMENT ON COLUMN rptregimecaixa.cod_operacao_lancamento IS 'Codigo da operacao do lançamento';
COMMENT ON COLUMN rptregimecaixa.cod_tipo_operacao_lancamento IS 'Indica se o lançamento é de débito ou crédito';
COMMENT ON COLUMN rptregimecaixa.cod_centrodecusto IS 'Codigo do centro de custo do lancamento';
COMMENT ON COLUMN rptregimecaixa.cod_plano_de_contas IS 'Plano de contas do lançamento';
COMMENT ON COLUMN rptregimecaixa.origemlancamento IS 'Indica se o lançamento é relacionado ao contas a receber CR, contas a pagar CP, sem vinculo SV';
COMMENT ON COLUMN rptregimecaixa.cod_pessoa_ref IS 'Código da pessoa a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecaixa.cod_titulo_ref IS 'Código do título à qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecaixa.cod_contrato_ref IS 'Código do contrato a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecaixa.cod_incentivo_ref IS 'Código do incentivo a qual o lançamento está vinculado';
COMMENT ON COLUMN rptregimecaixa.cod_periodo_letivo_ref IS 'Código do período letivo a qual o lançamento está vinculado';
--
