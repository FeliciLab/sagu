CREATE OR REPLACE FUNCTION obterNumeroDeParcelas(p_contractid integer, p_learningperiodid integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: obterNumeroDeParcelas
  PURPOSE: Retorna o número de parcelas para divisão do valor do semestre

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       13/02/2013 Leovan Tavares    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    -- Número de parcelas definido no contrato
    CPCL integer;
    -- Parcelas passadas
    PPCL integer;
    -- Número de parcelas
    NPCL integer;
    --Numero de parcelas do periodo letivo
    LPCL integer;
    
    --Tipo de regime
    v_tipo_de_regime integer;

    -- Data que será utilizada para verificar o número de parcelas restantes
    v_data_referencia date;
    -- Data inicial do período letivo
    v_data_periodo date;
    -- Parcelas geradas
    v_parcelas_geradas int;
BEGIN
    SELECT INTO v_tipo_de_regime B.regimenId
              FROM acdEnroll A
        INNER JOIN acdGroup B
                ON (A.groupId = B.groupId)
        INNER JOIN acdCurriculum C
                ON (B.curriculumId = C.curriculumId)
        INNER JOIN acdCurricularComponent D
                ON (C.curricularComponentId = D.curricularComponentId AND
                    C.curricularComponentVersion = D.curricularComponentVersion)
        INNER JOIN acdLearningPeriod E
                ON (E.learningPeriodId = B.learningPeriodId)
             WHERE E.periodId IN (SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = p_learningPeriodId)
               AND A.contractId = p_contractId
               AND A.statusid <> GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::integer;
    
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: GERAR_PARCELAS_RESTANTES   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'GERAR_PARCELAS_RESTANTES') , '3 - PARÂMETROS GERAIS');

    IF((getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_VENCIMENTO') OR 
       (getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_MES_VENCIMENTO') )
    THEN

        SELECT INTO v_data_periodo begindate FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId;

        -- Obter CPCL, que está armazenado na tabela de contratos
        SELECT INTO CPCL parcelsnumber FROM acdcontract WHERE contractid = p_contractId;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas definido no contrato da pessoa (prioridade 1): ' || CPCL, '2 - CONFIGURAÇÕES GERAIS');
        
        -- Obtém do período letivo, caso não encontrado no contrato
        IF CPCL IS NULL
        THEN
      SELECT INTO CPCL A.parcelsnumber FROM acdLearningPeriod A WHERE A.learningPeriodId = p_learningperiodid;

      PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas definido no período letivo (prioridade 2): ' || CPCL, '2 - CONFIGURAÇÕES GERAIS');
        END IF;

  -- Obtém do preço, caso não encontrado no contrato e no período letivo
        IF CPCL IS NULL
        THEN
      SELECT INTO CPCL A.parcelsnumber
             FROM finPrice A
       INNER JOIN acdContract B
               ON (A.courseId, A.courseVersion, A.turnId, A.unitId) = (B.courseId, B.courseVersion, B.turnId, B.unitId)
            WHERE B.contractId = p_contractId
                    AND NOW()::DATE >= A.startdate
                    AND NOW()::DATE <= A.enddate;

      PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas definido no preço do curso (prioridade 3): ' || CPCL, '2 - CONFIGURAÇÕES GERAIS');
        END IF;

        -- Obter PPCL
        -- Se já existem títulos, a data para referência é a emissão dos mesmos
        SELECT INTO v_parcelas_geradas, v_data_referencia
            COUNT(DISTINCT parcelnumber),
            MIN(emissiondate) 
        FROM finreceivableinvoice A
        WHERE iscanceled IS FALSE
          AND parcelnumber <> 0
        AND EXISTS ( SELECT contractid FROM finentry
                    WHERE invoiceid = A.invoiceid
                    AND contractid = p_contractId)
                    AND EXISTS (SELECT learningperiodid FROM finentry AA
                                    INNER JOIN acdlearningperiod BB USING (learningperiodid) 
                                    WHERE invoiceid = A.invoiceid
                                    AND BB.periodid IN (SELECT periodid FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId));

        --Caso a data de referencia for menor que a inicial do periodo, assume a data do período como de referencia
        IF ( v_data_referencia < v_data_periodo )
        THEN
            v_data_referencia := v_data_periodo;
        END IF;

        -- Caso não existam títulos, a data de referência é a atual
        IF v_parcelas_geradas > 0 THEN
            PPCL := EXTRACT(MONTH FROM v_data_referencia) - EXTRACT(MONTH FROM v_data_periodo);

            IF ( now()::DATE > v_data_periodo ) 
            THEN
                IF ( getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_VENCIMENTO' )
                THEN
                    IF ( EXTRACT('DAY' FROM v_data_referencia) > obterdiavencimento(p_contractId, p_learningPeriodId))
                    THEN 
                        PPCL := PPCL +1;
                    END IF;    
                END IF;
            END IF;

            IF ( (CPCL - PPCL) > v_parcelas_geradas ) THEN
            RETURN (CPCL - PPCL);
            ELSE

            RETURN v_parcelas_geradas;
            END IF;
        END IF;

        v_data_referencia := now()::date;

        -- Verifica-se a diferença em meses entre a data de referencia e a data inicial do periodo
        IF v_data_referencia > v_data_periodo 
        THEN
            PPCL := EXTRACT(MONTH FROM v_data_referencia) - EXTRACT(MONTH FROM v_data_periodo);
            
            IF ( getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_VENCIMENTO' )
            THEN
                IF ( EXTRACT('DAY' FROM v_data_referencia) > obterdiavencimento(p_contractId, p_learningPeriodId))
                THEN 
                    PPCL := PPCL +1;
                END IF;    
            END IF;

            NPCL := CPCL - PPCL;
        ELSE
            NPCL := CPCL;
        END IF;

        RETURN NPCL;
    ELSE
        --Obtém o número de parcelas do contrato
        SELECT INTO CPCL parcelsnumber FROM acdcontract WHERE contractid = p_contractId;
        --Obtém o número de parcelas definido no período letivo
        SELECT INTO LPCL parcelsnumber FROM acdLearningPeriod WHERE learningperiodId = p_learningPeriodId;
        --Obtém o numero de parcelas definido no preço
        SELECT INTO PPCL A.parcelsnumber
               FROM finPrice A
         INNER JOIN acdContract B
                 ON (A.courseId, A.courseVersion, A.turnId, A.unitId) = (B.courseId, B.courseVersion, B.turnId, B.unitId)
              WHERE B.contractId = p_contractId;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas definido no período letivo (prioridade 2): ' || LPCL, '2 - CONFIGURAÇÕES GERAIS');
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: GERAR_PARCELAS_RESTANTES   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'GERAR_PARCELAS_RESTANTES') ||' (prioridade 3)', '3 - PARÂMETROS GERAIS');   

        RETURN COALESCE(CPCL, LPCL, PPCL);

    END IF;    
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
