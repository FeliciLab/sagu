--
-- 04/12/2012 - Jonas Gualberto Diel - Busca e gera as taxas de matrícula
--
CREATE OR REPLACE FUNCTION generateEnrollFeeByInvoiceId(p_invoiceId integer)
  RETURNS boolean AS
$BODY$
DECLARE    
    v_enrollFee RECORD; --Taxas de matrícula
    v_entries RECORD; --Lanéamentos
    v_parcelNumber INT; --Numero da parcela
    v_value NUMERIC; --Valor da taxa
    v_entry RECORD; --Lanéamento para aglutinar taxa   
    v_entryId INT; --Lanéamento para aglutinar taxa
BEGIN    
    --número da parcela
    v_parcelNumber := parcelNumber FROM finReceivableInvoice WHERE invoiceId = p_invoiceId;

    --Percorrer cada taxa de matrícula do peréodo letivo
    FOR v_enrollFee IN SELECT * FROM finEnrollFee WHERE learningPeriodId IN (SELECT learningPeriodId FROM finEntry WHERE invoiceid = p_invoiceId AND learningPeriodId IS NOT NULL)
    LOOP
        --Percorrer cada lanéamento do tétulo que possua contrato
        FOR v_entries IN SELECT * FROM finEntry WHERE invoiceId = p_invoiceId AND contractId IS NOT NULL
        LOOP
            IF ( v_entries.operationId IN (SELECT renewaloperation FROM findefaultOperations UNION SELECT enrolloperation FROM findefaultOperations) ) AND ( v_parcelNumber <= v_enrollFee.parcelsnumber ) AND ( (v_enrollFee.isfreshman AND isfreshmanbyperiod(v_entries.contractId, (SELECT periodid
                                                                                                                                                                                                                                                                                         FROM acdLearningPeriod
                                                                                                                                                                                                                                                                                        WHERE learningPeriodId = v_enrollFee.learningperiodid)::VARCHAR)) OR ( v_enrollFee.isfreshman IS FALSE AND isfreshmanbyperiod(v_entries.contractId, (SELECT periodid
                                                                                                                                                                                                                                                                                                                                                                                                                                               FROM acdLearningPeriod
                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE learningPeriodId = v_enrollFee.learningperiodid)::VARCHAR) IS FALSE ) )
            THEN
                -- Procurar por outro lanéamento igual. se existir, não inserir novo, pois
                --a taxa de matrícula do contrato jé teré sido gerada
                IF COUNT(*) <= 0 FROM finEntry WHERE invoiceId = p_invoiceId AND contractId = v_entries.contractId AND operationId = v_enrollFee.operationId AND learningPeriodId = v_enrollFee.learningPeriodId
                THEN
                    -- Se valor é percentual, obter a soma dos lanéamentos do tipo mensalidade
                    --para o tétulo atual e aplicar o percentual para definir o valor
                    IF v_enrollFee.valueIsPercent IS TRUE
                    THEN
                        -- aplicar percentual sobre lanéamento atual, que seré sempre de matrícula ou renovação  
                        v_value := v_entries.value * ( v_enrollFee.value /100 );
                    ELSE
                        --Se o valor é fixo, simplismente gerar o novo valor
                        v_value := v_enrollFee.value;
                    END IF;

                    --Caso ja existir um lanéamento aglutina o lanéamento
                    IF getParameter('FINANCE', 'AGGLUTINATE_INVOICE_ENTRIES') = 'YES'
                    THEN
                        SELECT INTO v_entry * FROM finEntry 
                                                WHERE invoiceId = p_invoiceId 
                                                  AND contractId = v_entries.contractId 
                                                  AND operationId = v_enrollFee.operationId
                                                  AND learningPeriodId = v_enrollFee.learningPeriodId
                                                  AND isaccounted IS FALSE
                                                  AND creationtype = v_entries.creationtype
                                                  AND costcenterid = v_entries.costcenterid;   
                        
                        v_entryId := v_entry.entryId; --Atribui o codigo para aglutinar

                    END IF;

                    --Realiza o update
                    UPDATE finEntry
                       SET invoiceId = p_invoiceId,
                           operationId = v_enrollFee.operationId,
                           entryDate = now()::date,
                           value = ROUND(v_entries.value + v_value,2),
                           costCenterId = v_entries.costCenterId,
                           comments = E'\n Aglutinada parcela '||v_parcelNumber||' de '|| v_enrollFee.parcelsNumber ||' da taxa '|| v_enrollFee.operationId ||' - '|| (SELECT description FROM finoperation WHERE operationId = v_enrollFee.operationId ) ||'.',
                           bankReturnCode = v_entries.bankReturnCode,
                           isAccounted = v_entries.isAccounted,
                           contractId = v_entries.contractId,
                           learningPeriodId = v_entries.learningPeriodId,
                           bankMovementId = v_entries.bankMovementId
                     WHERE entryId  = v_entryId;

                     --Caso lanéamento não existir insere o lanéamento da taxa
                     IF NOT FOUND THEN
                        INSERT INTO finEntry (
                                    invoiceid, 
                                    operationId, 
                                    entryDate, 
                                    value, 
                                    costcenterId, 
                                    comments, 
                                    bankreturncode, 
                                    isaccounted, 
                                    contractid, 
                                    learningPeriodId,
                                    bankMovementId )
                                    VALUES
                                  ( p_invoiceId,
                                    v_enrollFee.operationId,
                                    now()::date,
                                    ROUND(v_value,2),
                                    v_entries.costCenterId,
                                    E'\n Parcela '||v_parcelNumber||' de '|| v_enrollFee.parcelsNumber ||' da taxa '|| v_enrollFee.operationId ||' - '|| (SELECT description FROM finoperation WHERE operationId = v_enrollFee.operationId ) ||'.',
                                    v_entries.bankReturnCode,
                                    v_entries.isAccounted,
                                    v_entries.contractId,
                                    v_entries.learningPeriodId,
                                    v_entries.bankMovementId );
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
