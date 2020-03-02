CREATE OR REPLACE FUNCTION cr_fin_lancamentos_contasreceber(p_dataini DATE, p_datafim DATE, p_tipo CHAR)
RETURNS SETOF DadosContabeis AS
$BODY$

/*************************************************************************************
  NAME: cr_fin_lancamentos_contasreceber
  PURPOSE: Retorna informações contábeis de lançamentos de contas a receber.
           Recebe os filtros de data inicial, final, e o tipo se deve ser 
           pela data de caixa ou competência: 
           CA-De caixa; CO-De competência;

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- -----------------         ------------------------------------
  1.0       22/12/2014 Augusto Alves da Silva    1. Função criada.
  1.1       14/01/2015 Luís F. Wermann           1.1 Função atualizada para trabalhar
                                                 com pagamentos adiantados.
**************************************************************************************/
DECLARE
    v_string0_sem_adiantamento VARCHAR;
    v_string0_com_adiantamento VARCHAR;
    v_string1 VARCHAR;
    v_string2 VARCHAR;
    v_string3_sem_adiantamento VARCHAR;
    v_string3_com_adiantamento VARCHAR;
    v_string4 VARCHAR;
    v_string5 VARCHAR;
    v_string5_sem_adiantamento VARCHAR;
    v_string5_com_adiantamento  VARCHAR;
    v_contas_sem_adiantamento VARCHAR;
    v_contas_com_adiantamento VARCHAR;
    v_sql VARCHAR;
    v_tipo INT;
    v_from_sem_adiantamento VARCHAR;
    v_from_com_adiantamento VARCHAR;
    v_orderBy VARCHAR;
BEGIN
    IF p_tipo = 'CA' THEN
        v_tipo := 1;
    ELSEIF p_tipo = 'CO' THEN
        v_tipo := 2;
    END IF;

--As datas incial e final não podem ser de meses diferentes
--Ex.: 05/01/2015 até 05/02/2015 está ERRADO, precisam ficar no mesmo mês
IF ( EXTRACT('MONTH' FROM p_dataini::DATE) <> EXTRACT('MONTH' FROM p_datafim::DATE) OR
     EXTRACT('YEAR' FROM p_dataini::DATE) <> EXTRACT('YEAR' FROM p_datafim::DATE) )
THEN
    RAISE EXCEPTION 'Desculpe, mas as datas incial e final para a geração dos dados contábeis devem estar dentro do mesmo mês do mesmo ano.';
END IF;

v_string0_sem_adiantamento := '  
-- Verificar se o lançamento tem vínculo com cheque para obter a data contábil a partir das movimentações do cheque
SELECT 
(CASE WHEN verificaSeLancamentoTemVinculoComCheque(A.cod_lancamento)
THEN
    (SELECT data_contabil FROM obtemChequeParaContabilizacao(A.cod_lancamento, MCH.movimentacaoChequeId))::VARCHAR
ELSE 
    TO_CHAR(A.data_competencia_lancamento, getParameter(\'BASIC\', \'MASK_DATE\'))::VARCHAR
END) AS data_contabil_lancamento, ';

v_string0_com_adiantamento := '    
SELECT TO_CHAR(D.maturityDate, getParameter(\'BASIC\', \'MASK_DATE\'))::VARCHAR AS data_contabil_lancamento,  ';

v_string1 := ' 
         dateToUser(A.data_de_caixa)::VARCHAR AS data_caixa,
         TO_CHAR(A.data_competencia_lancamento, getParameter(\'BASIC\', \'MASK_DATE\'))::VARCHAR AS data_competencia,
                E.personId AS codigo_aluno,
                (CASE WHEN (SELECT INOME.personId || \'-\' || getPersonName(INOME.personId) 
                         FROM ONLY finInvoice INOME
                        INNER JOIN finEntry ENOME
                                ON (ENOME.invoiceId = INOME.invoiceId)
                             WHERE ENOME.entryId = A.cod_lancamento) IS NOT NULL
                      THEN
                          E.name || \'(\' || (SELECT INOME.personId || \'-\' || getPersonName(INOME.personId) 
                                         FROM ONLY finInvoice INOME
                                        INNER JOIN finEntry ENOME
                                                ON (ENOME.invoiceId = INOME.invoiceId)
                                             WHERE ENOME.entryId = A.cod_lancamento) || \')\'
                      ELSE
                          E.name
                 END) AS nome_aluno, 
                A.cod_titulo_ref AS titulo,
                D.parcelNumber AS parcela,
                datetouser(A.data_vencimento_titulo)::VARCHAR AS vencimento,
                A.valor_lancamento AS valor_lancamento,
                B.description AS operacao,
                A.cod_tipo_operacao_lancamento::CHAR AS tipo_operacao,
                --Caso algum lançamento do título for originário de uma negociação deve obter como centro de custo
                --o centro de custo do título original (que foi baixado)
                (CASE WHEN D.invoiceId IS NOT NULL
                THEN
                   (CASE WHEN ( (SELECT invoiceId FROM obtemTitulosNegociadoDoTitulo(D.invoiceId) LIMIT 1) > 0 )
                         THEN
                             (SELECT costCenterId
                                FROM finReceivableInvoice
                               WHERE invoiceId = (SELECT invoiceId FROM obtemTitulosNegociadoDoTitulo(D.invoiceId) LIMIT 1))
                         ELSE
                             A.cod_centrodecusto
                    END)
                ELSE 
                   A.cod_centrodecusto
                END) AS codigo_centro_de_custo,
                (CASE WHEN D.invoiceId IS NOT NULL
                THEN
                    (CASE WHEN ( (SELECT invoiceId FROM obtemTitulosNegociadoDoTitulo(D.invoiceId) LIMIT 1) > 0 )
                          THEN
                              (SELECT BCENTRO.description
                                 FROM finReceivableInvoice ACENTRO
                           INNER JOIN accCostCenter BCENTRO
                                   ON (ACENTRO.costCenterId = BCENTRO.costCenterId)
                                WHERE ACENTRO.invoiceId = (SELECT invoiceId FROM obtemTitulosNegociadoDoTitulo(D.invoiceId) LIMIT 1))
                          ELSE
                              C.description
                     END)
                ELSE 
                    C.description
                END) AS centro_de_custo, ';

v_contas_sem_adiantamento := 
'  --Caso o lançamento seja oriundo de uma negociação a conta de débito será da de acordo
  (CASE WHEN ( (SELECT invoiceId FROM obtemTitulosNegociadoDoLancamento(A.cod_lancamento) LIMIT 1) > 0 )
        THEN
            CASE WHEN D.invoiceId IS NOT NULL
                 THEN
                    COALESCE(obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId), COALESCE(obterPlanoDeContasDoLancamentoPrincipalDoTitulo((SELECT invoiceId FROM obtemTitulosNegociadoDoLancamento(A.cod_lancamento) LIMIT 1)), getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_CREDITO_PADRAO\')))
                 ELSE
                    getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\')
            END
        ELSE
            --Caso a operação do lançamento seja de mensalidade, juros e multa ou acréscimo bancário, pega a conta de débito do lançamento principal do título
            CASE WHEN B.operationId IN (SELECT UNNEST(ARRAY[monthlyfeeoperation, interestOperation, otheradditionsoperation])
                                                        FROM finDefaultOperations)
                 THEN
                     CASE WHEN D.invoiceId IS NOT NULL
                        THEN
                            obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId)
                        ELSE
                            getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\')
                     END
                --Quando a operação for de taxa bancária ou de desconto pega da própria operação, senão do padrão
                WHEN (B.operationId IN (SELECT UNNEST(ARRAY[bankTaxOperation, discountOperation]) FROM finDefaultOperations))
                THEN
                    COALESCE(B.accountSchemeId, getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\'))
                ELSE
                -- Verificar se o lançamento tem vínculo com cheque para obter a conta contábil a partir das movimentações do cheque
                CASE WHEN verificaSeLancamentoTemVinculoComCheque(A.cod_lancamento)
                    THEN
                       (SELECT codigo_conta_contabil FROM obtemChequeParaContabilizacao(A.cod_lancamento, MCH.movimentacaoChequeId))
                    ELSE
                       --Por padrão obtém a conta configurada para o curso, ou da operação, ou por último da da movimentação de caixa/banco do título.
                       CASE WHEN CA.accountSchemeId IS NOT NULL
                            THEN 
                                CA.accountSchemeId 
                            WHEN B.accountSchemeId IS NOT NULL
                            THEN
                                B.accountSchemeId
                            WHEN (SELECT bankMovementId FROM obterMovimentacaoBancariaDoLancamento(A.cod_lancamento, 1)) IS NOT NULL 
                            THEN
                                (SELECT BXK.accountSchemeId
                                   FROM fin.BankMovement BX
                             INNER JOIN finBankAccount BXK
                                     ON (CASE WHEN BX.bankAccountId IS NOT NULL 
                                              THEN 
                                                  BX.bankAccountId = BXK.bankAccountId
                                              ELSE
                                                  BXK.bankAccountId = D.bankAccountId
                                         END)
                                  WHERE BX.bankMovementId = (SELECT bankMovementId FROM obterMovimentacaoBancariaDoLancamento(A.cod_lancamento, 1)))
                            WHEN (SELECT counterMovementId FROM obterMovimentacaoDeCaixaDoLancamento(A.cod_lancamento, 1)) IS NOT NULL 
                            THEN
                                (SELECT CXC.accountSchemeId
                                   FROM finCounterMovement CXM
                             INNER JOIN finOpenCounter CXO
                                     ON (CXO.openCounterId = CXM.openCounterId)
                             INNER JOIN finCounter CXC
                                     ON (CXC.counterId = CXO.counterId)
                                  WHERE CXM.counterMovementId = (SELECT counterMovementId FROM obterMovimentacaoDeCaixaDoLancamento(A.cod_lancamento, 1)))
                            ELSE
                               getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\')
                        END
                  END
           END
    END) AS codigo_conta_contabil,                

  -- Verificar se o lançamento tem vínculo com cheque para obter a contra partida a partir das movimentações do cheque
  (CASE WHEN verificaSeLancamentoTemVinculoComCheque(A.cod_lancamento)
        THEN
           (SELECT codigo_contra_partida FROM obtemChequeParaContabilizacao(A.cod_lancamento, MCH.movimentacaoChequeId))
        -- Caso o lançamento seja oriundo de uma negociação a conta de crédito precisa ser a de abertura do título original
        WHEN ( (SELECT invoiceId FROM obtemTitulosNegociadoDoLancamento(A.cod_lancamento) LIMIT 1) > 0 )
        THEN
            (SELECT accountSchemeId
               FROM finOperation
              WHERE operationId = (SELECT agreementOperation FROM finDefaultOperations LIMIT 1))
        ELSE
                --Caso a operação do lançamento seja de pagamento ou desconto, a contra partida é a conta de débito da mensalidade.
            CASE WHEN B.operationId IN (SELECT UNNEST(ARRAY[paymentoperation, discountoperation])
                                                       FROM finDefaultOperations)
                 THEN 
                     --Caso a operação seja de pagamento e tenha sido feita pelo menos um mês antes do vencimento é um pagamento adiantado
                     --As duas contas de adiantamento também devem estar pré-configuradas
                     CASE WHEN (B.operationId = (SELECT paymentoperation FROM finDefaultOperations LIMIT 1))
                          AND ( (DATE_PART(\'month\', A.data_efetivacao_lancamento::DATE) < DATE_PART(\'month\', D.maturityDate::DATE)) OR 
                                (DATE_PART(\'year\', A.data_efetivacao_lancamento::DATE) < DATE_PART(\'year\', D.maturityDate::DATE)) )
                          AND EXISTS (SELECT 1 FROM basConfig WHERE moduleConfig = \'FINANCE\' AND parameter = \'CONTA_CREDITO_ADIANTAMENTO\' AND trim(value) <> \'\' AND value <> \'0\')
                          AND EXISTS (SELECT 1 FROM basConfig WHERE moduleConfig = \'FINANCE\' AND parameter = \'CONTA_CREDITO_ADIANTAMENTO\' AND trim(value) <> \'\' AND value <> \'0\')
                         THEN               
                             (SELECT getParameter(\'FINANCE\', \'CONTA_CREDITO_ADIANTAMENTO\'))
                         ELSE
                             CASE WHEN D.invoiceId IS NOT NULL
                                THEN
                                    COALESCE(obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId), getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_CREDITO_PADRAO\'))
                                ELSE
                                    getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_CREDITO_PADRAO\')
                             END
                     END
                 --Caso a operação seja de juros e multa ou acréscimo bancário pega a contra partida pela conta da operação.
                 WHEN B.operationId IN (SELECT UNNEST(ARRAY[interestOperation, otheradditionsoperation])  FROM finDefaultOperations)
                 THEN
                     B.accountSchemeId
                 --Quando a operação for de taxa bancária pega sempre o plano de contas do banco
                 WHEN B.operationId = (SELECT banktaxoperation FROM finDefaultOperations LIMIT 1)
                 THEN
                     BAC.accountSchemeId
                 ELSE 
                     --Por padrão obtém a contra partida pela conta do incentivo de financiamento, ou da conta padrão.
                     --Se o o título possuir algum lançamento de mensalidade (e o lançamento atual não for mensalidade), deve pegar como contra-partida a própria mensalidade (no caso de financiamentos, ex.: FIES)
                     CASE WHEN D.invoiceId IS NOT NULL
                       THEN
                        CASE WHEN (obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId) = COALESCE((SELECT accountschemeid 
                                                                                                             FROM finOperation 
                                                                                                            WHERE operationid = (SELECT monthlyfeeoperation 
                                                                                                                                   FROM finDefaultOperations 
                                                                                                                                  LIMIT 1)), getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\'))) AND
                                 (A.cod_tipo_operacao_lancamento::CHAR = \'C\')
                        THEN
                            COALESCE(obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId), getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_DEBITO_PADRAO\'))
                        ELSE
                            COALESCE((CASE WHEN char_length(L.accountschemeid) = 0
                                      THEN
                                          NULL
                                      ELSE
                                          L.accountschemeid
                                      END), getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_CREDITO_PADRAO\'))
                        END
                      ELSE
                        getParameter(\'ACCOUNTANCY\', \'CONTA_CONTABIL_CREDITO_PADRAO\')
                     END
                END
    END) AS codigo_contra_partida,';  
                

v_contas_com_adiantamento :=
               '  --No caso de adiantamentos a conta débito sempre será a conta do parâmetro
               
   getParameter(\'FINANCE\', \'CONTA_DEBITO_ADIANTAMENTO\') AS codigo_conta_contabil,                

  --No caso de adiantamentos a conta crédito sempre buscará da função
                 obterPlanoDeContasDoLancamentoPrincipalDoTitulo(D.invoiceId) AS codigo_contra_partida,';

v_string2 :=   '  D.comments AS observacoes_titulo,
                A.origem,
                COALESCE(OC.counterId::TEXT, BM.bankId::TEXT)::INT AS codigo_local_pagamento,
                COALESCE(CO.description, BA.description) AS local_pagamento,
                A.username AS usuario_do_lancamento,';                
                
v_string3_sem_adiantamento := '
                              (CASE WHEN CM.movementDate IS NOT NULL
                                     THEN
                                        \'SIM\'
                                     ELSE
                                         \'NÃO\'
                                END)::VARCHAR AS lancamento_de_caixa,';

v_string3_com_adiantamento := '
                               \'NÃO\'  lancamento_de_caixa,';

v_string4 :='
                II.ournumber AS nosso_numero,
                (select datageracao::VARCHAR from finHistoricoRemessa where invoiceId = D.invoiceId limit 1) AS data_envio_ao_banco,
                BM.occurrence::VARCHAR AS ocorrencia_bancaria,
                (CASE WHEN TI.invoiceid IS NOT NULL
                      THEN 
                           \'PEDAGÓGICO\'
                      WHEN A.cod_contrato_ref IS NOT NULL
                      THEN
                           \'ACADÊMICO\'
                      ELSE
                           \'NENHUM\'
                 END)::VARCHAR AS modulo_de_vinculo,
                COALESCE(CC.nome, COU.name) AS curso,
                LP.periodId AS periodo_academico,
                TO_CHAR(COALESCE(LP.beginDate, OT.datainicialoferta), getParameter(\'BASIC\', \'MASK_DATE\'))::VARCHAR AS data_inicial,
                TO_CHAR(COALESCE(LP.beginDate, OT.datainicialoferta), \'mm/yyyy\')::VARCHAR AS mes_ano_inicial,
                TO_CHAR(COALESCE(LP.endDate, OT.datafinaloferta), getParameter(\'BASIC\', \'MASK_DATE\'))::VARCHAR AS data_final,
                TO_CHAR(COALESCE(LP.endDate, OT.datafinaloferta), \'mm/yyyy\')::VARCHAR AS mes_ano_final,
                COALESCE(CENA.centerId, CENP.centerId) AS codigo_do_centro,
                COALESCE(CENA.name, CENP.name) AS nome_do_centro,
                A.cod_lancamento AS codigo_do_lancamento,
                B.operationId AS codigo_da_operacao,
                A.cod_arquivo_retorno_bancario AS codigo_do_retorno_bancario,
                COALESCE(CO.unitId, I.unitId)::VARCHAR AS codigo_unidade,
                (SELECT description 
    FROM basUnit
   WHERE unitId = COALESCE(CO.unitId, I.unitId)) AS unidade,
                (\'01/\' || TO_CHAR(D.emissiondate, \'mm/yyyy\'))::VARCHAR AS data_inicial_competencia, --talvez deva ser a data contábil?
                N.nfeId AS codigo_interno_nota_fiscal,
                N.numeronotafiscal AS numero_nota_fiscal,
                (CASE WHEN (SELECT TNFE.invoiceId
                         FROM ONLY finReceivableInvoice TNFE
                        INNER JOIN finEntry ENFE
                                ON (TNFE.invoiceId = ENFE.invoiceId)
                        INNER JOIN finNfe PNFE
                                ON (ENFE.entryId = PNFE.rpsId)
                             WHERE TNFE.invoiceId = A.cod_titulo_ref
                                AND PNFE.estaCancelada IS FALSE
                                AND PNFE.numeroNotaFiscal IS NOT NULL
                                AND A.cod_lancamento = ENFE.entryId) IS NOT NULL
                     THEN
                         true
                     ELSE
                         false
                END) AS titulo_possui_atrelamento_com_nfe,
                obterObservacaoDeOperacaoFormatada(B.observacao, A.cod_lancamento) AS observacao_da_operacao, 
                MCH.movimentacaoChequeId ';

v_from_sem_adiantamento := ' 
    FROM cr_fin_lancamentos_caixa_competencia(' || v_tipo || ', ''' || p_dataini || ''', ''' || p_datafim || ''', NULL, NULL, NULL, NULL) A';

v_from_com_adiantamento := ' 
    FROM (SELECT *
            FROM cr_fin_lancamentos_caixa_competencia(' || v_tipo || ', \'2000-01-01\', (CAST(''' || p_dataini || ''' AS DATE) - INTERVAL \'1 DAY\')::DATE, NULL, NULL, NULL, NULL)
	   WHERE ((EXTRACT(\'MONTH\' FROM data_competencia_lancamento) < EXTRACT(\'MONTH\' FROM data_vencimento_titulo)) OR
                 ((EXTRACT(\'YEAR\' FROM data_competencia_lancamento) < EXTRACT(\'YEAR\' FROM data_vencimento_titulo))))
	     AND (data_vencimento_titulo BETWEEN ''' || p_dataini || '''::DATE AND ''' || p_datafim || '''::DATE)) A ';

v_string5 := '	   
     INNER JOIN finOperation B
	     ON A.cod_operacao_lancamento = B.operationId
     INNER JOIN accCostCenter C
	     ON A.cod_centrodecusto = C.costCenterId
 LEFT JOIN ONLY finReceivableInvoice D
	     ON D.invoiceId = A.cod_titulo_ref
 LEFT JOIN ONLY basPerson E
	     ON E.personId = D.personId
      LEFT JOIN prctituloinscricao F
	     ON A.cod_titulo_ref = F.invoiceid
      LEFT JOIN finCounterMovement CM
	     ON CM.invoiceId = D.invoiceId
	    AND CM.value = A.valor_lancamento
	    AND CM.operation = B.operationTypeId
      LEFT JOIN finOpenCounter OC
	     ON OC.openCounterId = CM.openCounterId
      LEFT JOIN finCounter CO
	     ON CO.counterId = OC.counterId
      LEFT JOIN fin.bankMovement BM	     
	     ON A.conta_bancaria = BM.bankmovementid::varchar
      LEFT JOIN finBank BA
	     ON BA.bankId = BM.bankId
      LEFT JOIN finBankAccount BAC
	     ON BAC.bankAccountId = D.bankAccountId
      LEFT JOIN finBankInvoiceInfo II
	     ON II.invoiceId = D.invoiceId
      LEFT JOIN prcTituloInscricao TI
	     ON TI.invoiceid = D.invoiceid
      LEFT JOIN acpInscricao I
	     ON TI.inscricaoid = I.inscricaoid 
	    AND I.personid = D.personid
      LEFT JOIN acpOfertaCurso OFC
	     ON I.ofertacursoid = OFC.ofertacursoid
      LEFT JOIN acpOcorrenciaCurso OCU
	     ON OFC.ocorrenciacursoid = OCU.ocorrenciacursoid
      LEFT JOIN acpCurso CC
	     ON CC.cursoid = OCU.cursoid
      LEFT JOIN acpInscricaoTurmaGrupo ITG
	     ON ITG.inscricaoId = I.inscricaoId
      LEFT JOIN acpOfertaTurma OT
	     ON OT.ofertaTurmaId = ITG.ofertaTurmaId
      LEFT JOIN acdContract CON
	     ON CON.contractid = A.cod_contrato_ref
      LEFT JOIN acdCourse COU
	     ON COU.courseId = CON.courseId
      LEFT JOIN acdLearningPeriod LP
	     ON LP.learningPeriodId = A.cod_periodo_letivo_ref
      LEFT JOIN acdCenter CENP
	     ON CENP.centerId = CC.centerId
      LEFT JOIN acdCenter CENA
	     ON CENA.centerId = COU.centerId
      LEFT JOIN accCourseAccount CA
	     ON (CA.courseId,
		 CA.courseVersion,
		 CA.unitId) = (CON.courseId,
			       CON.courseVersion,
			       CON.unitId) 
      LEFT JOIN finLoan L --Incentivos de financiamento.
	     ON L.incentiveTypeId = A.cod_incentivo_ref
      LEFT JOIN finNfe N
	     ON N.rpsId = A.cod_lancamento
	    AND N.estaCancelada IS FALSE
      LEFT JOIN finCounterMovementCheque CMC
             ON CM.counterMovementId = CMC.counterMovementId
      LEFT JOIN finMovimentacaoCheque MCH
             ON CMC.chequeId = MCH.chequeId ';

v_string5_sem_adiantamento := '   
	  WHERE (D.isCanceled IS FALSE OR D.invoiceId IS NULL)
            AND A.valor_lancamento > 0
            AND A.cod_lancamento IS NOT NULL ';

--No caso dos pagamentos adiantados ignora tipo, filtra pela de vencimento
v_string5_com_adiantamento := '
            WHERE D.maturityDate::DATE BETWEEN ''' || p_dataini || ''' AND ''' || p_datafim || '''
              AND D.isCanceled IS FALSE
              AND A.valor_lancamento > 0 ';

v_orderBy := ' ORDER BY movimentacaoChequeId ASC ';

--Adiciona o UNION ALL para filtrar os pagamentos adiantados apenas se as contas de adiantamento estiverem configuradas
IF EXISTS (SELECT 1 FROM basConfig WHERE moduleConfig = 'FINANCE' AND parameter = 'CONTA_CREDITO_ADIANTAMENTO' AND trim(value) <> '' AND value <> '0')
   AND EXISTS (SELECT 1 FROM basConfig WHERE moduleConfig = 'FINANCE' AND parameter = 'CONTA_DEBITO_ADIANTAMENTO' AND trim(value) <> '' AND value <> '0') 
THEN
    v_sql := v_string0_sem_adiantamento || v_string1 || v_contas_sem_adiantamento || v_string2 || v_string3_sem_adiantamento || v_string4 || v_from_sem_adiantamento || v_string5 || v_string5_sem_adiantamento ||'  UNION ALL  ' || v_string0_com_adiantamento || v_string1 || v_contas_com_adiantamento || v_string2 || v_string3_com_adiantamento || v_string4 || v_from_com_adiantamento || v_string5 || v_string5_com_adiantamento
             || ' AND (B.operationId = (SELECT paymentoperation FROM finDefaultOperations LIMIT 1))
                                                     AND ( (DATE_PART(\'month\', A.data_efetivacao_lancamento::DATE) < DATE_PART(\'month\', D.maturityDate::DATE)) OR 
                                                            (DATE_PART(\'year\', A.data_efetivacao_lancamento::DATE) < DATE_PART(\'year\', D.maturityDate::DATE)) ) ' || v_orderBy;
ELSE
    v_sql := v_string0_sem_adiantamento || v_string1 || v_contas_sem_adiantamento || v_string2 || v_string3_sem_adiantamento || v_string4 || v_from_sem_adiantamento || v_string5 || v_string5_sem_adiantamento || v_orderBy;
END IF;

RETURN QUERY EXECUTE v_sql;

END;
$BODY$
LANGUAGE plpgsql;