CREATE OR REPLACE FUNCTION obterDescricaoOcorrenciaLogMovimentacao(p_ocorrencia CHAR, p_bankid CHAR)
RETURNS TEXT AS
$BODY$
/******************************************************************************
  NAME: obterDescricaoOcorrenciaLogMovimentacao
  DESCRIPTION: Retorna a descricao da ocorrência concatenado com a descrição da mesma.
              SOMENTE se for do banco 041 BANRISUL

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       29/05/2015 João F. P. Souza       1. Função criada.
******************************************************************************/
BEGIN
RETURN (
CASE WHEN ( p_ocorrencia = '02' AND p_bankid = '041' )
         THEN
             'Confirmação de Entrada'
    WHEN ( p_ocorrencia = '03'  AND p_bankid = '041' )
         THEN
             'Entrada rejeitada'
    WHEN ( p_ocorrencia = '04'  AND p_bankid = '041' )
         THEN
             'Baixa de título liquidado por edital'
    WHEN ( p_ocorrencia = '06'  AND p_bankid = '041' )
         THEN
             'Liquidação normal'
    WHEN ( p_ocorrencia = '07'  AND p_bankid = '041' )
         THEN
             'Liquidação parcial'
    WHEN ( p_ocorrencia = '08'  AND p_bankid = '041' )
         THEN
             'Baixa por pagamento, liquidação pelo saldo'
    WHEN ( p_ocorrencia = '09'  AND p_bankid = '041' )
         THEN
             'Devolução automática'
    WHEN ( p_ocorrencia = '10'  AND p_bankid = '041' )
         THEN
             'Baixado conforme instruções'
    WHEN ( p_ocorrencia = '11'  AND p_bankid = '041' )
         THEN
             'Arquivo levantamento'
    WHEN ( p_ocorrencia = '12'  AND p_bankid = '041' )
         THEN
             'Concessão de abatimento'
    WHEN ( p_ocorrencia = '13'  AND p_bankid = '041' )
         THEN
             'Cancelamento de abatimento'
    WHEN ( p_ocorrencia = '14'  AND p_bankid = '041' )
         THEN
             'Vencimento alterado'
    WHEN ( p_ocorrencia = '15'  AND p_bankid = '041' )
         THEN
             'Pagamento em cartório'
    WHEN ( p_ocorrencia = '16'  AND p_bankid = '041' )
         THEN
             'Alteração de dados'
    WHEN ( p_ocorrencia = '18'  AND p_bankid = '041' )
         THEN
             'Alterações de instruções'
    WHEN ( p_ocorrencia = '19'  AND p_bankid = '041' )
         THEN
             'Confirmação de instrução protesto'
    WHEN ( p_ocorrencia = '20'  AND p_bankid = '041' )
         THEN
             'Confirmação de instrução para sustar protesto'
    WHEN ( p_ocorrencia = '21'  AND p_bankid = '041' )
         THEN
             'Aguardando autorização para protesto por edital'
    WHEN ( p_ocorrencia = '22'  AND p_bankId = '041' )
         THEN
             'Protesto sustado por alteração de vencimento e prazo de cartório'
    WHEN ( p_ocorrencia = '23'  AND p_bankId = '041' )
         THEN
             'Confirmação de entrada em cartório'
    WHEN ( p_ocorrencia = '25'  AND p_bankId = '041' )
         THEN
             'Devolução, liquidado anteriormente'
    WHEN ( p_ocorrencia = '26'  AND p_bankId = '041' )
         THEN
             'Devolvido pelo cartório - erro de informação'
    WHEN ( p_ocorrencia = '30'  AND p_bankId = '041' )
         THEN
             'Cobrança a creditar (liquidação em trãnsito)'
    WHEN ( p_ocorrencia = '31'  AND p_bankId = '041' )
         THEN
             'Título em trãnsito pago pelo cartório'
    WHEN ( p_ocorrencia = '32'  AND p_bankId = '041' )
         THEN
             'Reembolso e transferência Desconto e Vendor ou carteira em garantia'
    WHEN ( p_ocorrencia = '33'  AND p_bankId = '041' )
         THEN
             'Reembolso e devolução Desconto e Vendor'
    WHEN ( p_ocorrencia = '34'  AND p_bankId = '041' )
         THEN
             'Reembolso não efetuado por falta de saldo'
    WHEN ( p_ocorrencia = '40'  AND p_bankId = '041' )
         THEN
             'Baixa de títulos protestados'
    WHEN ( p_ocorrencia = '42'  AND p_bankId = '041' )
         THEN
             'Alteração de título'
    WHEN ( p_ocorrencia = '43'  AND p_bankId = '041' )
         THEN
             'Relação de títulos'
    WHEN ( p_ocorrencia = '44'  AND p_bankId = '041' )
         THEN
             'Manutenção mensal'
    WHEN ( p_ocorrencia = '45'  AND p_bankId = '041' )
         THEN
             'Sustação de cartório e envio de título a cartório'
    WHEN ( p_ocorrencia = '46'  AND p_bankId = '041' )
         THEN
             'Fornecimento de formulário pré-impresso'
    WHEN ( p_ocorrencia = '47'  AND p_bankId = '041' )
         THEN
             'Confirmação de entrada - Pagador DDA'
    WHEN ( p_ocorrencia = '68'  AND p_bankId = '041' )
         THEN
             'Acerto dos dados do rateio de crédito'
    WHEN ( p_ocorrencia = '69'  AND p_bankId = '041' )
         THEN
             'Cancelamento dos dados do rateio'
         ELSE
             ''
    END
);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;