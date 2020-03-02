CREATE OR REPLACE FUNCTION sincronizarLancamentoNasMovimentacoes()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: sincronizarLancamentoNasMovimentacoes
  DESCRIPTION: Verifica movimentação bancária/de caixa atrelada ao lançamento
  e, ao editar, ajusta nas devidas tabelas.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       16/03/15   Luís F. Wermann       1. Trigger criada.
  1.1       24/04/15   Nataniel I. da Silva  1.1 Trigger ajustada para não ocorrer
  erro na exclusão de um lançamento.
******************************************************************************/
BEGIN
    IF TG_OP = 'UPDATE' 
    THEN
        --Se veio de retorno bancário (possui nosso número), não pode ser editado/excluído
        IF ( OLD.bankReturnCode IS NOT NULL)
        THEN
            RAISE EXCEPTION 'O lançamento % não pode ser alterado/excluído pois é oriundo de um retorno bancário.', OLD.entryId;
        END IF;

        --Se tinha movimentação de caixa e recebe nova movimentação de banco
        IF ( OLD.counterMovementId IS NOT NULL AND
             NEW.bankMovementId IS NOT NULL ) 
        THEN
            --Deleta a referência
            UPDATE finEntry SET counterMovementId = NULL WHERE entryId = OLD.entryId;

            --Deleta a movimentação de caixa
            DELETE FROM finCounterMovement WHERE counterMovementId = OLD.counterMovementId;
        END IF;

        --Se tinha movimentação de banco e recebe nova movimentação de caixa
        IF ( OLD.bankMovementId IS NOT NULL AND
             NEW.counterMovementId IS NOT NULL )
        THEN
            --Delete a referência
            UPDATE finEntry SET bankMovementId = NULL WHERE entryId = OLD.entryId;

            --Delete a movimentação de banco
            DELETE FROM fin.bankMovement WHERE bankMovementId = OLD.bankMovementId;
        END IF;

        --Se estiver somente atualizando a mesma movimentação bancária
        IF ( OLD.bankMovementId IS NOT NULL AND
             NEW.bankMovementId IS NOT NULL)
        THEN
            --Não faz nada, as colunas da bankmovement formam vários lançamentos
        END IF;

        --Se estiver somente atualizando a mesma movimentação de caixa
        IF ( OLD.counterMovementId IS NOT NULL AND
             NEW.counterMovementId IS NOT NULL )
        THEN
            IF NEW.entryDate <> OLD.entryDate
            THEN
                UPDATE finCounterMovement SET value = NEW.value,
                                              movementDate = NEW.entryDate,
                                              operation =  (SELECT operationTypeId FROM finOperation WHERE operationId = NEW.operationId),
                                              operationId = NEW.operationId
                                        WHERE counterMovementId = OLD.counterMovementId;
            ELSE
                UPDATE finCounterMovement SET value = NEW.value,
                                              operation =  (SELECT operationTypeId FROM finOperation WHERE operationId = NEW.operationId),
                                              operationId = NEW.operationId
                                        WHERE counterMovementId = OLD.counterMovementId;
            END IF;
        END IF;
                                
        --Se tinha alguma movimentação e agora não tem mais
        IF ( (OLD.counterMovementId IS NOT NULL OR  OLD.bankMovementId IS NOT NULL) AND
             (NEW.counterMovementId IS NULL AND NEW.bankMovementId IS NULL) )
        THEN
            --Se a movimentação era de caixa
            IF ( OLD.counterMovementId IS NOT NULL )
            THEN
                --Deleta a movimentação de caixa
                DELETE FROM finCounterMovement WHERE counterMovementId = OLD.counterMovementId;

            --Se a movimentação era de banco
            ELSE
                --Só deleta a movimentação de banco caso não hajam outros lançamentos com ela
                IF ( SELECT COUNT(*) <= 0 FROM finEntry WHERE bankMovementId = OLD.bankMovementId )
                THEN
                    DELETE FROM fin.BankMovement WHERE bankMovementId = OLD.bankMovementId;
                END IF;
            END IF;
        END IF;

    ELSIF TG_OP = 'DELETE' 
    THEN
        --Se a movimentação era de caixa
        IF ( OLD.counterMovementId IS NOT NULL )
        THEN
            --Deleta a movimentação de caixa
            DELETE FROM finCounterMovement WHERE counterMovementId = OLD.counterMovementId;

        --Se a movimentação era de banco
        ELSIF ( OLD.bankMovementId IS NOT NULL ) 
        THEN
            --Só deleta a movimentação de banco caso não hajam outros lançamentos com elas
            IF (SELECT COUNT(*) <= 0 FROM finEntry WHERE bankMovementId = OLD.bankMovementId) 
            THEN
                DELETE FROM fin.BankMovement WHERE bankMovementId = OLD.bankMovementId;
            END IF;
        END IF;
    END IF;

    IF TG_OP = 'DELETE'
    THEN
        RETURN OLD;
    ELSE 
        RETURN NEW;
    END IF;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_sincronizarLancamentoNasMovimentacoes ON finEntry;
CREATE TRIGGER trg_sincronizarLancamentoNasMovimentacoes
    AFTER UPDATE OR DELETE ON finEntry
    FOR EACH ROW
    EXECUTE PROCEDURE sincronizarLancamentoNasMovimentacoes();
