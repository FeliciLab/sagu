CREATE OR REPLACE VIEW cr_fin_incentivo AS (
	 SELECT I.incentiveId AS codigo_incentivo,
	        I.contractId AS codigo_contrato,
	        LP.learningPeriodId AS codigo_periodo_letivo,
	        C.personId AS codigo_pessoa,
	        PP.name AS pessoa,
	        CO.name AS curso,
	        D.content AS cpf,
	        TO_CHAR(I.startDate, getParameter('BASIC', 'MASK_DATE')) AS data_inicio_incentivo,
	        TO_CHAR(I.endDate, getParameter('BASIC', 'MASK_DATE')) AS data_fim_incentivo,
	        (now()::DATE BETWEEN I.startDate AND I.endDate) AS incentivo_vigente,
	        I.valueIsPercent AS valor_e_percentual,
		ROUND(I.value, getParameter('BASIC', 'REAL_ROUND_VALUE')::int) AS valor_configurado_beneficio,
	        I.supporterId AS codigo_patrocinador,
	        getPersonName(I.supporterId) AS patrocinador,
	        I.agglutinate AS aglutinar_valor,
	        I.costCenterId AS codigo_centro_de_custos,
	        I.cancellationDate AS data_cancelamento,
	        I.incentivoAditado AS incentivo_aditado,
	        I.pagamentovalorfinanciado AS data_pagamento_valor_financiado,
	        (CASE I.concedersobre
		     WHEN 'N' THEN
		         'Valor nominal'
		     WHEN 'S' THEN
			 'Saldo em aberto'
		 END) AS conceder_sobre,
	        I.prioridade AS prioridade,
	        (CASE I.opcaoDeAjuste
	            WHEN 'D' THEN
	                'Diluir valor em parcelas restantes'
	            WHEN 'A' THEN
	                'Abater parcelas sequentes com valor pago'
	         END) AS opcao_de_ajuste,
	        I.incentiveTypeId AS codigo_tipo_incentivo,
	        IT.description AS tipo_incentivo,
	        (SELECT description
	           FROM finOperation
	          WHERE operationId = IT.operationId) AS codigo_operacao_desconto_beneficiado,
	        O.description AS operacao_desconto_beneficiado,
	        IT.needadjustauthorization AS precisa_autorizacao_para_ajustes,
	        IT.sendInvoices AS enviar_titulos,
	        IT.isextinct AS esta_estinto,
	        IT.generatecredits AS gerar_creditos,
	        IT.paymentoperation AS codigo_operacao_estorno_beneficiado,
	        (SELECT description
	           FROM finOperation
	          WHERE operationId = IT.paymentoperation) AS operacao_estorno_beneficiado,
	        IT.repaymentoperation AS codigo_operacao_reembolso_beneficiador,
	        (SELECT description
	           FROM finOperation
	          WHERE operationId = IT.paymentoperation) AS operacao_reembolso_beneficiador,
	        IT.applydiscounts AS aplicar_descontos,
	        IT.aditarincentivo AS aditar_incentivo,
	        IT.percentrenovacao AS percentual_aproveitamento_para_renovacao,
	        LP.periodId AS codigo_periodo,
	        get_semester_contract(I.contractId) AS semestres_cursados,
	        isFreshmanByPeriod(I.contractId, LP.periodId) AS e_calouro_no_periodo,
	        COALESCE(obterValorAPagar(I.contractId, LP.learningPeriodId), 0.00) AS valor_total_semestre,
		ROUND((CASE I.valueIsPercent 
                            WHEN TRUE THEN
                                COALESCE(obterValorAPagar(I.contractId, LP.learningPeriodId), 0) * (I.value / 100)
                            ELSE
                                I.value
                       END), getParameter('BASIC', 'REAL_ROUND_VALUE')::int) AS valor_total_beneficio,
                C.courseId AS codigo_curso,
                C.courseVersion AS versao_curso,
                C.turnId AS codigo_turno,
                getTurnDescription(C.turnId) AS turno,
                C.unitId AS codigo_unidade,
                getUnitDescription(C.unitId) AS unidade,
                getContractStateByPeriod(C.contractId, LP.periodId) AS estado_contratual_no_periodo,
                (SELECT description
                   FROM acdStateContract
                  WHERE stateContractId = getContractStateByPeriod(C.contractId, LP.periodId)) AS descricao_estado_contratual_no_periodo
	   FROM finIncentive I
INNER JOIN ONLY finIncentiveType IT
	     ON IT.incentiveTypeId = I.incentiveTypeId
     INNER JOIN finOperation O
	     ON O.operationId = IT.operationId
     INNER JOIN acdContract C
	     ON C.contractId = I.contractId
INNER JOIN ONLY basPhysicalPerson PP
	     ON PP.personId = C.personId
     INNER JOIN acdCourse CO
	     ON CO.courseId = C.courseId
      LEFT JOIN acdLearningPeriod LP
	     ON (LP.courseId,
		 LP.courseVersion,
		 LP.turnId,
		 LP.unitId) = (C.courseId,
			       C.courseVersion,
			       C.turnId,
			       C.unitId)
	    AND (LP.beginDate, LP.endDate) OVERLAPS (I.startDate, I.endDate)
      LEFT JOIN basDocument D
	     ON D.personId = C.personId
	    AND D.documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT 
);
