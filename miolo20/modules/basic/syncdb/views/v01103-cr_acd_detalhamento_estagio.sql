CREATE OR REPLACE VIEW cr_acd_detalhamento_estagio AS (
    SELECT A.contractId AS cod_contrato,
           A.personId AS cod_pessoa,
           A.name AS pessoa,
           A.email AS email_pessoa,
           A.cpf,
           A.rg,
           A.datanascimento AS data_nascimneto,
           A.miolousername AS login_pessoa,
           A.datahoje AS data_hoje,
           A.dataPorExtenso AS data_hoje_extenso,
           A.coordinatorpersonid AS cod_coordenador,
           A.coordinatorname AS coordenador,
           getCourseName(A.courseId) AS curso_nome,
           A.courseId AS cod_curso,
           A.courseVersion AS versao_curso,
           A.turnId AS turno,
           getTurnDescription(A.turnId) AS descricao_turno,
           A.unitId AS unidade,
           getUnitDescription(A.unitId) AS descricao_unidade,
           C.curriculumId,
           B.groupId,
           B.enrollId,
           D.name AS nome_disciplina,
           D.curricularcomponentid AS cod_disciplina,
           D.curricularComponentVersion AS versao_disciplina,
           D.academicnumberhours AS carga_horaria,
           D.lessoncredits AS creditos,
           TE.trainingEmphasisId AS codigo_enfase_estagio,
           TE.description AS enfase_estagio,
           TD.responsibleId AS codigo_responsavel_estagio,
           getPersonName(TD.responsibleId) AS responsavel_estagio,
           TD.realizedactivities AS atividades_realizadas_estagio,
           TD.place AS local_estagio,
           dateToUser(TD.startDate) AS data_inicio_estagio,
           dateToUser(TD.endDate) AS data_fim_estagio,
           TD.duration AS carga_horaria_estagio,
           LP.periodId AS codigo_periodo
      FROM rptContrato A
INNER JOIN acdEnroll B
        ON A.contractId = B.contractId
       AND B.statusId IN (GETPARAMETER('ACADEMIC','ENROLL_STATUS_APPROVED')::INT, GETPARAMETER('ACADEMIC','ENROLL_STATUS_EXCUSED')::INT) -- status aprovado ou dispensado
INNER JOIN acdCurriculum C
        ON B.curriculumId = C.curriculumId
       AND C.curricularComponentTypeId = GETPARAMETER('ACADEMIC', 'CURRICULAR_COMPONENT_TYPE_STAGE')::INT -- disciplinas do tipo estágio
INNER JOIN acdCurricularComponent D
        ON (C.curricularComponentId, C.curricularComponentVersion) = (D.curricularComponentId, D.curricularComponentVersion)
INNER JOIN acdGroup G
        ON G.groupId = B.groupId
INNER JOIN acdLearningPeriod LP
        ON LP.learningPeriodId = G.learningPeriodId
 LEFT JOIN acdTrainingDetail TD
        ON TD.enrollId = B.enrollId
 LEFT JOIN acdTrainingEmphasis TE
        ON TE.trainingEmphasisId = TD.trainingEmphasisId
);