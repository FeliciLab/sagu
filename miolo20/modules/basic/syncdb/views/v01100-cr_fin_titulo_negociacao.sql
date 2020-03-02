CREATE OR REPLACE VIEW cr_fin_titulo_negociacao AS (
     SELECT T.*,
	    COALESCE(codigo_negociacao_fechamento, codigo_negociacao_geracao) AS codigo_negociacao,
	    (CASE WHEN codigo_negociacao_fechamento IS NOT NULL
		  THEN
		       TRUE
		  ELSE
		       FALSE
	     END) AS esta_fechado_por_negociacao,
	    P.policyId AS codigo_politica_negociacao,
	    P.description AS politica_negociacao,
	    N.adminuser AS usuario_admin_negociacao,
	    N.discount AS valor_desconto_negociacao,
	    N.discountbyvalue AS valor_desconto_negociacao_nao_percentual,
	    N.observation AS observacao_negociacao,
            N.recordType AS codigo_tipo_negociacao,
	    (CASE N.recordType
		  WHEN 'N'
		  THEN
		       'Negociação'
		  WHEN 'A'
		  THEN
		       'Antecipação'
	     END) AS tipo_negociacao,
	    N.entryValue AS valor_de_entrada_negociacao,
	    N.feeRelease AS liberar_taxas_negociacao,
	    N.interestrelease AS liberar_juros_negociacao,
	    N.finerelease AS liberar_multas_negociacao,
            LP.periodId AS codigo_periodo,
            COALESCE(LP.description, '') periodo_letivo,
            TO_CHAR(N.dateTime::DATE, getParameter('BASIC', 'MASK_DATE')) AS data_negociacao,
            N.diaVencimento AS dia_vencimento_negociacao,
            N.jurosAdicionalIsPercent AS juros_adicional_negociacao_e_percentual,
            N.jurosAdicional AS juros_adicional_negociacao,
            N.numeroParcelas AS numero_de_parcelas_negociacao,
            N.feeValueIsPercent AS valor_taxa_negociacao_e_percentual,
            N.feeValue AS  valor_taxa_negociacao,
            N.entryValueIsPercent AS valor_entrada_negociacao_e_percentual,
            BA.bankAccountId AS codigo_conta_bancaria_negociacao,
            BA.description AS conta_bancaria_negociacao,
            O.operationId AS codigo_operacao_negociacao,
            O.description AS operacao_negociacao,
            CC.costCenterId AS codigo_centro_de_custo_negociacao,
            CC.description AS centro_de_custo_negociacao,
            P.monthlyInterestPercent AS percentual_mensal_de_juros_politica_negociacao,
            P.finePercent AS percentual_multa_politica_negociacao,
            (CASE T.parcela
                  WHEN 0 
                  THEN 
                       'Entrada'
                  ELSE 
                       'Parcela ' || T.parcela::VARCHAR
             END) AS parcela_descricao
       FROM view_titulos_por_curso T
 INNER JOIN fin.negotiation N
	 ON N.negotiationId = COALESCE(codigo_negociacao_fechamento, codigo_negociacao_geracao)
 INNER JOIN finPolicy P
	 ON P.policyId = N.policyId
  LEFT JOIN acdLearningPeriod LP
         ON LP.learningPeriodId = T.codigo_periodo_letivo_titulo
  LEFT JOIN finBankAccount BA
         ON BA.bankAccountId = N.bankAccountId
  LEFT JOIN finOperation O
         ON O.operationId = N.operationId
  LEFT JOIN accCostCenter CC
         ON CC.costCenterId = N.costCenterId
      WHERE T.titulo_de_negociacao
   ORDER BY codigo_negociacao, titulo
);
