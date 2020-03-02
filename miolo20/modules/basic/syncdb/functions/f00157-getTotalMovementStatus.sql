CREATE OR REPLACE FUNCTION getTotalMovementStatus(p_contractId int, v_limitDate date)
RETURNS TABLE(intervalCursed INTERVAL, intervalClosed INTERVAL) AS
$BODY$
/******************************************************************************
  NAME: getTotalMovementStatus
  DESCRIPTION: Retorna o tempo cursado e de trancamento de determinado período

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       20/08/2013 Jonas Diel        Função Criada
******************************************************************************/
DECLARE
    v_counterMovement RECORD;    
    v_intervalCursed INTERVAL; --Intervalo de tempo cursado
    v_intervalClosed INTERVAL; --Intervalo de tempo trancado

    v_beforeMovement RECORD;
    v_firstMovement BOOLEAN;
BEGIN
    v_intervalCursed := 0;
    v_intervalClosed := 0;

    v_firstMovement := TRUE;

    FOR v_counterMovement IN 
                  SELECT A.stateContractId,
                         A.stateTime,
                         A.learningPeriodId,
                         B.inouttransition,
                         B.isclosecontract
                    FROM acdMovementContract A
              INNER JOIN acdStateContract B
                      ON B.stateContractId = A.stateContractId
                   WHERE contractId = p_contractId 
                ORDER BY A.statetime
    LOOP

        v_counterMovement.stateTime := COALESCE(v_limitDate::timestamp, v_counterMovement.stateTime);
    
        IF v_firstMovement IS FALSE THEN --Não for a primeira linha

            IF v_counterMovement.isclosecontract IS TRUE THEN --A movimentação tranca o contrato
                            
                IF v_beforeMovement.isclosecontract IS FALSE THEN --Verifica a anterior
                    v_intervalCursed := v_intervalCursed + (v_counterMovement.stateTime - v_beforeMovement.stateTime)::interval;
                ELSE --Calcula tempo de trancamento                    
                    v_intervalClosed := v_intervalClosed + (v_counterMovement.stateTime - v_beforeMovement.stateTime)::interval;
                END IF;

            ELSE --A movimentação nao tranca o contrato       

                IF v_beforeMovement.isclosecontract IS TRUE THEN --Verifica a anterior
                    v_intervalCursed := v_intervalCursed + (now() - v_counterMovement.stateTime)::interval;
                ELSE --soma intervalo cursado                    
                    v_intervalCursed := v_intervalCursed + (v_counterMovement.stateTime - v_beforeMovement.stateTime)::interval;
                END IF;

            END IF;          
        END IF;       

        v_beforeMovement := v_counterMovement;
        v_firstMovement := FALSE;
    END LOOP;

    RETURN QUERY SELECT v_intervalCursed, v_intervalClosed;
    
    END;
$BODY$
LANGUAGE 'plpgsql';
--
