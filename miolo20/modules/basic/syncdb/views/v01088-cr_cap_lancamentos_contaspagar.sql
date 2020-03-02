CREATE OR REPLACE VIEW cr_cap_lancamentos_contaspagar AS (
	SELECT T.tituloid AS codigo_titulo,
	       T.valor AS valor_titulo,
	       TO_CHAR(T.vencimento, getParameter('BASIC', 'MASK_DATE')) AS data_vencimento_titulo,
	       T.valorAberto AS valor_aberto,
	       T.tituloAberto AS titulo_esta_aberto,
	       T.solicitacaoparcelaid AS codigo_solicitacao_parcela,
	       SP.parcela AS numero_parcela,
	       SP.valor AS valor_parcela,
	       TO_CHAR(SP.dataVencimento, getParameter('BASIC', 'MASK_DATE')) AS data_vencimento_parcela,
	       S.solicitacaoId AS codigo_solicitacao,
	       S.personId AS codigo_solicitante,
	       getPersonName(S.personId) AS solicitante,
	       S.solicitacaoEstadoId AS codigo_estado_solicitacao,
	       SE.nome AS estado_solicitacao,
	       S.dadosCompra AS dados_compra,
	       S.costCenterId AS codigo_centro_de_custos,
	       S.justificativa AS justificativa,
	       S.fornecedorId AS codigo_fornecedor,
	       getPersonName(S.fornecedorId) AS fornecedor,
	       S.formaDePagamentoId AS codigo_forma_pagamento,
	       FDP.descricao AS forma_pagamento,
	       TO_CHAR(S.datasolicitacao, getParameter('BASIC', 'MASK_DATE')) AS data_solicitacao,
	       S.accountSchemeId AS codigo_plano_de_contas,
	       ACS.description AS plano_de_contas,
	       L.lancamentoId AS codigo_lancamento,
	       (L.valor * (CASE WHEN DATAESTANOINTERVALO(L.dataLancamento::date, CR.datainicial, COALESCE(CR.datafinal, 'infinity'))
	                        THEN COALESCE(CRCC.parcentualrateio / 100, 1)
	                        ELSE 1
	                   END))::numeric(12, 2) AS valor_lancamento,
	       L.tipoLancamento AS codigo_tipo_lancamento,
	       TL.nome AS tipo_lancamento,
	       TO_CHAR(L.dataLancamento, getParameter('BASIC', 'MASK_DATE')) AS data_lancamento,
	       F.origem AS origem_lancamento,
	       L.accountSchemeId AS codigo_plano_de_contas_lancamento,
	       ACSL.description AS plano_de_contas_lancamento,
	       L.costCenterId AS codigo_centro_de_custos_lancamento,
               COALESCE((SELECT cnpj
	              FROM ONLY basLegalPerson
	                  WHERE personId = S.fornecedorId),
	                (SELECT content
	                   FROM basDocument
	                  WHERE personId = S.fornecedorId
	                    AND documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT), '') AS documento_fornecedor,
               S.numerodanotafiscal AS numero_nota_fiscal
	  FROM capTitulo T 
    INNER JOIN capSolicitacaoParcela SP
	    ON SP.solicitacaoParcelaId = T.solicitacaoParcelaId 
    INNER JOIN capSolicitacao S
	    ON S.solicitacaoId = SP.solicitacaoId
    INNER JOIN capFormaDePagamento FDP
	    ON FDP.formaDePagamentoId = S.formaDePagamentoId
    INNER JOIN capLancamento L
	    ON L.tituloId = T.tituloId
    INNER JOIN (SELECT A1.tituloId,
                       (CASE WHEN (SELECT COUNT(C1.tituloId) > 0
                                     FROM fin.bankMovement C1
                                    WHERE A1.tituloId = C1.tituloId)
                             THEN
				  'BANCO'
			     ELSE
				  'CAIXA'
                        END) AS origem
                  FROM capLancamento A1
            INNER JOIN capTitulo B1
                    ON A1.tituloId = B1.tituloId 
                   AND A1.tipoLancamento = 'C' 
                   AND B1.tituloAberto = FALSE) F
            ON T.tituloId = F.tituloId
     LEFT JOIN capSolicitacaoEstado SE
	    ON SE.solicitacaoEstadoId = S.solicitacaoEstadoId
     LEFT JOIN accAccountScheme ACS
	    ON ACS.accountSchemeId = S.accountSchemeId
     LEFT JOIN capTipoLancamento TL
	    ON TL.tipoLancamentoId = L.tipoLancamento
     LEFT JOIN accAccountScheme ACSL
	    ON ACSL.accountSchemeId = L.accountSchemeId
     LEFT JOIN caprateio CR
            ON CR.accountSchemeId = S.accountSchemeId
     LEFT JOIN caprateiocentrodecusto CRCC
            ON CRCC.rateioid = CR.rateioid AND CRCC.costCenterId = L.costCenterId
);
