CREATE OR REPLACE VIEW cr_res_residente AS (
	 SELECT R.residenteId AS codigo_residente,
	        R.enfaseId AS codigo_enfase,
	        E.descricao AS enfase,
	        E.abreviatura AS abreviatura_enfase,
	        E.centerId AS codigo_centro_enfase,
	        CE.name AS centro_enfase,
	        R.nucleoprofissionalid AS codigo_nucleo_profissional,
	        NP.descricao AS nucleo_profissional,
	        NP.abreviatura AS abreviatura_nucleo_profissional,
	        TO_CHAR(R.inicio, getParameter('BASIC', 'MASK_DATE')) AS data_inicio_residencia,
	        TO_CHAR(R.fimPrevisto, getParameter('BASIC', 'MASK_DATE')) AS data_fim_previsto_residencia,
	        (CASE WHEN (SELECT EXTRACT('YEAR' FROM NOW()::DATE) - EXTRACT('YEAR' FROM R.inicio::DATE) + 1) > 3 
		     THEN 
		         3
		     ELSE 
		         (SELECT EXTRACT('YEAR' FROM NOW()::DATE) - EXTRACT('YEAR' FROM R.inicio::DATE) + 1)
		 END) AS periodo_atual_residente,
	        R.notaperiodo1semestre1 AS nota_periodo1_semestre1,
	        R.parecerperiodo1semestre1 AS parecer_periodo1_semestre1,
	        R.notaperiodo1semestre2 AS nota_periodo1_semestre2,
	        R.parecerperiodo1semestre2 AS parecer_periodo1_semestre2,
	        R.mediaperiodo1 AS media_periodo1,
	        R.parecermediaperiodo1 AS parecer_media_periodo1,
	        R.notaperiodo2semestre1 AS nota_periodo2_semestre1,
	        R.parecerperiodo2semestre1 AS parecer_periodo2_semestre1,
	        R.notaperiodo2semestre2 AS nota_periodo2_semestre2,
	        R.parecerperiodo2semestre2 AS parecer_periodo2_semestre2,
	        R.mediaperiodo2 AS media_periodo2,
	        R.parecermediaperiodo2 AS parecer_media_periodo2,
	        R.notaperiodo3semestre1 AS nota_periodo3_semestre1,
	        R.parecerperiodo3semestre1 AS parecer_periodo3_semestre1,
	        R.notaperiodo3semestre2 AS nota_periodo3_semestre2,
	        R.parecerperiodo3semestre2 AS parecer_periodo3_semestre2,
	        R.mediaperiodo3 AS media_periodo3,
	        R.parecermediaperiodo3 AS parecer_media_periodo3,
	        R.notafinal AS nota_final,
	        R.parecerfinal AS parecer_final,
	        R.personId AS codigo_pessoa,
	        getPersonName(R.personId) AS nome_pessoa,
	        R.subscriptionid AS codigo_inscricao_processo_seletivo,
	        R.unidade1 AS codigo_unidade1,
	        U1.description AS unidade1,
	        R.unidade2 AS codigo_unidade2,
	        U2.description AS unidade2,
	        R.descricao AS descricao_residencia,
	        R.centerId AS codigo_centro_residencia,
	        R.turmaid AS codigo_turma,
	        T.codigoturma AS codigo_turma_char,
	        T.enfaseId AS codigo_enfase_turma,
	        ET.descricao AS enfase_turma,
	        ET.abreviatura AS abreviatura_enfase_turma,
	        ET.centerId AS codigo_centro_enfase_turma,
	        CET.name AS centro_enfase_turma,
	        T.nucleoprofissionalid AS codigo_nucleo_profissional_turma,
	        NPT.descricao AS nucleo_profissional_turma,
	        NPT.abreviatura AS abreviatura_nucleo_profissional_turma,
	        T.descricao AS turma,
	        TO_CHAR(T.datainicio, getParameter('BASIC', 'MASK_DATE')) AS data_inicio_turma,
	        TO_CHAR(T.datafim, getParameter('BASIC', 'MASK_DATE')) AS data_fim_turma,
	        T.quantidadeperiodo AS quantidade_de_periodos_turma,
	        T.vagas AS vagas,
	        (CASE T.tipoavaliacaotcr
		     WHEN 'N' THEN
		         'Por nota'
		     ELSE
		  	 'Por conceito'
		 END) AS tipo_avaliacao_tcr,
	        R.instituicaoformadora AS codigo_pessoa_juridica_instituicao_formadora,
	        getPersonName(R.instituicaoformadora) AS pessoa_juridica_instituicao_formadora,
                BD.content AS matricula,
                COALESCE(PP.email, PP.emailAlternative, '') AS email,
                SUOC.statusDaOcorrenciaDeContratoId AS codigo_status_ultima_ocorrencia_contrato,
                SUOC.descricao AS status_ultima_ocorrencia_contrato,
                SUOC.bloqueiaResidencia AS status_ultima_ocorrencia_contrato_bloqueia_residencia,
                SUOC.concluiresidencia AS status_ultima_ocorrencia_contrato_conclui_residencia,
                PP.workFunction AS profissao,
                COALESCE(PP.residentialPhone, PP.cellPhone, '') AS telefone,
                RG.content AS rg,
                CPF.content AS cpf,
                INSS.content AS inss,
                res.obterPeriodoDoResidente(R.residenteId) AS periodo_do_residente,
                TCC.trabalhoDeConclusaoId AS codigo_tcc,
                TCC.orientadorId AS codigo_orientador_tcc,
                getPersonName(TCC.orientadorId) AS orientador_tcc,
                TCC.titulo AS titulo_tcc,
                TCC.tema AS tema_tcc,
                COALESCE(TCC.apto, FALSE) AS apto_no_tcc,
                TCC.nota AS nota_tcc,
                (SELECT string_agg(getPersonName(personId), ', ')
                   FROM res.coorientador
                  WHERE trabalhoDeConclusaoId = TCC.trabalhoDeConclusaoId) AS coorientadores_tcc,
                (SELECT string_agg(getPersonName(personId), ', ')
                   FROM res.membroDabanca
                  WHERE trabalhoDeConclusaoId = TCC.trabalhoDeConclusaoId) AS examinadores_tcc,
                (CASE TCC.apto
                      WHEN TRUE 
                      THEN 
                           'APTO'
                      WHEN FALSE 
                      THEN 
                           'INAPTO'
                      ELSE 
                           ''
                 END) AS situacao_tcc,
                (SELECT TO_CHAR(MIN(AA.datahora)::DATE, getParameter('BASIC', 'MASK_DATE'))
                   FROM res.ocorrenciadecontrato AA
                  WHERE AA.residenteid = R.residenteid
                    AND AA.statusdaocorrenciadecontratoid = 1) AS data_inicio_ocorrencias,
                COALESCE((SELECT TO_CHAR(MAX(OC.datahora::DATE), getParameter('BASIC', 'MASK_DATE'))
                            FROM res.ocorrenciadecontrato OC
                      INNER JOIN res.statusdaocorrenciadecontrato SOC
                              ON SOC.statusdaocorrenciadecontratoid = OC.statusdaocorrenciadecontratoid
                           WHERE residenteid = R.residenteId
                             AND SOC.descricao ILIKE 'PREVISÃO DE CONCLUSÃO'),(TO_CHAR(R.fimPrevisto, getParameter('BASIC', 'MASK_DATE')))) AS data_fim_ocorrencias,
                TO_CHAR(COALESCE((SELECT OC.datahora
                                    FROM res.ocorrenciadecontrato OC
                              INNER JOIN res.statusdaocorrenciadecontrato SOC
                                      ON SOC.statusdaocorrenciadecontratoid = OC.statusdaocorrenciadecontratoid
                                   WHERE residenteid = R.residenteId
                                     AND SOC.descricao ILIKE 'PREVISÃO DE CONCLUSÃO'
                                ORDER BY OC.datahora
                              DESC LIMIT 1)::DATE, R.fimPrevisto), getParameter('BASIC', 'MASK_DATE')) as fim_previsto_ocorrencias,
                FI.filepath || FI.fileId AS photopath
	   FROM res.residente R
     INNER JOIN res.nucleoProfissional NP
	     ON NP.nucleoprofissionalid = R.nucleoprofissionalid
     INNER JOIN res.enfase E
	     ON E.enfaseId = R.enfaseId
INNER JOIN ONLY basPhysicalPerson PP
	     ON PP.personId = R.personId
 LEFT JOIN ONLY baslegalperson LP
 	     ON LP.personId = R.instituicaoformadora
      LEFT JOIN res.ocorrenciaDeContrato UOC
             ON UOC.ocorrenciadecontratoid = res.ultimaOcorrenciaDeContratoId(R.residenteId)
      LEFT JOIN res.statusDaOcorrenciaDeContrato SUOC
	     ON SUOC.statusDaOcorrenciaDeContratoId = UOC.statusDaOcorrenciaDecontratoId
      LEFT JOIN acdCenter CR
	     ON CR.centerId = R.centerId
      LEFT JOIN acdCenter CE
	     ON CE.centerId = E.centerId
      LEFT JOIN basUnit U1
	     ON U1.unitId = R.unidade1
      LEFT JOIN basUnit U2
	     ON U2.unitId = R.unidade2
      LEFT JOIN spr.subscription S
	     ON S.subscriptionid = R.subscriptionid
      LEFT JOIN res.turma T
	     ON T.turmaId = R.turmaId
      LEFT JOIN res.enfase ET
	     ON ET.enfaseId = T.enfaseId
      LEFT JOIN acdCenter CET
 	     ON CET.centerId = ET.centerId
      LEFT JOIN res.nucleoProfissional NPT
	     ON NPT.nucleoprofissionalid = T.nucleoprofissionalid
      LEFT JOIN basDocument BD
             ON PP.personId = BD.personId
	    AND BD.documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CARTAO_PONTO')::int
      LEFT JOIN basDocument RG
             ON PP.personId = RG.personId
            AND RG.documentTypeId = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::int
      LEFT JOIN basDocument CPF
             ON PP.personId = CPF.personId
            AND CPF.documentTypeId = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int
      LEFT JOIN basDocument INSS
             ON PP.personId = INSS.personId
            AND INSS.documentTypeId = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_INSS')::int
      LEFT JOIN res.trabalhodeconclusao TCC
             ON TCC.residenteId = R.residenteId
      LEFT JOIN basFile FI
             ON FI.fileId = PP.photoId
);