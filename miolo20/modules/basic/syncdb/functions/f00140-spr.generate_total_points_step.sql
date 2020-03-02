CREATE OR REPLACE FUNCTION spr.generate_total_points_step(p_stepid integer)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: spr.GENERATE_TOTAL_POINTS_STEP
  DESCRIPTION: FUNÇÃO responsável por calcular o total de pontos dos inscritos
  na etapa, usada na FUNÇÃO spr.CLASSIFICATION_STEP

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       12/01/2011 Fabiano Tomasini 1. FUNÇÃO criada.
  1.1       01/07/2011 Alex Smith       1. Adequação de encoding e substitui-
                                           ção do esquema antigo para o novo.
  1.2       08/08/2011 Alex Smith       1. Correção para utilização de tipos
                                           de dados corretos na FUNÇÃO ROUND.
                                        2. Adequação da FUNÇÃO aos coding
                                           standards.
  1.3       12/12/2011 Moises Heberle   1. Alteracao de calculo para considerar
                                           tambem o campo spr.step.weight
  1.4       26/12/2011 ftomasini        1. Ajustes calculo pontuação
  1.5       19/04/2013 Jonas Diel       1. Adicionada opção para cálculo de 
                                           notas por soma ou média
******************************************************************************/
DECLARE
    v_step record;
    v_subscriptionStepInfo record;
    v_evaluationPoints record;
    v_totalPoints numeric;
    v_totalWeight numeric;
    v_result numeric;
    v_totalPointsInThePreviousStep numeric;
    v_total_weight_steps numeric;
    v_total_points_step numeric;
    v_total_points_enem_step numeric;
    v_classificado_pelo_enem boolean;
    v_total_points_step_normal numeric;

BEGIN
    v_totalPoints := 0;
    v_totalWeight := 0;

    --Atribui as informações da etapa na variável record v_step
    SELECT * INTO v_step
      FROM spr.step
     WHERE stepId = p_stepId;

    --Atribui as informações da tabela subscriptionstepinfo na variável v_subscriptionStepInfo
    FOR v_subscriptionStepInfo IN SELECT *
                                    FROM spr.subscriptionStepInfo
                                   WHERE stepId = p_stepId
                                   --AND subscriptionid = 3020
    LOOP
        RAISE NOTICE 'Processando inscrição %', v_subscriptionStepInfo.subscriptionId;
        RAISE NOTICE 'Interno: %', v_subscriptionStepInfo.useInternalEvaluations;

        v_totalPoints := 0;
        v_totalWeight := 0;
        v_total_points_step := 0;
        v_total_points_enem_step := 0;
        v_total_weight_steps  := 0;
        v_classificado_pelo_enem := false;

        FOR v_evaluationPoints IN SELECT A.totalpoints,
                                         A.totalpointsenem,
                                         B.weight,
                                         C.weight as weightStep
                                    FROM spr.evaluationpoints A
                              INNER JOIN spr.evaluation B
                                      ON (A.evaluationId = B.evaluationId)
                              INNER JOIN spr.step C
                                      ON (C.stepId = B.stepId)
                                   WHERE A.subscriptionId = v_subscriptionStepInfo.subscriptionId
                                     AND C.stepId = v_subscriptionStepInfo.stepId
                                     AND B.isinternal = v_subscriptionStepInfo.useInternalEvaluations
        LOOP
            --Soma os pesos de todas as avaliações da etapa
            v_totalWeight := v_totalWeight + COALESCE(v_evaluationPoints.weight, 0);

	    IF v_step.calculationMethod = 1 THEN	    
    		--Média do total de pontos de todas as avaliações da etapa considerando o peso de cada uma
    		v_total_points_step := ROUND( (v_total_points_step + (COALESCE(v_evaluationPoints.totalPoints, 0) * COALESCE(v_evaluationPoints.weight,1)))::numeric, 2);		
    		RAISE NOTICE 'Total de pontos + (Total de pontos avaliação(%) * Total peso avaliação(%)) = %', COALESCE(v_evaluationPoints.totalPoints, 0), COALESCE(v_evaluationPoints.weight,1), v_total_points_step;

            v_total_points_enem_step := ROUND( (v_total_points_enem_step + (COALESCE(v_evaluationPoints.totalPointsEnem, 0) * COALESCE(v_evaluationPoints.weight,1)))::numeric, 2);       
            RAISE NOTICE 'Total de pontos Enem + (Total de pontos avaliação Enem(%) * Total peso avaliação(%)) = %', COALESCE(v_evaluationPoints.totalPointsEnem, 0), COALESCE(v_evaluationPoints.weight,1), v_total_points_enem_step;

	    END IF; 
	    IF v_step.calculationMethod = 2 THEN
    		--Calcula a soma das avaliações da etapa
    		v_total_points_step := ROUND(v_total_points_step + (COALESCE(v_evaluationPoints.totalPoints, 0))::numeric, 2);
    		RAISE NOTICE 'Total de pontos + (Total de pontos avaliação(%)) = %', COALESCE(v_evaluationPoints.totalPoints, 0), v_total_points_step;

            --Calcula a soma das avaliações do enem da etapa
            v_total_points_enem_step := ROUND(v_total_points_enem_step + (COALESCE(v_evaluationPoints.totalPointsEnem, 0))::numeric, 2);
            RAISE NOTICE 'Total de pontos enem + (Total de pontos do enem na avaliação(%)) = %', COALESCE(v_evaluationPoints.totalPointsEnem, 0), v_total_points_enem_step;
	    END IF;

        END LOOP;

        --Se a etapa utiliza Enem
        IF v_step.notasenem IS TRUE THEN        
            --Caso o total de pontos do Enem for Maior que o total de pontos das avaliações, considera do Enem
            IF v_total_points_enem_step > v_total_points_step THEN
                v_total_points_step_normal := v_total_points_step;
                v_total_points_step := v_total_points_enem_step;
                v_classificado_pelo_enem := true;
            END IF;
        END IF;

        RAISE NOTICE 'Total de pontos = %', v_total_points_step;
        
        -- evitar divisão por zero
        IF v_totalWeight = 0 THEN
           v_totalWeight := 1;
        END IF;

	--Se for média
	IF v_step.calculationMethod = 1 THEN
		--Total de pontos dividido pelo total dos pesos das avaliações	
		v_total_points_step := (v_total_points_step / v_totalWeight);
		v_total_points_enem_step := (v_total_points_enem_step / v_totalWeight);
		RAISE NOTICE 'Total de pontos da etapa(%) / Total dos pesos das avaliações(%) = % / ENEM: %',v_total_points_step,v_totalWeight,(v_total_points_step / v_totalWeight), v_total_points_enem_step;
	END IF;

        --Total de pesos de todas etapas do processo seletivo
        v_total_weight_steps := SUM(weight) FROM spr.step WHERE selectiveProcessId = v_step.selectiveprocessid;

        -- evitar divisão por zero
        IF v_total_weight_steps = 0 THEN
           v_total_weight_steps := 1;
        END IF;

	--Se for média
	IF v_step.calculationMethod = 1 THEN
		--Pontuação na etapa multiplicada pelo peso da etapa e dividida pelo total dos pesos das etapas
		v_totalPoints := v_total_points_step  * COALESCE(v_step.weight,1) / v_total_weight_steps;
        END IF;
        --Se for Soma
        IF v_step.calculationMethod = 2 THEN
		v_totalPoints := v_total_points_step;
	END IF;
	
        --Se soma total com o total de pontos da etapa anterior caso accumulateprevioussteps for true
        IF v_step.accumulatePreviousSteps THEN
                SELECT totalPoints
                  INTO v_totalPointsInThePreviousStep
                  FROM spr.subscriptionStepInfo A
            INNER JOIN spr.step B
                    ON (A.stepId = B.stepId)
            INNER JOIN spr.selectiveProcess C
                    ON (B.selectiveProcessId = C.selectiveProcessId)
                 WHERE C.selectiveProcessId = v_step.selectiveProcessid
                   AND B.stepOrder = (v_step.stepOrder - 1)
                   AND A.subscriptionId = v_subscriptionStepInfo.subscriptionId;

            --RAISE NOTICE ' Pontuação da etapa anterior (%) + ((Pontuação total (%) / Soma dos pesos (%)) . Peso da etapa(%) / Soma dos pesos das etapas(%)) = %', COALESCE(v_totalPointsInThePreviousStep, 0), v_totalPoints, v_totalWeight, v_step.weight, v_total_weight_steps, v_result + COALESCE(v_totalPointsInThePreviousStep, 0);

            v_totalPoints := v_totalPoints + COALESCE(v_totalPointsInThePreviousStep, 0);

        ELSE
            --RAISE NOTICE ' Pontuação total da etapa(%) . Peso da etapa(%) / Soma dos pesos das etapas (%) = %', v_total_points_step, v_step.weight, v_total_weight_steps, v_totalPoints;
        END IF;

        RAISE NOTICE '%', v_totalPoints;

        --v_total_points_step := COALESCE(v_total_points_step_normal, v_total_points_step);
        v_total_points_step := (CASE WHEN v_total_points_step > v_total_points_enem_step THEN v_total_points_step ELSE v_total_points_enem_step END);

        RAISE NOTICE 'Total points: % -- Total points step: % - TPS Enem: %', v_totalPoints, v_total_points_step, v_total_points_enem_step;

        --Atualiza as informacoes da inscricoes na etapa com o total de pontos obtidos na etapa
        UPDATE spr.subscriptionStepInfo
           SET totalPoints = v_totalPoints,
               totalPointsstep = v_total_points_step,
               totalPointsstepenem = v_total_points_enem_step
         WHERE subscriptionid = v_subscriptionStepInfo.subscriptionId
           AND stepId = p_stepId;

    END LOOP;

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION spr.generate_total_points_step(integer)
  OWNER TO postgres;
--
