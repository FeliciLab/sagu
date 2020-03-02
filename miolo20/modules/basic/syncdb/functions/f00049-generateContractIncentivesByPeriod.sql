--
-- Leovan Tavares da Silva em 18/06/2012
--
CREATE OR REPLACE FUNCTION generateContractIncentivesByPeriod(p_contractid integer, p_learningperiodid integer)
  RETURNS boolean AS
$BODY$
DECLARE
    -- Objetos
    v_incentives record;
    v_learningPeriod record;
    v_contract record;
    v_invoice record;

    -- Operaçães
    v_incentiveOperationId integer; -- Desconto do incentivo
    v_paymentIncentiveOperationId int; -- Estorno do incentivo
    v_repaymentIncentiveOperationId int; -- Reembolso do incentivo

    -- variáveis
    V_PRECO FLOAT; -- Valor de contrato
    V_VI FLOAT; --Valor de incentivo
    V_NPCL INT; -- número de parcelas
    V_BPCL INT; -- número de parcelas bloqueadas
    V_APCL INT; -- número de parcelas abertas
    V_VIB FLOAT; -- Valor de incentivo bloqueado
    V_AJT FLOAT; -- Valor de ajuste no valor do incentivo
    V_VPI FLOAT; -- Valor da parcela de incentivo

    V_SUMVALUE FLOAT;
    V_SUBVALUE FLOAT;
    V_IVALUE FLOAT;
    V_INL FLOAT; -- Valor de incentivo não lanéado por falta de saldo no tétulo
    V_ANL FLOAT; -- Valor de ajuste não lanéado por falta de saldo no tétulo  
BEGIN
    V_PRECO = 0;
    V_VI = 0;
    V_NPCL = 0;
    V_BPCL = 0;
    V_APCL = 0;
    V_VIB = 0;
    V_AJT = 0;
    V_VPI = 0;

    V_SUMVALUE = 0;
    V_SUBVALUE = 0;
    V_IVALUE = 0;
    V_INL = 0;
    V_ANL = 0;
 
    --Obtem o peréodo letivo
    SELECT * INTO v_learningPeriod FROM acdLearningPeriod WHERE learningPeriodId = p_learningPeriodId;

    --Obtem o contrato
    SELECT * INTO v_contract FROM acdContract WHERE contractId = p_contractId;

    FOR v_incentives IN SELECT * FROM finincentive WHERE contractid = p_contractid AND (v_learningPeriod.beginDate, v_learningPeriod.endDate) OVERLAPS (startDate, endDate)
    LOOP
        --Código da operação do incentivo
        v_incentiveOperationId := operationId FROM ONLY finincentivetype WHERE incentivetypeid = v_incentives.incentivetypeid;
        --Código da operação de acréscimo de incentivo



        v_paymentIncentiveOperationId := paymentoperation FROM ONLY finincentivetype WHERE incentivetypeid = v_incentives.incentivetypeid;
        --Código da operação de reembolso de incentivo
        v_repaymentIncentiveOperationId := repaymentoperation FROM ONLY finincentivetype WHERE incentivetypeid = v_incentives.incentivetypeid;

        --
        -- OBTENçãO DE variáveis
        --
        -- número de parcelas (número de tétulos a receber o incentivo)
        V_NPCL := COUNT(*) FROM finreceivableinvoice A 
                          WHERE A.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate                        
                            AND iscanceled = 'f'
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.learningPeriodId = p_learningPeriodId)                      
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.contractId = p_contractId);
        -- Valor contratado
        V_SUMVALUE := (SELECT SUM(E.value)
                         FROM ONLY finInvoice I
                   INNER JOIN finEntry E
                           ON E.invoiceId = I.invoiceId
                        WHERE I.isCanceled IS FALSE
                          AND I.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.learningPeriodId = p_learningPeriodId)                      
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.contractId = p_contractId)
                          AND E.operationId IN (SELECT UNNEST(ARRAY[monthlyfeeoperation, renewaloperation,addcurricularcomponentoperation,enrolloperation]) FROM finDefaultOperations));

        IF V_NPCL = 0 OR V_SUMVALUE = 0
        THEN
            RETURN TRUE;
        END IF;

        V_SUBVALUE := (SELECT SUM(E.value)
                         FROM ONLY finInvoice I
                   INNER JOIN finEntry E
                           ON E.invoiceId = I.invoiceId
                        WHERE I.isCanceled IS FALSE
                          AND I.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.learningPeriodId = p_learningPeriodId)                      
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.contractId = p_contractId)
                          AND E.operationId = (SELECT cancelcurricularcomponentoperation FROM finDefaultOperations));
        V_PRECO := COALESCE(V_SUMVALUE, 0) - COALESCE(V_SUBVALUE, 0);

        -- número de parcelas bloqueadas
        V_BPCL := COUNT(*) FROM finreceivableinvoice A 
                          WHERE A.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate                        
                            AND iscanceled = 'f'
                            AND getblockedinvoice(p_contractId, p_learningPeriodId, A.parcelNumber, 0, 0) IS NOT NULL
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.learningPeriodId = p_learningPeriodId)                      
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.contractId = p_contractId);

        -- Valor de incentivo bloqueado
        V_SUMVALUE := (SELECT SUM(E.value)
                         FROM ONLY finInvoice I
                   INNER JOIN finEntry E
                           ON E.invoiceId = I.invoiceId
                        WHERE I.isCanceled IS FALSE
                          AND I.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate
                          AND E.incentiveTypeId = v_incentives.incentivetypeid
                          AND getblockedinvoice(p_contractId, p_learningPeriodId, I.parcelNumber, 0, 0) IS NOT NULL
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.learningPeriodId = p_learningPeriodId)                      
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.contractId = p_contractId)
                          AND E.operationId IN (SELECT UNNEST(ARRAY[operationid, repaymentoperation]) FROM ONLY finIncentiveType WHERE incentiveTypeId = v_incentives.incentivetypeid));
        V_SUBVALUE := (SELECT SUM(E.value)
                         FROM ONLY finInvoice I
                   INNER JOIN finEntry E
                           ON E.invoiceId = I.invoiceId
                        WHERE I.isCanceled IS FALSE
                          AND I.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate
                          AND E.incentiveTypeId = v_incentives.incentivetypeid
                          AND getblockedinvoice(p_contractId, p_learningPeriodId, I.parcelNumber, 0, 0) IS NOT NULL
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.learningPeriodId = p_learningPeriodId)                      
                          AND EXISTS (SELECT 1
                                        FROM finEntry X
                                       WHERE X.invoiceId = I.invoiceId
                                         AND X.contractId = p_contractId)
                          AND E.operationId = (SELECT paymentOperation FROM ONLY finIncentiveType WHERE incentiveTypeId = v_incentives.incentivetypeid));
        V_VIB := COALESCE(V_SUMVALUE, 0) - COALESCE(V_SUBVALUE, 0);

        -- Valor de incentivo
        IF v_incentives.valueispercent IS TRUE THEN
            V_VI := ( v_incentives.value / 100 ) * V_PRECO;
        ELSE
            V_VI := v_incentives.value;
        END IF;

        -- Parcela de incentivo
        V_VPI := V_VI / V_NPCL;

        -- nÃºmero de parcelas abertas (nÃ£o bloqueadas)
        V_APCL := V_NPCL - V_BPCL;

        --Calcula valor de ajuste somente se exisitirem parcelas em aberto
        IF V_APCL > 0 
        THEN 
            -- Valor do ajuste de cada parcela
            V_AJT := ((V_VPI * V_BPCL) - V_VIB) / V_APCL;
        ELSE
            V_AJT := 0;
        END IF;


        --
        -- Percorre os tÃ©tulos abertos para lanÃ©ar o valor de incentivo
        --
        FOR v_invoice IN SELECT * FROM finreceivableinvoice A 
                          WHERE A.referenceMaturityDate BETWEEN v_incentives.startDate AND v_incentives.endDate                        
                            AND iscanceled = 'f'
                            AND getblockedinvoice(p_contractId, p_learningPeriodId, A.parcelNumber, 0, 0) IS NULL
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.learningPeriodId = p_learningPeriodId)                      
                            AND EXISTS (SELECT 1
                                          FROM finEntry X
                                         WHERE X.invoiceId = A.invoiceId
                                           AND X.contractId = p_contractId)
        LOOP
            IF v_paymentIncentiveOperationId IS NOT NULL 
            THEN
                DELETE FROM finentry 
                      WHERE invoiceid = v_invoice.invoiceid
                        AND incentiveTypeId = v_incentives.incentivetypeid
                        AND operationid IN (SELECT UNNEST(ARRAY[operationid, repaymentoperation, paymentoperation]) FROM ONLY finIncentiveType WHERE incentiveTypeId = v_incentives.incentivetypeid);

                IF (V_VPI + V_INL) > 0 THEN
                    V_IVALUE := balance(v_invoice.invoiceid);
                    -- Verifica se existe saldo no tÃ©tulo para o lanÃ©amento do incentivo
                    IF (V_VPI + V_INL) <= V_IVALUE THEN
                        V_IVALUE := V_VPI + V_INL;
                    ELSE
                        V_INL := (V_VPI + V_INL) - V_IVALUE;
                    END IF;

                    -- Insere lanÃ©amento com o valor de incentivo
                    INSERT INTO finEntry(invoiceId, operationId, entryDate, value, costcenterid, contractid, learningperiodid, incentivetypeid) VALUES (
                        v_invoice.invoiceId,
                        v_incentiveOperationId,
                        NOW(),
                        ROUND(V_IVALUE::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                        v_invoice.costCenterId,
                        V_contract.contractid,
                        V_learningperiod.learningperiodid,
                        v_incentives.incentivetypeid
                    );
                END IF;

                IF V_AJT > 0 THEN
                    V_IVALUE := balance(v_invoice.invoiceid);
                    -- Verifica se existe saldo para lanÃ©amento do ajuste
                    IF (V_AJT + V_ANL) <= V_IVALUE THEN
                        V_IVALUE := V_AJT + V_ANL;
                    ELSE
                        V_ANL := (V_AJT + V_ANL) - V_IVALUE;
                    END IF;

                    -- Insere lanÃ©amento com o valor de ajuste

                    IF V_IVALUE <> 0 THEN
                        INSERT INTO finEntry(invoiceId, operationId, entryDate, value, costcenterid, contractid, learningperiodid, incentivetypeid) VALUES (
                            v_invoice.invoiceId,
                            v_repaymentIncentiveOperationId,
                            NOW(),
                            ROUND(V_IVALUE::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                            v_invoice.costCenterId,
                            V_contract.contractid,
                            V_learningperiod.learningperiodid,
                            v_incentives.incentivetypeid
                        );
                    END IF;
                ELSE
                    IF V_AJT < 0 THEN
                        -- Insere lanÃ©amento com o valor de ajuste
                        INSERT INTO finEntry(invoiceId, operationId, entryDate, value, costcenterid, contractid, learningperiodid, incentivetypeid) VALUES (
                        v_invoice.invoiceId,
                        v_paymentIncentiveOperationId,
                        NOW(),
                        ROUND((V_AJT * (-1))::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                        v_invoice.costCenterId,
                        V_contract.contractid,
                        V_learningperiod.learningperiodid,
                        v_incentives.incentivetypeid );
                    END IF;
                END IF;
            ELSE
                RAISE EXCEPTION 'O código da operação não foi encontrado, verifique se as datas iniciais e finais dos incentivos da pessoa estão dentro da data inicial e final do período letivo.';
            END IF;
        END LOOP;
    END LOOP;

    RETURN TRUE;
END;
$BODY$
  LANGUAGE 'plpgsql';
