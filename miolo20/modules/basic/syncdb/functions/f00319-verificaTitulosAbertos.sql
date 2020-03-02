CREATE OR REPLACE FUNCTION verificaTitulosAbertos()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaTitulosAbertos
  DESCRIPTION: Trigger que verifica se todas as disciplinas do período estão 
	       canceladas e se todos os títulos vinculados as disciplinas
	       canceladas do período não estão bloqueados, e executa o 
	       cancelamento deste títulos.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       02/02/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    -- Verifica se todas as disciplinas do período estão canceladas
    v_disciplinas_canceladas BOOLEAN;

    -- Recebe o learningperiodid referente a disciplina cancelada
    v_periodo INT;

    -- Verifica se todos os títulos vinculados as disciplinas do período estão abertos
    v_titulos_abertos BOOLEAN;

    -- Recebe o objeto da tabela acdlearningperiod
    v_periodo_letivo RECORD;
BEGIN

    -- Verifica se a disciplina está sendo cancelada e o módulo financeiro está instalado
    IF NEW.statusid = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT AND GETPARAMETER('BASIC', 'MODULE_FINANCE_INSTALLED') = 'YES'
    THEN
       SELECT INTO v_periodo learningPeriodId
              FROM acdGroup
             WHERE groupId = NEW.groupId;

       -- Verifica se o período letivo gera financeiro
       SELECT INTO v_periodo_letivo * FROM acdLearningPeriod WHERE learningPeriodId = v_periodo;

       IF v_periodo_letivo.isfinancegenerate IS TRUE
       THEN
	       --Verifica se todas as disciplinas do período estão canceladas
	       SELECT INTO v_disciplinas_canceladas 
		      (SELECT COALESCE(COUNT(*), 0)
			FROM acdEnroll A
		  INNER JOIN acdGroup B
			  ON A.groupId = B.groupId
		  INNER JOIN acdLearningPeriod C
			  ON C.learningPeriodId = B.learningPeriodId
		       WHERE A.contractId = NEW.contractId
			 AND C.periodId IN (SELECT periodId 
					      FROM acdLearningPeriod
					     WHERE learningPeriodId = v_periodo)) = (SELECT COALESCE(COUNT(*), 0)
											FROM acdEnroll A
										  INNER JOIN acdGroup B
											  ON A.groupId = B.groupId
										  INNER JOIN acdLearningPeriod C
											  ON C.learningPeriodId = B.learningPeriodId
										       WHERE A.contractId = NEW.contractId
											 AND C.periodId IN (SELECT periodId 
													      FROM acdLearningPeriod
													     WHERE learningPeriodId = v_periodo)
											 AND A.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT);
		-- Verifica se todos os títulos do período estão abertos
		SELECT INTO v_titulos_abertos
		       (SELECT COALESCE(COUNT(*), 0)
		     FROM ONLY finReceivableInvoice A
		    INNER JOIN finEntry B
			    ON A.invoiceId = B.invoiceId
			 WHERE B.contractId = NEW.contractId
			   AND B.learningPeriodId = v_periodo) = (SELECT COALESCE(COUNT(*), 0)
							       FROM ONLY finReceivableInvoice A
							      INNER JOIN finEntry B
								      ON A.invoiceId = B.invoiceId
								   WHERE B.contractId = NEW.contractId
								     AND B.learningPeriodId = v_periodo
								     AND NOT EXISTS (SELECT 1
										       FROM finhistoricoremessa
										      WHERE invoiceid = A.invoiceid)
								     AND A.balance > 0
								     AND A.maturityDate >= now()::DATE);
		
		IF v_disciplinas_canceladas IS TRUE AND v_titulos_abertos IS TRUE 
		THEN
	            UPDATE finInvoice AA
	               SET iscanceled = TRUE
	              FROM (SELECT A.invoiceId
		         FROM ONLY finReceivableInvoice A
		        INNER JOIN finEntry B
			        ON A.invoiceId = B.invoiceId
			     WHERE B.contractId = NEW.contractId
			       AND B.learningPeriodId = v_periodo) X
		     WHERE X.invoiceId = AA.invoiceId; 
		END IF;
	   END IF;
    END IF;
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_verificaTitulosAbertos ON acdEnroll;
CREATE TRIGGER trg_verificaTitulosAbertos
  AFTER INSERT OR UPDATE
  ON acdEnroll
  FOR EACH ROW
  EXECUTE PROCEDURE verificaTitulosAbertos();
