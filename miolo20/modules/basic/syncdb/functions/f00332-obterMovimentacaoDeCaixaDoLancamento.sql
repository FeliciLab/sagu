CREATE OR REPLACE FUNCTION obterMovimentacaoDeCaixaDoLancamento(p_lancamentoId INT, p_tipoConta INT)
RETURNS SETOF finCounterMovement AS
$BODY$
/******************************************************************************
  NAME: obterMovimentacaoDeCaixaDoLancamento
  DESCRIPTION: Retorna um registro completo de movimentação de caixa, referente
               ao lançamento financeiro recebido por parâmetro, caso possua. Se
               o lançamento não possuir movimentação de caixa, serão retornados 
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
    v_counterMovement finCounterMovement;
BEGIN
    IF p_tipoConta = 1
    THEN
        SELECT INTO v_entry 
		    E.dateTime,
		    E.entryId,
		    E.invoiceId,
		    O.operationTypeId,
		    E.value,
		    E.counterMovementId
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
		    0 AS counterMovementId
	       FROM capLancamento L
              WHERE L.lancamentoId = p_lancamentoId;
    END IF;

    IF v_entry.countermovementid IS NOT NULL AND v_entry.countermovementid <> 0
    THEN
        RETURN QUERY (
             SELECT  *
	       FROM finCounterMovement
	      WHERE counterMovementId = v_entry.countermovementid);
    ELSE
        RETURN QUERY ( 
             SELECT  *
	       FROM finCounterMovement
	      WHERE (CASE p_tipoConta
                          WHEN 1 
                          THEN
                               (invoiceId = v_entry.invoiceId)
                          ELSE
                               (tituloId = v_entry.invoiceId)
                     END)
	        AND ((operation = v_entry.operationTypeId 
	        AND ROUND(value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND(v_entry.value, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT))
		 OR (operation <> v_entry.operationTypeId
		AND ROUND(value::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT) = ROUND((v_entry.value * -1)::NUMERIC, getparameter('BASIC', 'REAL_ROUND_VALUE')::INT)))
		AND TO_CHAR(datetime, getParameter('BASIC', 'MASK_TIMESTAMP')) = TO_CHAR(v_entry.dateTime, getParameter('BASIC', 'MASK_TIMESTAMP')) limit 1 );
    END IF;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
