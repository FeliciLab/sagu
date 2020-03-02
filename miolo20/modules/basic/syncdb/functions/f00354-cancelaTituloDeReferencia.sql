CREATE OR REPLACE FUNCTION cancelaTituloDeReferencia()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: cancelaTituloDeReferencia
  DESCRIPTION: Verifica se existe um título de referência e se o mesmo não está 
               bloqueado, extorna o lançamento e cancela o título.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       10/03/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    v_lancamento RECORD;
    v_operacao INT;
BEGIN
    -- Se o título está sendo cancelado e existe um título de referencia
    IF NEW.iscanceled IS TRUE AND NEW.titulodereferencia IS NOT NULL
    THEN
        -- Verifica se o título do financiamento não está bloqueado
	IF tituloBloqueado(NEW.titulodereferencia, true) IS FALSE
	THEN
            -- Obtém informações do lançamento
            FOR v_lancamento IN
		( SELECT * 
		    FROM finEntry
		   WHERE invoiceid = NEW.titulodereferencia
	       	     AND titulodereferencia = NEW.invoiceid
		     AND incentivetypeid IS NOT NULL )
	    LOOP
		SELECT INTO v_operacao repaymentoperation FROM finincentivetype WHERE incentivetypeid = v_lancamento.incentivetypeid;

		-- Insere lançamento com a operação cadastrada no incentivo contrária a de cobrança
		INSERT INTO finentry 
		            (invoiceid, 
			     operationid, 
		             entrydate, 
		             value, 
			     costcenterid, 
		             contractid, 
			     learningperiodid)
		     VALUES (NEW.titulodereferencia,
			     v_operacao,
			     now()::date,
			     ROUND(v_lancamento.value::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
			     v_lancamento.costcenterid,
			     v_lancamento.contractid,
			     v_lancamento.learningperiodid,
                             'LANCAMENTO REFERENTE AO CANCELAMENTO DO TÍTULO ' || NEW.invoiceid);
	        RAISE NOTICE 'LANCAMENTO INSERIDO NO TITULO DO FINANCIADOR: VALOR - % OPERACAO - % - TITULO - %', v_lancamento.value, v_operacao, NEW.titulodereferencia;
	    END LOOP;

            -- Se o saldo do título está zerado, cancela o título
	    IF balance(NEW.titulodereferencia) = 0
	    THEN
		UPDATE finInvoice SET iscanceled = TRUE WHERE invoiceId = NEW.titulodereferencia;
	    END IF;
        END IF;
    END IF;
	
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_cancelaTituloDeReferencia ON finInvoice;
CREATE TRIGGER trg_cancelaTituloDeReferencia
    BEFORE UPDATE ON finInvoice
    FOR EACH ROW
    EXECUTE PROCEDURE cancelaTituloDeReferencia();
