CREATE OR REPLACE FUNCTION verificaCaixaAbertoVinculadoAoLancamento()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaCaixaAbertoVinculadoAoLancamento
  DESCRIPTION: Trigger que verifica e bloqueia alteração no lançamento caso o
  seja um lançamento vinculado a movimentação de caixa e o caixa já tenha sido
  fechado. 

  ticket #37592

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       11/05/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    v_counterMovementId INTEGER;
    v_openCounter INTEGER;
BEGIN
    IF TG_OP = 'INSERT'
    THEN
        v_counterMovementId := NEW.counterMovementId;
    ELSE
        v_counterMovementId := OLD.counterMovementId;

        IF ( TG_OP = 'UPDATE' )
        THEN
            -- Lançamentos estornados podem ser atualizados - ticket #38170
            IF ((OLD.lancamentoEstornado IS FALSE) AND (NEW.lancamentoEstornado IS TRUE))
            THEN
                v_counterMovementId := NULL;
            ELSIF ( (OLD.lancamentoEstornado IS TRUE) AND (NEW.lancamentoEstornado IS TRUE) )
            THEN
                RAISE EXCEPTION 'Esse lançamento não pode ser estornado por que no dia % já foi estornado pelo usuário %.', datetouser(OLD.datetime::DATE), OLD.username;
            END IF;
        END IF;

    END IF;
    
    IF v_counterMovementId IS NOT NULL
    THEN
        -- Verifica se o caixa atrelado a movimentação de caixa não está fechado
        SELECT INTO v_openCounter A.openCounterId
          FROM finOpenCounter A
         WHERE A.openCounterId = (SELECT openCounterId 
                                    FROM finCounterMovement 
                                   WHERE counterMovementId = v_counterMovementId)::INT
           AND NOT EXISTS (SELECT 1 
                             FROM finCloseCounter 
                            WHERE openCounterId = A.openCounterId);

        IF v_openCounter IS NULL
        THEN
            -- Exception bloqueando a operação
            RAISE EXCEPTION 'Essa operação não pode ser efetuado pois o caixa vinculado a movimentação de caixa já esta fechado.'; 
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
  LANGUAGE plpgsql VOLATILE;

DROP TRIGGER IF EXISTS trg_verificaCaixaAbertoVinculadoAoLancamento ON finentry;
CREATE TRIGGER trg_verificaCaixaAbertoVinculadoAoLancamento
  BEFORE INSERT OR UPDATE OR DELETE
  ON finentry
  FOR EACH ROW
  EXECUTE PROCEDURE verificaCaixaAbertoVinculadoAoLancamento();