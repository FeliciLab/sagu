CREATE OR REPLACE FUNCTION spr.set_step_subscriptions_status(p_stepId spr.step.stepId%TYPE)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: spr.set_step_subscriptions_status
  DESCRIPTION: FUNÇÃO responsável por setar o status dos inscritos na etapa,
  usada na FUNÇÃO spr.classification_in_step

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- -----------------  ------------------------------------
  1.0       12/01/2011 Fabiano Tomasini   1. FUNÇÃO criada.
  1.1       01/06/2011 Fabiano Tomasini   1. Novo critério de desempate por
                                             prioridade de avaliação
  1.2       02/06/2011 Leovan Tavares     1. Pequenos ajustes na verificação
                                             do novo critério de desempate
                                             por prioridade de avaliação
  1.3       08/08/2011 Alex Smith         1. Coding standard.
  1.4       09/08/2011 Fabiano Tomasini   1. Novo critério de desempate
                                             por etapa.
  1.5       26/12/2011 ftomasini          1. Ajuste pontuação mínima da etapa
******************************************************************************/
DECLARE
    v_select text;
    v_tiebreak record;
    v_orderBy text;
    v_subscriptionStepInfo record;
    v_step record;
    v_statusId integer;
    v_count integer;
    v_countPosition integer;
    v_stepEvaluations record;
    v_selectBestPunctuationInStep text;
    v_selectEvaluation text;
	v_dynamicOrder integer;
BEGIN

    --Atribui as informaçães da etapa na variável record v_step
    SELECT * INTO v_step FROM spr.step WHERE stepId = p_stepId;

    v_selectBestPunctuationInStep :='';
    v_selectEvaluation :='';
    v_dynamicOrder := 8;
    v_count := 1;
    v_countPosition := 1;
    v_orderBy := ' ORDER BY 7 ASC, 4 DESC,';

    --Percorre todos os critérios de desempate da etapa ordenados pela prioridade e concatena na variável v_order_by
    FOR v_tiebreak IN SELECT tiebreakid,
                             steptiebreakid,
                             tiebreakstepid
                        FROM spr.steptiebreak
                       WHERE stepId = p_stepId
                         AND priority IS NOT NULL
                    ORDER BY priority
    LOOP
        --Critério de desempate idade em ordem decrescente
        IF v_tiebreak.tiebreakId = 1 THEN
            v_orderBy := v_orderBy || '5 DESC,';
        END IF;
        --Critério de desempate idade em ordem crescente
        IF v_tiebreak.tiebreakId = 2 THEN
            v_orderBy := v_orderBy || '5 ASC,';
        END IF;
        --Critério de desempate ordem de inscrição (controlada pela flag subscriptionorder da tabela subscription)
        IF v_tiebreak.tiebreakId = 3 THEN
            v_orderBy := v_orderBy || '6 ASC,';
        END IF;
        --Critério de desempate sorteio
        IF v_tiebreak.tiebreakId = 4 THEN
            v_orderBy := v_orderBy || 'RANDOM(),';
        END IF;
        --Critério de desempate por prioridade de provas
        IF  v_tiebreak.tiebreakid = 5 THEN
			 --Atribui avaliaéoes da etapa na variável v_step_evaluations e percorre elas ordenado pela prioridade
             FOR v_stepEvaluations IN SELECT *
                                         FROM spr.evaluation
                                        WHERE stepId = p_stepId
                                          AND priority IS NOT NULL
                                     ORDER BY priority
             LOOP
                 v_selectEvaluation:= v_selectEvaluation || ',(SELECT AA.totalpoints
                                                                 FROM spr.evaluationpoints AA
                                                           INNER JOIN spr.evaluation BB
                                                                   ON (AA.evaluationId = BB.evaluationId)
                                                           INNER JOIN spr.step CC
                                                                   ON (CC.stepId = BB.stepId)
                                                                WHERE AA.subscriptionid = A.subscriptionid
                                                                  AND BB.evaluationId = '''||v_stepEvaluations.evaluationid||'''
                                                                  AND CC.stepId = ''' || p_stepId || ''') as evaluation_' ||v_dynamicOrder;

                 v_orderBy:= v_orderBy || v_dynamicOrder || ' DESC,';
                 v_dynamicOrder:= v_dynamicOrder + 1;

             END LOOP;
        END IF;
        --Critério de desempate por etapa
        IF v_tiebreak.tiebreakid = 6 THEN
            v_selectBestPunctuationInStep:= v_selectBestPunctuationInStep || ',(SELECT AA.totalPointsStep
                                                                                  FROM spr.subscriptionstepinfo AA
                                                                            INNER JOIN spr.subscription BB
                                                                                    ON (AA.subscriptionId = BB.subscriptionId)
                                                                                 WHERE AA.subscriptionid = A.subscriptionid
                                                                                   AND AA.stepId = ''' || v_tiebreak.tiebreakstepid || ''') AS stepPoints_' ||v_dynamicOrder;
            v_orderBy:= v_orderBy || v_dynamicOrder || ' DESC,';
            v_dynamicOrder:= v_dynamicOrder + 1;
        END IF;
    END LOOP;

    v_orderBy := substr( v_orderBy, 1, length(v_orderBy)-1);

    v_select := 'SELECT A.subscriptionId,
                        A.stepId,
                        A.subscriptionStatusId,
                        A.totalPoints,
                        (extract(YEAR from current_date) - extract(YEAR FROM E.datebirth))::integer AS age,
                        D.subscriptionorder,
                        CASE WHEN F.generatefinance = ''t'' AND balance(D.invoiceid) !=0
                            THEN 5 --DESISTENTE
                        ELSE
                            (CASE WHEN (NOT spr.CHECK_IF_EVALUATIONS_OF_SUBSCRIPTION_RECEIVED_MINPOINTS(A.subscriptionId,A.stepId))
                                      OR (C.minPoints > coalesce(A.totalPointsStep,0))
                                THEN 4
                                ELSE -1 END)
                        END AS initialStatus
                        --Avaliaçães caso tenha
                        '||v_selectEvaluation||'
                        '||v_selectBestPunctuationInStep||'
                  FROM spr.subscriptionStepInfo A
            INNER JOIN spr.step C
                    ON (C.stepId = A.stepId)
            INNER JOIN spr.subscription D
                    ON (D.subscriptionId = A.subscriptionId)
            INNER JOIN spr.selectiveprocess F
                    ON (D.selectiveprocessid = F.selectiveprocessId)
       INNER JOIN ONLY basphysicalPerson E
                    ON (E.personId = D.personId)
                 WHERE A.stepId = ''' || p_stepId || ''''||v_orderBy ;

    -- Percorre todas inscrições e seta o status conforme número de vagas, pontos mínimos da etapa e das avaliaçães.
    FOR v_subscriptionStepInfo IN EXECUTE v_select
    LOOP
        -- Reprova o inscrito caso ele tenha obtido uma nota menor que a nota mínima em uma das avaliaçães
        IF( v_subscriptionStepInfo.initialStatus != -1 )
        THEN
            IF ( v_subscriptionStepInfo.initialStatus = 5 )
            THEN
                v_statusId := v_subscriptionStepInfo.initialStatus;
            ELSE
                -- statusid 4 = REPROVADO
                v_statusId := v_subscriptionStepInfo.initialStatus;
            END IF;
        ELSE
            -- Aprova os primeiros da lista conforme o número de vagas. Se a quantidade de vagas é NULL,
            -- significa que não hé limite de vagas, ou seja, todos podem ser aprovados.
            IF( COALESCE(v_step.vacancies, v_count) >= v_count )
            THEN
                -- statusid 2 = APROVADO
                v_statusId := 2;
                v_count := v_count+1;
            -- Suplentes
            ELSE
                -- statusid 3 = SUPLENTE
                v_statusId := 3;
            END IF;
        END IF;

        RAISE NOTICE 'Inscrição %: setando status para % e colocação para %', v_subscriptionStepInfo.subscriptionId, v_statusId, v_countPosition;

        -- Atualiza as informaçães da inscrição
        UPDATE spr.subscriptionStepInfo
           SET subscriptionStatusId = v_statusId,
               position = v_countPosition
         WHERE subscriptionid = v_subscriptionStepInfo.subscriptionId
           AND stepId = p_stepId;

        -- Incrementa a posição
        v_countPosition := v_countPosition + 1;
    END LOOP;

    RETURN TRUE;
END;
$BODY$
LANGUAGE 'plpgsql';
--


