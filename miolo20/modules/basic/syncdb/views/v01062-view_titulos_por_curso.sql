CREATE OR REPLACE VIEW view_titulos_por_curso AS (
    SELECT A.invoiceid AS titulo,
           A.personid AS codigopessoa,
           A.parcelnumber as parcela,
           TO_CHAR(A.maturitydate, getParameter('BASIC', 'MASK_DATE')) as vencimento,
           A.value::NUMERIC as valor,
           (CASE WHEN balance(A.invoiceid) = 0 AND EXISTS (SELECT X.entryid 
                                                             FROM finentry X 
                                                            WHERE X.invoiceid = A.invoiceid 
                                                              AND X.operationid = (SELECT paymentoperation 
                                                                                     FROM findefaultoperations 
                                                                                    LIMIT 1))
                 THEN
                     ROUND((SELECT sum(X.value) 
                              FROM finentry X 
                             WHERE X.invoiceid = A.invoiceid 
                               AND X.operationid = (SELECT paymentoperation 
                                                      FROM findefaultoperations 
                                                     LIMIT 1)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)
                 ELSE
                     ROUND(0, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)
            END) as recebido,
           (CASE WHEN balance(A.invoiceid) > 0
                 THEN
                     balanceWithPoliciesDated(A.invoiceId, now()::date)
                 ELSE
                     ROUND(0, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)
            END) as areceber,
           COALESCE(H.name, BP.name) as pessoanome,
           COALESCE (G.courseid::TEXT, E.cursoid::TEXT) as courseid,
           G.courseversion as courseversion,
           COALESCE (G.turnid, E.turnId) as turnid,
           COALESCE (G.unitid, E.unitid) as unitid,
           (CASE WHEN G.contractid::text IS NOT NULL 
                 THEN 
                     G.courseid::text || '/' || G.courseversion::text || '/' || G.turnid::text || '/' || G.unitid::text
                 ELSE 
                     (CASE WHEN C.inscricaoid::text IS NOT NULL 
                           THEN 
                               E.ocorrenciacursoid::text 
                      END) 
            END) AS ocorrenciacurso,
           (CASE WHEN G.contractid::text IS NOT NULL 
                 THEN 
                     'A'
                 ELSE 
                     (CASE WHEN C.inscricaoid::text IS NOT NULL 
                           THEN 
                               'P' 
                      END) 
            END) AS modulo,
           COALESCE (COU.name::TEXT, CC.nome::TEXT, '') as cursonome,
           ROUND(A.nominalValue, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS valor_nominal,
           TO_CHAR(A.emissiondate, getParameter('BASIC', 'MASK_DATE')) AS data_emissao,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'REDUCTION_BALANCE_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_abatimento_de_saldo,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'DISCOUNT_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_desconto,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'INCENTIVE_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_incentivo,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'INTEREST_FINE_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_juros_multa,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'MONTHLY_FEE_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_mensalidade,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'NEGOTIATION_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_negociacao,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'PAYMENT_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_pagamento,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'TAX_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_taxa,
           obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'TRANSFERENCE_OPERATION_GROUP_ID'), FALSE) AS saldo_lancamentos_grupo_transferencia,
           (SELECT SUM(COALESCE(X.credito, 0) - COALESCE(X.debito, 0))
              FROM (SELECT (CASE WHEN O.operationTypeId = 'D' THEN SUM(EN.value) END) AS debito,
                           (CASE WHEN O.operationTypeId = 'C' THEN SUM(EN.value) END) AS credito
                      FROM finEntry EN
                INNER JOIN finOperation O
                        ON O.operationid = EN.operationid
                     WHERE EN.invoiceId = A.invoiceId
                       AND O.operationGroupId NOT IN (getparameter('FINANCE', 'REDUCTION_BALANCE_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'DISCOUNT_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'INCENTIVE_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'INTEREST_FINE_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'MONTHLY_FEE_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'NEGOTIATION_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'PAYMENT_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'TAX_OPERATION_GROUP_ID'),
                                                      getparameter('FINANCE', 'TRANSFERENCE_OPERATION_GROUP_ID'))
                  GROUP BY O.operationTypeId) X) AS saldo_outros_lancamentos,
           balance(A.invoiceId) AS saldo_a_receber_nao_corrigido,
           (G.contractid IS NOT NULL OR C.inscricaoid IS NOT NULL) AS atrelado_a_curso,
           getTurnDescription(COALESCE(G.turnid, E.turnId)) AS turno_descricao,
           getUnitDescription(COALESCE(G.unitid, E.unitid)) AS unidade_descricao,
           (SELECT COUNT(EN.entryId) > 0
              FROM finEntry EN
        INNER JOIN fin.negotiationGeneratedEntries NGE
                ON NGE.entryId = EN.entryId
             WHERE EN.invoiceId = A.invoiceId) AS titulo_de_negociacao,
            BA.bankaccountid as codigo_conta_bancaria_sagu,
            BA.description as descricao_conta_bancaria,
            BA.accountnumber as numero_conta_bancaria,
            BA.accountnumberdigit as digito_verificador_conta,
            BA.branchnumber as _numero_agencia,
            BA.branchnumberdigit as digito_verificador_agencia,
            BK.bankid as codigo_banco,
            BK.bankidvd as digito_verificador_banco,
            BK.description descricao_banco,
            (SELECT NGE.negotiationid
              FROM finEntry EN
        INNER JOIN fin.negotiationGeneratedEntries NGE
                ON NGE.entryId = EN.entryId
             WHERE EN.invoiceId = A.invoiceId
               AND NGE.generated IS FALSE
          ORDER BY NGE.dateTime DESC
             LIMIT 1) AS codigo_negociacao_fechamento,
            (SELECT NGE.negotiationid
              FROM finEntry EN
        INNER JOIN fin.negotiationGeneratedEntries NGE
                ON NGE.entryId = EN.entryId
             WHERE EN.invoiceId = A.invoiceId
               AND NGE.generated IS TRUE
          ORDER BY NGE.dateTime DESC
             LIMIT 1) AS codigo_negociacao_geracao,
           getPersonDocument(A.personid, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT) AS cpf_pessoa,
           getPersonDocument(A.personid, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::INT) AS rg_pessoa,
           H.cityId AS codigo_cidade,
           CI.stateId AS uf_estado,
           CI.name AS cidade,
           H.zipCode AS cep,
           COALESCE(H.neighborhood, '') AS bairro,
           H.location AS endereco,
           H.number AS numero_endereco,
           COALESCE(H.complement, '') AS complemento_endereco,
           (SELECT learningPeriodId
	      FROM finEntry
             WHERE invoiceId = A.invoiceId
               AND learningPeriodId IS NOT NULL
             LIMIT 1) AS codigo_periodo_letivo_titulo,
           COC.name AS nome_credora,
           LP.cnpj AS cnpj_credora,
           LP.cityId AS codigo_cidade_credora,
           CIC.stateId AS uf_estado_credora,
           CIC.name AS cidade_credora,
           LP.zipCode AS cep_credora,
           COALESCE(LP.neighborhood, '') AS bairro_credora,
           LP.location AS endereco_credora,
           LP.number AS numero_endereco_credora,
           COALESCE(LP.complement, '') AS complemento_endereco_credora,
           P.policyId AS codigo_politica,
           P.description AS descricao_politica,
           P.operationId AS codigo_operacao_politica,
           P.collectiontypeid AS codigo_tipo_cobranca_politica,
           (SELECT description
              FROM finCollectionType
             WHERE collectiontypeid = P.collectiontypeid) AS tipo_cobranca_politica,
           P.applyinterest AS aplicar_juros_politica,
           P.monthlyinterestpercent AS percentual_juros_ao_mes_politica,
           P.daysToInterest AS dias_para_aplicar_juros_politica,
           P.interestType AS codigo_tipo_juros,
           (CASE P.interestType WHEN 'S' THEN 'Simples' ELSE 'Composto' END) AS tipo_juros,
           P.applyFine AS aplicar_multas_politica,
           P.finePercent AS percentual_multa_politica,
           P.daysToFine AS dias_para_aplicar_multas_politica,
           P.isextinct AS politica_esta_extinta,
           (SELECT operationId 
              FROM finEntry 
             WHERE invoiceId = A.invoiceId 
          ORDER BY entryId 
             LIMIT 1) AS codigo_operacao_abertura,
           (SELECT description 
              FROM finEntry
        INNER JOIN finOperation
                ON (finEntry.operationId = finOperation.operationId)
             WHERE invoiceId = A.invoiceId 
          ORDER BY entryId 
             LIMIT 1) AS descricao_operacao_abertura, 
            (SELECT finOperation.accountSchemeId 
               FROM finEntry
         INNER JOIN finOperation 
                 ON (finEntry.operationId = finOperation.operationId)
              WHERE invoiceId = A.invoiceId 
           ORDER BY entryId 
              LIMIT 1) AS conta_operacao_abertura,
           obterSaldoDeChequesPorStatus(A.invoiceId, getParameter('FINANCE', 'STATUS_CHEQUE_ID_EM_ABERTO')::INT) AS saldo_cheques_em_aberto,
           obterSaldoDeChequesPorStatus(A.invoiceId, getParameter('FINANCE', 'STATUS_CHEQUE_ID_DEVOLVIDO')::INT) AS saldo_cheques_devolvidos,
           (obterSaldoDeChequesPorStatus(A.invoiceId, getParameter('FINANCE', 'STATUS_CHEQUE_ID_EM_ABERTO')::INT) +
            obterSaldoDeChequesPorStatus(A.invoiceId, getParameter('FINANCE', 'STATUS_CHEQUE_ID_DEVOLVIDO')::INT)) AS saldo_total_cheques,
           getPersonName(A.personId) AS nome_pessoa,
           A.maturitydate AS vencimento_nao_formatado,
           A.referencematuritydate AS vencimento_de_referencia,
           TO_CHAR(A.referencematuritydate, getParameter('BASIC', 'MASK_DATE')) AS vencimento_de_referencia_formatado,
           (SELECT COUNT(E.entryId) > 0
              FROM finEntry E
        INNER JOIN finOperation O
                ON O.operationId = E.operationId
             WHERE E.invoiceId = A.invoiceId
               AND O.operationGroupId = getParameter('FINANCE', 'MONTHLY_FEE_OPERATION_GROUP_ID')) AS titulo_possui_lancamento_de_mensalidade,
           isFreshManByPeriod(G.contractId, getParameter('BASIC', 'CURRENT_PERIOD_ID')) AS e_calouro_no_periodo_atual,
           (B.invoiceId IS NOT NULL) titulo_do_pedagogico,
           ROUND((COALESCE((SELECT valor FROM obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'DISCOUNT_OPERATION_GROUP_ID'), FALSE)), 0.00) +
                  COALESCE((SELECT valor FROM obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(A.invoiceId, getparameter('FINANCE', 'INCENTIVE_OPERATION_GROUP_ID'), FALSE)), 0.00)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS total_de_descontos,
           INS.description AS origem_titulo,
           ROUND(getInvoiceInterestValue(A.invoiceId, NOW()::DATE), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS valor_de_juros_atualizado,
           ROUND(getInvoiceFineValue(A.invoiceId, NOW()::DATE), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS valor_de_multa_atualizada,
           COALESCE(getContractClassId(G.contractId), (SELECT OT.codigo
                                                         FROM acpInscricaoTurmaGrupo ITG
                                                   INNER JOIN acpOfertaTurma OT
                                                           ON OT.ofertaTurmaId = ITG.ofertaTurmaId
                                                        WHERE ITG.inscricaoId = C.inscricaoId
                                                        LIMIT 1)) AS codigo_turma,
           COALESCE((SELECT name
                       FROM acdClass
                      WHERE classId = getContractClassId(G.contractId)), (SELECT OT.descricao
                                                                            FROM acpInscricaoTurmaGrupo ITG
                                                                      INNER JOIN acpOfertaTurma OT
                                                                              ON OT.ofertaTurmaId = ITG.ofertaTurmaId
                                                                           WHERE ITG.inscricaoId = C.inscricaoId
                                                                           LIMIT 1)) AS nome_turma
 FROM ONLY finReceivableInvoice A
 LEFT JOIN prcTituloInscricao B
        ON (A.invoiceid = B.invoiceid)
 LEFT JOIN acpInscricao C
        ON (B.inscricaoid = C.inscricaoid and A.personid = C.personid)
 LEFT JOIN acpOfertaCurso D
        ON (C.ofertacursoid = D.ofertacursoid)
 LEFT JOIN acpOcorrenciaCurso E
        ON (D.ocorrenciacursoid = E.ocorrenciacursoid)
 LEFT JOIN acpCurso CC
        ON (CC.cursoid = E.cursoid)
 LEFT JOIN acdContract G
        ON (G.contractid = (SELECT contractId
                              FROM finEntry
                             WHERE invoiceId = A.invoiceId
                             LIMIT 1))
 LEFT JOIN acdCourse COU
        ON COU.courseId = G.courseId
 LEFT JOIN ONLY basPhysicalPerson H
        ON (A.personid = H.personid)
 LEFT JOIN finbankaccount BA
        ON BA.bankaccountid = A.bankaccountid
 LEFT JOIN finbank BK
        ON BA.bankid = BK.bankid
 LEFT JOIN basCity CI
        ON CI.cityId = H.cityId
 LEFT JOIN basCompanyConf COC
        ON COC.companyId = getParameter('BASIC', 'DEFAULT_COMPANY_CONF')::int
 LEFT JOIN ONLY basLegalPerson LP
        ON LP.personId = COC.personId
 LEFT JOIN basCity CIC
        ON CIC.cityId = LP.cityId
 LEFT JOIN ONLY basPerson BP 
        ON (A.personId = BP.personId)
 LEFT JOIN finPolicy P
        ON P.policyId = A.policyId
 LEFT JOIN finIncomeSource INS
        ON INS.incomeSourceId = A.incomeSourceId
     WHERE A.iscanceled IS FALSE
);
