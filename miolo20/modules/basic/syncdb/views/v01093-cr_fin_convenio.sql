CREATE OR REPLACE VIEW cr_fin_convenio AS
(
    SELECT A.convenantPersonId AS codigo_do_convenio_da_pessoa,
           A.personId AS codigo_pessoa,
           getPersonName(A.personId) AS nome_pessoa,
           getPersonDocument(A.personId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT) AS cpf_pessoa,
           A.convenantId AS codigo_convenio,
           TO_CHAR(A.beginDate, getParameter('BASIC', 'MASK_DATE')) AS data_inicial, 
           TO_CHAR(A.endDate, getParameter('BASIC', 'MASK_DATE')) AS data_final,
           A.contractId AS codigo_contrato,
           A.observacao AS observacao,
           A.inscricaoId AS inscricao_pedagogica,
           A.tipoConcessao AS tipo_de_concessao,
           B.description AS descricao_convenio,
           B.value AS valor_convenio,
           B.isPercent AS valor_percentual,
           B.daysToDiscount AS dias_para_aplicar_convenio,
           B.beforeAfter AS antes_ou_depois,
           B.convenantOperation AS operacao_do_convenio,
           B.percentRenovacao AS porcentagem_de_renovacao,
           B.aditarIncentivo AS aditar_incentivo,
           B.acumulativo,
           B.condicional,
           B.todasDisciplinas AS todas_as_discplinas,
           B.aplicaVeteranos AS aplicar_veteranos,
           B.aplicaCalouros AS aplicar_calouros,
           B.concederPeriodo as conceder_automaticamente,
           B.crMaximo AS limite_maximo_de_creditos,
           B.crMinimo AS limite_minimo_de_creditos,
           (CASE WHEN (A.beginDate <= now()::DATE AND A.endDate >= now()::DATE)
                 THEN
                     TRUE
                 ELSE
                     FALSE
            END) AS vigente,
           C.courseId AS codigo_curso,
           getCourseName(C.courseId) AS nome_curso,
           C.courseVersion AS versao_curso,
           COALESCE(C.unitId, D.unitId)  AS codigo_unidade,
           (CASE WHEN C.unitId IS NOT NULL THEN getUnitDescription(C.unitId)
                 WHEN D.unitId IS NOT NULL THEN getUnitDescription(D.unitId)
            END) AS descricao_unidade,
           COALESCE(C.turnId, F.turnId) AS codigo_turno,
           (CASE WHEN C.turnId IS NOT NULL THEN getTurnDescription(C.turnId)
                 WHEN F.turnId IS NOT NULL THEN getTurnDescription(F.turnId)
            END) AS descricao_turno,
           G.learningPeriodId AS periodo_letivo,
           G.periodId AS periodo_academico
      FROM finConvenantPerson A
INNER JOIN finConvenant B
        ON (A.convenantId = B.convenantId)
 LEFT JOIN acdContract C
        ON (A.contractId = C.contractId)
 LEFT JOIN acpInscricao D
        ON (A.inscricaoId = D.inscricaoId)
 LEFT JOIN acpOfertaCurso E
        ON (D.ofertaCursoId = E.ofertaCursoId)
 LEFT JOIN acpOcorrenciaCurso F
        ON (E.ocorrenciaCursoId = F.ocorrenciaCursoId)
 LEFT JOIN acdLearningPeriod G
        ON (C.courseId,
            C.turnId,
            C.unitId,
            C.courseVersion) = (G.courseId,
                                G.turnId,
                                G.unitId,   
                                G.courseVersion)
       AND  (G.beginDate, G.endDate) OVERLAPS (A.beginDate, A.endDate)
);