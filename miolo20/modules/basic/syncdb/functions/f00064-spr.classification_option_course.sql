--
CREATE OR REPLACE FUNCTION spr.classification_option_course(p_stepId spr.step.stepId%TYPE)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: spr.classification_option_course
  DESCRIPTION: Classifica os inscritos no processo seletivo conforme sua opção
  de curso esta FUNÇÃO é executada somente para a ultima etapa do processo.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       12/01/2011 Fabiano Tomasini 1. FUNÇÃO criada.
  1.1       01/07/2011 Alex Smith       1. Adequação de encoding e substitui-
                                           ção do esquema antigo para o novo.
  1.2       08/08/2011 Alex Smith       1. Coding standard.
******************************************************************************/
DECLARE
    v_subscriptionStepInfo record;
    v_subscriptionOptionCourse record;
    v_option record;
    v_step record;
    v_maxPosition integer;
    v_subscriptionConfirmed boolean;
    v_substitutes integer;
    v_classificadopeloenem boolean;
BEGIN
    -- Update subscription option seta status inscrito para todos e zera posiçães
    UPDATE spr.subscriptionOption
       SET position = null,
           subscriptionStatusId = 1
     WHERE subscriptionId IN (SELECT subscriptionId
                                FROM spr.subscriptionStepInfo
                               WHERE stepId = p_stepId);

    RAISE NOTICE 'Gerando classificação por opção a partir da etapa %.', p_stepId;

    -- Percorre todos os inscritos na ultima etapa e classifica nos cursos pelo total de pontos
    FOR v_subscriptionStepInfo IN SELECT *
                                    FROM spr.subscriptionStepInfo
                                   WHERE stepid = p_stepId
                                ORDER BY position
    LOOP
        v_classificadopeloenem := false;
        IF v_subscriptionStepInfo.totalpointsstepenem = v_subscriptionStepInfo.totalpointsstep THEN
            v_classificadopeloenem := true;
        END IF;
        
        RAISE NOTICE 'Processando inscrição %.', v_subscriptionStepInfo.subscriptionId;

        -- Flag que determina que o inscrito passo nessa opção.
        v_subscriptionConfirmed := FALSE;

        -- Percorre todas as opçães de curso do inscrito ordenada pelo optionnumber
        FOR v_subscriptionOptionCourse IN SELECT *
                                            FROM spr.subscriptionOption A
                                      INNER JOIN spr.option B
                                              ON B.optionId = A.optionId
                                           WHERE subscriptionId = v_subscriptionStepInfo.subscriptionId
                                        ORDER BY optionNumber
        LOOP
            -- se status jé confirmado em outra opção
            IF v_subscriptionConfirmed THEN
                RAISE NOTICE ' Opção % definida como classificado em outra opção.', v_subscriptionOptionCourse.optionId || '-' || v_subscriptionOptionCourse.description;
                -- Status de classificado em outra opção
                UPDATE spr.subscriptionOption
                   SET subscriptionStatusId = 6
                 WHERE optionId = v_subscriptionOptionCourse.optionId
                   AND subscriptionId = v_subscriptionOptionCourse.subscriptionId;
            ELSE
                -- Obtem a posição de valor mais alto até o momento
                SELECT COALESCE(MAX(position), 0) INTO v_maxPosition
                  FROM spr.subscriptionOption
                 WHERE optionId = v_subscriptionOptionCourse.optionId;

                -- número de vagas maior que o valor da ultima posição e status diferente de reprovado e desistente.
                IF v_subscriptionOptionCourse.vacancies > v_maxPosition 
                   AND v_subscriptionStepInfo.subscriptionStatusId != 4  
                   AND v_subscriptionStepInfo.subscriptionStatusId != 5
                   AND v_subscriptionStepInfo.subscriptionStatusId != 1
                   THEN
                    RAISE NOTICE ' Opção % definida como aprovado.', v_subscriptionOptionCourse.optionId || '-' || v_subscriptionOptioncourse.description;
                    -- Aprova o inscrito na opção de curso
                    UPDATE spr.subscriptionOption
                       SET subscriptionStatusId = 2,
                           position = v_maxPosition + 1,
                           classificadopeloenem = v_classificadopeloenem
                     WHERE optionId = v_subscriptionOptionCourse.optionId
                       AND subscriptionId = v_subscriptionOptionCourse.subscriptionId;

                    -- Flag que determina que o inscrito passou nessa opção.
                    v_subscriptionConfirmed := TRUE;
                ELSE
                    -- Obtem o total de suplentes para a opção mais 1
                    SELECT COALESCE(COUNT(*), 0) + 1 INTO v_substitutes
                      FROM spr.subscriptionOption
                     WHERE optionId = v_subscriptionOptionCourse.optionId
                       AND subscriptionStatusId = 3;

                    --Atribui as informaçães da etapa atual na variável record v_step
                    SELECT * INTO v_step FROM spr.step WHERE stepId = p_stepId;

                    -- Se nao excedeu o numero de suplentes e status diferente de reprovado e desistente.                    
                    IF v_subscriptionStepInfo.subscriptionStatusId != 4 AND v_subscriptionStepInfo.subscriptionStatusId != 5 THEN
                        RAISE NOTICE ' Opção % definida como suplente.', v_subscriptionOptionCourse.optionId || '-' || v_subscriptionOptioncourse.description;
                        -- Suplente
                        UPDATE spr.subscriptionOption
                           SET subscriptionStatusId = 3,
                               position = v_maxPosition + 1,
                               classificadopeloenem = v_classificadopeloenem
                         WHERE optionId = v_subscriptionOptionCourse.optionId
                           AND subscriptionId = v_subscriptionOptionCourse.subscriptionId;

                    ELSEIF v_subscriptionStepInfo.subscriptionStatusId = 5 THEN
                        -- Desistente
                        UPDATE spr.subscriptionOption
                           SET subscriptionStatusId = 5,
                               position = v_maxPosition + 1,
                               classificadopeloenem = v_classificadopeloenem
                         WHERE optionId = v_subscriptionOptionCourse.optionId
                           AND subscriptionId = v_subscriptionOptionCourse.subscriptionId;

                    ELSE
                        RAISE NOTICE ' Opção % definida como reprovado.', v_subscriptionOptionCourse.optionId || '-' || v_subscriptionOptioncourse.description;
                        -- Reprovado
                        UPDATE spr.subscriptionOption
                           SET subscriptionStatusId = 4,
                               position = v_maxPosition + 1
                         WHERE optionId = v_subscriptionOptionCourse.optionId
                           AND subscriptionId = v_subscriptionOptionCourse.subscriptionId;
                    END IF;
                END IF;
            END IF;
        END LOOP;
    END LOOP;

    RETURN TRUE;
END;
$BODY$
LANGUAGE 'plpgsql';
--
