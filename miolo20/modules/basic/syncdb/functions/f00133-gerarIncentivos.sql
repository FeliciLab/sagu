CREATE OR REPLACE FUNCTION gerarIncentivos(p_contractid integer, p_learningperiodid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: gerarincentivos
  PURPOSE: gera os incentivos de um contrato em determinado periodo letivo

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/03/2013 Leovan Tavares    1. Funcao criada.
**************************************************************************************/
DECLARE
    -- Valor nominal dos titulos que devem receber incentivo
    PRECO numeric;
    -- Valor de incentivo a conceder
    VINC numeric;
    -- Valor de incentivo bloqueado
    VBLK numeric;
    -- Valor de incentivo a lancar
    VALC numeric;
    -- Valor de incentivo lancado na parcela
    VLEX numeric;
    -- Valor por parcela
    VPCL numeric;
    -- Valor do lancamento
    VLNC numeric;
    -- Valor nao lancado por falta de saldo
    VNLC numeric;
    -- Numero de parcelas a conceder o incentivo (nao bloqueadas)
    NPCL integer;
    
    -- Objeto para representar um incentivo
    v_incentivo finincentive;
    -- Objeto para representar um tipo de incentivo
    v_tipo_incentivo finincentivetype;
    -- Objeto para representar o contrato
    v_contrato acdcontract;
    -- Objeto para representar o peri­odo letivo
    v_periodo acdlearningperiod;
    
    -- Variaveis para receber informacoes dos ti­tulos que devem receber incentivo
    v_titulos_npcl integer;
    v_titulos_preco numeric;
    
    -- Variaveis para receber informacoes dos titulos bloqueados
    v_bloqueados_bpcl integer;
    v_bloqueados_pblk numeric;
    v_bloqueados_vblk numeric;
    
    -- Objeto para representar um ti­tulo
    v_titulo finreceivableinvoice;

    --Títulos abertos com incentivos cancelados
    v_titulos_incentivo_cancelado finreceivableinvoice;

    --Informa se titulos bloqueados podem ou não receber incentivos
    v_somenteTitulosEmDia boolean;

    --Saldo nao bloqueado
    v_saldo numeric;

    -- Verifica se o incentivo é do tipo patrocinador
    v_patrocinador RECORD;

    -- Verifica se o incentivo é do tipo financiamento
    v_financiamento RECORD;

    -- Obtém o sequencial da tabela finentry
    v_entryid INTEGER;

    -- Personid da pessoa vínculada ao financiamento
    v_personid INTEGER;

    -- Recebe o titulo do financiador, caso exista
    v_tituloFinanciador INTEGER;

    -- Recebe o valor do lançamento para o financiador a ser descontado
    v_lancamentoFinanciador NUMERIC;
    
    -- Recebe o balance dos títulos que irão receber incentivo
    v_titulos_balance numeric;
BEGIN
    -- Obter contrato
    SELECT INTO v_contrato * FROM acdcontract WHERE contractid = p_contractId;
    
    -- Obter peri­odo letivo
    SELECT INTO v_periodo * FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId;

    --Percorre lançamentos em aberto que possuem lançamentos de incentivos cancelados
    FOR v_titulos_incentivo_cancelado IN SELECT *
                          FROM finreceivableinvoice A
                         WHERE iscanceled IS FALSE
                           AND titulobloqueado(invoiceid, true) IS FALSE
                           --Contrato
                           AND EXISTS (SELECT 1 FROM finentry
                                       WHERE invoiceid = A.invoiceid
                                         AND contractid = p_contractId)
                           --Período letivo
                           AND EXISTS (SELECT 1 FROM finentry AA INNER JOIN acdlearningperiod BB USING (learningperiodid)
                                       WHERE AA.invoiceid = A.invoiceid
                                         AND BB.periodid = v_periodo.periodid)
                           --Possua lancamento de incentivo cancelado
                           AND EXISTS (SELECT 1 FROM finentry E 
                                    LEFT JOIN finIncentive I 
                                           ON ( I.contractid = p_contractid
                                          AND I.incentivetypeid = E.incentivetypeid
                                          AND A.referencematuritydate BETWEEN I.startDate and I.endDate
                                          AND ( I.cancellationdate IS NULL OR I.cancellationdate > A.referencematuritydate ) )
                                        WHERE E.invoiceid = A.invoiceid
                                          AND E.incentivetypeid IS NOT NULL
                                          AND I.incentiveid IS NULL )
    LOOP
        --Remove lançamento do incentivo cancelado
        DELETE FROM finentry WHERE entryid IN ( SELECT E.entryid 
                                                    FROM finentry E 
                                                    LEFT JOIN finIncentive I ON ( I.contractid = p_contractid
                                                          AND I.incentivetypeid = E.incentivetypeid
                                                          AND v_titulos_incentivo_cancelado.referencematuritydate BETWEEN I.startDate and I.endDate
                                                          AND ( I.cancellationdate IS NULL OR I.cancellationdate > v_titulos_incentivo_cancelado.referencematuritydate ) )
                                                        WHERE E.invoiceid = v_titulos_incentivo_cancelado.invoiceid
                                                          AND E.incentivetypeid IS NOT NULL
                                                          AND I.incentiveid IS NULL);
    END LOOP;

    -- Verificar incentivos ativos no periodo letivo
    FOR v_incentivo IN SELECT * 
                         FROM finincentive 
                        WHERE contractid = p_contractId 
                          AND (v_periodo.beginDate, v_periodo.endDate) OVERLAPS (startDate, endDate)
                     ORDER BY concedersobre, prioridade
    LOOP
        -- Obter o tipo de incentivo
        SELECT INTO v_tipo_incentivo * FROM ONLY finincentivetype WHERE incentivetypeid = v_incentivo.incentivetypeid;

        SELECT INTO v_patrocinador * FROM finSupport WHERE incentivetypeid = v_incentivo.incentivetypeid;
        
        SELECT INTO v_financiamento * FROM finLoan WHERE incentivetypeid = v_incentivo.incentivetypeid;
        
        -- Obtem o numero de ti­tulos sobre o qual aplicar o incentivo
        SELECT INTO v_titulos_npcl, v_titulos_preco, v_titulos_balance 
                              COUNT(*), SUM(nominalvalue), SUM(A.balance)
                         FROM finreceivableinvoice A
                        WHERE referencematuritydate BETWEEN v_incentivo.startdate AND v_incentivo.enddate
                          AND iscanceled IS FALSE
                          AND parcelnumber > 0
                          AND EXISTS (SELECT 1 FROM finentry
                                       WHERE invoiceid = A.invoiceid
                                         AND contractid = p_contractId)
                          AND EXISTS (SELECT 1 FROM finentry AA INNER JOIN acdlearningperiod BB USING (learningperiodid)
                                       WHERE AA.invoiceid = A.invoiceid
                                         AND BB.periodid = v_periodo.periodid) ;

        -- Obtem informacoes sobre os titulos bloqueados
        SELECT INTO v_bloqueados_bpcl, v_bloqueados_pblk, v_bloqueados_vblk 
                    COUNT(*), 
                    SUM(nominalvalue), 
                    SUM(incentivevalue)
          FROM obtertitulosbloqueadosparaincentivos(p_contractId, p_learningPeriodId, v_incentivo.incentivetypeid, v_incentivo.startdate, v_incentivo.enddate);

        -- Alimentando variaveis
        IF v_incentivo.concederSobre = 'N' 
        THEN
            PRECO := COALESCE(v_titulos_preco, 0);
        ELSE
            PRECO := COALESCE(v_titulos_balance, 0);
        END IF;

        VBLK := COALESCE(v_bloqueados_vblk, 0);
        NPCL := COALESCE(v_titulos_npcl, 0) - COALESCE(v_bloqueados_bpcl, 0);

        -- Obtencao do valor de incentivo a conceder (VINC)
        -- Se o valor e em percentual
        IF v_incentivo.valueispercent IS TRUE 
        THEN
            VINC := PRECO * (v_incentivo.value / 100);
        ELSE
            VINC := v_incentivo.value;
        END IF;

        -- Valor a conceder
        VALC := VINC - VBLK;

        CONTINUE WHEN VALC <= 0 OR NPCL <= 0;
        
        --Alterado o código abaixo, pois devido a essa validação
        --não estava considerando se havia mais incentivos lançados para a pessoa.        
        --IF VALC <= 0 OR NPCL <= 0 THEN
        --    RETURN TRUE;
        --END IF;
        
        VPCL := VALC / NPCL;
        VLEX:= 0;

        --Abater parcelas subsequentes com valor pago
        IF v_incentivo.opcaodeajuste = 'A'
        THEN
            --Mantém o valor das parcelas como o valor de todas parcelas do periodo incluindo bloqueadas
            VPCL := VALC / COALESCE(v_titulos_npcl, 0);
            --Calcula a diferença de incentivo não recebida nas bloqueadas para abater na proxima parcela
            VLEX := ((VALC / NPCL) - (VALC / COALESCE(v_titulos_npcl, 0))) * NPCL; 

        END IF;

        -- Inicializa o valor nao lancado
        VNLC := 0;

        -- Percorre os titulos abertos fazendo os lancamentos
        FOR v_titulo IN SELECT *
                          FROM finreceivableinvoice A
                         WHERE iscanceled IS FALSE
                           AND titulobloqueado(invoiceid, true) IS FALSE
                           AND referencematuritydate BETWEEN v_incentivo.startdate AND v_incentivo.enddate
                           AND EXISTS (SELECT 1 FROM finentry
                                       WHERE invoiceid = A.invoiceid
                                         AND contractid = p_contractId)
                           AND EXISTS (SELECT 1 FROM finentry AA INNER JOIN acdlearningperiod BB USING (learningperiodid)
                                       WHERE AA.invoiceid = A.invoiceid
                                         AND BB.periodid = v_periodo.periodid)
                           AND A.parcelnumber <> 0
                           ORDER BY A.parcelnumber
        LOOP
            -- Primeiramente exclui todos os lancamentos referentes ao incentivo
            DELETE FROM finentry 
             WHERE invoiceid = v_titulo.invoiceid 
               AND operationid IN (v_tipo_incentivo.operationid, v_tipo_incentivo.paymentoperation, v_tipo_incentivo.repaymentoperation);

            -- Se esta-se utilizando a primeira parcela integral, pode haver diferença no valor
            -- das parcelas (nao sao todas iguais). Neste caso, lanca-se o incentivo proporcionalmente
            IF (getParameter('FINANCE', 'PRIMEIRA_PARCELA_INTEGRAL') = 'YES') THEN
                v_saldo = PRECO - COALESCE(v_bloqueados_pblk, 0);

                IF v_saldo > 0 THEN
                    IF v_incentivo.concederSobre = 'N' 
                    THEN
                        VPCL := VALC * (COALESCE(v_titulo.nominalvalue, 0)/v_saldo);
                    ELSE
                        VPCL := VALC * (COALESCE(v_titulo.balance, 0)/v_saldo);
                    END IF;
                END IF;
            END IF;

             -- O valor a lancar e o valor programado mais o que ficou 'para tras' dos outros ti­tulos
            VLNC := VPCL + VNLC + VLEX;
            VNLC := 0;   
            VLEX := 0;

            -- Verifica se ha saldo no ti­tulo para lancar o incentivo
            IF VLNC > balance(v_titulo.invoiceid) THEN
                -- Se valor e maior que o saldo do ti­tulo, lanca o valor disponivel e atualiza VNLC
                VNLC := VLNC - balance(v_titulo.invoiceid);
                VLNC := balance(v_titulo.invoiceid);
            END IF;
            
            IF ( VLNC > 0) 
            THEN
                v_entryid := nextval('seq_entryid');
                -- Insere o lancamento do incentivo
                INSERT INTO  finentry
                            (entryid,
                             invoiceid,
                             operationid,
                             entrydate,
                             value,
                             costcenterid,
                             contractid,
                             learningperiodid,
                             incentivetypeid)
                     VALUES (v_entryid,
                             v_titulo.invoiceid,
                             v_tipo_incentivo.operationid,
                             now()::date,
                             ROUND(VLNC::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                             v_titulo.costcenterid,
                             p_contractId,
                             p_learningPeriodId,
                             v_incentivo.incentivetypeid);

                -- Insere o lançamento para o patrocinador/financiador
                IF v_patrocinador.incentivetypeid IS NOT NULL AND v_patrocinador.geraTituloDeCobranca IS TRUE AND v_incentivo.supporterid IS NOT NULL
                THEN
                    RAISE NOTICE 'gerarTitulosDoIncentivo patrocinador % - % - % - %',v_incentivo.supporterid, v_titulo.invoiceid, v_entryid, v_incentivo.incentiveid;
                    PERFORM gerarTitulosDoIncentivo(v_incentivo.supporterid, v_titulo.invoiceid, v_entryid, v_incentivo.incentiveid);
                END IF;

                IF v_financiamento.incentivetypeid IS NOT NULL AND v_financiamento.geraTituloDeCobranca IS TRUE AND v_financiamento.loanerid IS NOT NULL
                THEN
                    
                    -- Se o financiador é a instituição, então o novo título é gerado para o aluno
                    IF (SELECT companyId 
                          FROM basCompanyConf
                         WHERE personId = v_financiamento.loanerid) = GETPARAMETER('BASIC','DEFAULT_COMPANY_CONF')::INTEGER
                    THEN
                        v_personid := v_contrato.personid;
                    ELSE
                        v_personid := v_financiamento.loanerid;
                    END IF;
                    
                    RAISE NOTICE 'gerarTitulosDoIncentivo financiador % - % - % - %',v_personid, v_titulo.invoiceid, v_entryid, v_incentivo.incentiveid;
                    PERFORM gerarTitulosDoIncentivo(v_personid, v_titulo.invoiceid, v_entryid, v_incentivo.incentiveid);
                END IF;

                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Concessão de incentivos: Sim' , '7 - PARCELA-' || v_titulo.parcelnumber);
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas que terão concessão de incentivos:' , '5 - CÁLCULOS EFETUADOS');
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor de incentivo é percentual: '||(CASE WHEN v_incentivo.valueispercent IS TRUE THEN 'Sim' ELSE 'Não'END), '7 - PARCELA-' || v_titulo.parcelnumber);
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor de incentivo na parcela: R$'||ROUND(VLNC::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer), '7 - PARCELA-' || v_titulo.parcelnumber);
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Inserindo lançamento de incentivo - Operação: '|| COALESCE(v_tipo_incentivo.operationid::text,'Não definido')|| '-'|| (SELECT coalesce(description,'') FROM finoperation WHERE operationid = v_tipo_incentivo.operationid) ||'   Data do lançamento: '|| TO_CHAR(now()::date, 'dd/mm/yyyy') ||'   Valor: R$ ' || COALESCE(ROUND(VLNC::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),0) || '   Centro de custo: ' || COALESCE(v_titulo.costcenterid||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_titulo.costcenterid),'') , '7 - PARCELA-' || v_titulo.parcelnumber);

                -- Atualiza saldo do titulo
                UPDATE fininvoice SET value = ROUND(balance(v_titulo.invoiceid)::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer) WHERE invoiceid = v_titulo.invoiceid;
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor atualizado da parcela: R$'||ROUND(balance(v_titulo.invoiceid)::numeric) , '7 - PARCELA-' || v_titulo.parcelnumber);
            
            ELSE
                IF balance(v_titulo.invoiceid) = 0
                THEN
                    v_tituloFinanciador := NULL;
                    v_lancamentoFinanciador := NULL;

                    -- Verifica se é necessário inserir lançamento para o financiador, caso os títulos oriundos do aluno estejam zerados, deve zear o incentivo lançado também
                    SELECT INTO v_tituloFinanciador titulodereferencia 
                      FROM ONLY finreceivableinvoice 
                          WHERE invoiceid = v_titulo.invoiceid;

                    IF v_tituloFinanciador IS NOT NULL
                    THEN
                        SELECT INTO v_lancamentoFinanciador value
                               FROM finEntry
                              WHERE invoiceid = v_tituloFinanciador
                                AND titulodereferencia = v_titulo.invoiceid
                                AND (((SELECT COALESCE(SUM(value), 0)
                                        FROM finEntry
                                  INNER JOIN finoperation
                                       USING (operationid)
                                       WHERE invoiceid = v_tituloFinanciador
                                         AND titulodereferencia = v_titulo.invoiceid
                                         AND operationtypeid = 'D') - (SELECT COALESCE(SUM(value),0)
                                                                         FROM finEntry
                                                                   INNER JOIN finoperation
                                                                        USING (operationid)
                                                                        WHERE invoiceid = v_tituloFinanciador
                                                                          AND titulodereferencia = v_titulo.invoiceid
                                                                          AND operationtypeid = 'C')) > 0);
                    END IF;

                    IF v_tituloFinanciador IS NOT NULL AND v_lancamentoFinanciador IS NOT NULL
                    THEN
                        INSERT INTO finentry
                                    (invoiceid,
                                     operationid,
                                     entrydate,
                                     value,
                                     costcenterid,
                                     contractid,
                                     learningperiodid,
                                     incentivetypeid)
                             VALUES (v_tituloFinanciador,
                                     v_tipo_incentivo.rePaymentOperation,
                                     now()::date,
                                     ROUND(v_lancamentoFinanciador::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                                     v_titulo.costcenterid,
                                     p_contractId,
                                     p_learningPeriodId,
                                     v_incentivo.incentivetypeid);
                    END IF;
                END IF;
            END IF;
        END LOOP;
    END LOOP;
    
    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql;
--
