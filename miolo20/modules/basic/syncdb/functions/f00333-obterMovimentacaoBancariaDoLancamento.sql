CREATE OR REPLACE FUNCTION obterMovimentacaoBancariaDoLancamento(p_lancamentoId INT, p_tipoConta INT)
RETURNS SETOF fin.bankMovement AS
$BODY$
/******************************************************************************
  NAME: obterMovimentacaoBancariaDoLancamento
  DESCRIPTION: Retorna um registro completo de movimentação bancaria, referente
               ao lançamento financeiro recebido por parâmetro, caso possua. Se
               o lançamento não possuir movimentação bancária, serão retornados 
               todos os registros em branco (NULL).

  PARAMETERS: p_lancamentoId (finEntry.entryId, capLancamento.lancamentoId)
	      p_tipoConta (1 = 'Conta a receber', 2 = 'Conta a pagar')

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       12/03/2015 Augusto A. Silva      1. Função criada.
******************************************************************************/
DECLARE
    v_entry RECORD;
    v_bankMovement fin.bankMovement;
BEGIN
    IF p_tipoConta = 1
    THEN
        SELECT INTO v_entry 
		    E.dateTime,
		    E.entryId,
		    E.invoiceId,
		    O.operationTypeId,
		    E.value,
		    E.bankmovementid
	       FROM finEntry E
         INNER JOIN finOperation O
	      USING (operationId)
	      WHERE E.entryId = p_lancamentoId;
    ELSE
        SELECT INTO v_entry 
		    L.dateTime,
		    L.lancamentoId AS entryId,
		    L.tituloId AS invoiceId,
    		    L.tipolancamento AS operationTypeId,
		    L.valor AS value,
		    0 AS bankmovementid
	       FROM capLancamento L
              WHERE L.lancamentoId = p_lancamentoId;
    END IF;

    IF v_entry.bankmovementid IS NOT NULL AND v_entry.bankmovementid <> 0
    THEN
        RETURN QUERY (
             SELECT  *
	       FROM fin.bankMovement
	      WHERE bankMovementId = v_entry.bankmovementid
                /**
                 * A validação abaixo NÃO PODE ser retirada, do jeito que ela trabalha vai buscar somente PAGAMENTOS,
                 * isto está correto, pois eu quero saber apenas o que o aluno tirou do bolso para pagar no banco
                 * e se retirarmos essa validação as TAXAS BANCÁRIAS, DESCONTOS e outros registros virão junto.
                 */
                AND (CASE getParameter('FINANCE', 'ENABLED_EXPLENDITURE_SEPARATION')::BOOLEAN
			  WHEN TRUE
			  THEN 
			       (ROUND((valuepaid - expenditure)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT))
			  ELSE
			       (ROUND((valuepaid + expenditure)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT))
		     END));
    ELSE
        RETURN QUERY (
             SELECT  *
	       FROM fin.bankMovement
	      WHERE (CASE p_tipoConta
                          WHEN 1 
                          THEN
                               (invoiceId = v_entry.invoiceId)
                          ELSE
                               (tituloId = v_entry.invoiceId)
                     END)
		AND (CASE p_tipoConta
                          WHEN 1
                          THEN
                               (CASE getParameter('FINANCE', 'ENABLED_EXPLENDITURE_SEPARATION')::BOOLEAN
                                     WHEN TRUE
                                     THEN 
                                          (ROUND((valuepaid - expenditure)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT))
                                     ELSE
                                          (ROUND((valuepaid + expenditure)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT))
                                END)
                          ELSE
                                --Quando for uma movimentação bancária feita pelo contas a pagar, o valor da movimentação estará negativa. Inverter para comparação.
                               (ROUND((value * -1)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT))
                     END)
                AND TO_CHAR(datetime, getParameter('BASIC', 'MASK_TIMESTAMP')) = TO_CHAR(v_entry.dateTime, getParameter('BASIC', 'MASK_TIMESTAMP')));
    END IF;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
