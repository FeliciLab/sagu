CREATE OR REPLACE FUNCTION obterSituacaoAcademicaDosContratosNoPeriodo(
    p_periodId VARCHAR, 
    p_exibirSituacaoDoPeriodoAnterior BOOLEAN, 
    p_courseId VARCHAR DEFAULT NULL, 
    p_courseVersion INTEGER DEFAULT NULL,
    p_turnId INTEGER DEFAULT NULL,
    p_unitId INTEGER DEFAULT NULL,
    p_contractId INTEGER DEFAULT NULL
)
RETURNS SETOF SituacaoDoContratoNoPeriodo AS
$BODY$
/******************************************************************************
  NAME: obterSituacaoAcademicaDosContratosNoPeriodo
  DESCRIPTION: Obter a situcação acadêmica no período de todos os contratos 
           cujos cursos possuam período letivo no período filtrado, 
           e no período anterior ao filtrado, e que os contratos possuam 
           alguma movimentação contratual cuja data da movimentação esteja
           entre a data do inicio do periodo anterior ao término do filtrado.
  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       03/02/15   Augusto A. Silva      1. Função criada.
  2.0       18/03/15   ftomasini             1. Otimização
                                             2. Indentacao
******************************************************************************/
BEGIN
    IF (SELECT prevPeriodId
      FROM acdPeriod
     WHERE periodId = p_periodId) IS NULL
    THEN
        RAISE EXCEPTION 'Para esta consulta, é necessário o preenchimento do período anterior para o período filtrado %', p_periodId;
    END IF;

    RETURN QUERY (
        SELECT DISTINCT C.personId::BIGINT AS codigo_aluno,
               PP.name AS nome_aluno,
                -- cpf
               (SELECT content
                  FROM basDocument
                 WHERE personId = C.personId
                   AND documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT LIMIT 1) AS cpf,
               -- rg
               (SELECT content
                  FROM basDocument
                 WHERE personId = C.personId
                   AND documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::INT LIMIT 1) AS rg,
               C.contractId AS codigo_contrato,
               C.courseId AS codigo_curso,
               CO.name AS nome_curso,
               C.unitId AS codigo_unidade,
               UT.description::varchar AS descricao_unidade,
               C.turnId AS codigo_turno,
               TU.description::varchar AS descricao_turno,
               (SELECT classId FROM acd_obterTurmaDoAlunoNoPeriodo(C.contractId, p_periodId)) AS codigo_turma,
               (SELECT name FROM acd_obterTurmaDoAlunoNoPeriodo(C.contractId, p_periodId)) AS descricao_turma,
               C.prevPeriodId AS periodo_anterior,
               --situacao anterior do contrato
               (CASE p_exibirSituacaoDoPeriodoAnterior WHEN TRUE 
                THEN
                    obterDescricaoDaSituacaoAcademica(obtem_situacao_contrato(C.contractId, c.prevPeriodId))
                ELSE
                    'NÃO EXIBIDO'
                END) AS situacao_no_periodo_anterior,
               p_periodId AS periodo,
               -- situacao atual do contrato
               obterDescricaoDaSituacaoAcademica(obtem_situacao_contrato(C.contractId, p_periodId)) AS situacao_no_periodo,
               -- verifica se é calouro no período
               isfreshmanbyperiod(C.contractId, p_periodId) AS aluno_calouro,
               C.courseVersion AS versao_curso,
               (SELECT SC.description
                  FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId) MCDA
            INNER JOIN acdStateContract SC
                    ON SC.stateContractId = MCDA.statecontractid) AS forma_de_ingresso,
               isacademicenrolledinperiod(C.contractId, p_periodId) AS esta_matriculado_no_periodo,
                (SELECT MIN(E.dateEnroll)
                   FROM unit_acdEnroll E
             INNER JOIN unit_acdGroup G
                     ON G.groupId = E.groupId
             INNER JOIN unit_acdLearningPeriod LP
                     ON LP.learningPeriodId = G.learningPeriodId
                  WHERE LP.periodId = p_periodId
                    AND E.contractId = C.contractId
                    AND E.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::int) AS data_matricula,
               PP.email,
               (COALESCE(PP.workPhone, (SELECT phone
                                          FROM basPhone
                                         WHERE personId = PP.personId
                                           AND type = 'PRO' LIMIT 1))) AS telefone_trabalho,
               (COALESCE(PP.cellPhone, (SELECT phone
                                          FROM basPhone
                                         WHERE personId = PP.personId
                                           AND type = 'CEL' LIMIT 1))) AS telefone_celular,
               (COALESCE(PP.residentialPhone, (SELECT phone
                                                 FROM basPhone
                                                WHERE personId = PP.personId
                                                  AND type = 'RES' LIMIT 1))) AS telefone_residencial,
               --situacao financeira
               (CASE WHEN isDefaulter(C.personId::bigint) 
                THEN
                    'INADIMPLENTE'::VARCHAR
                ELSE
                    ' '::VARCHAR
                END) AS situacao_financeira,
               -- estado contratual autual
               (SELECT description
                  FROM acdStateContract
                 WHERE stateContractId = getContractState(C.contractId)) AS atual_estado_contratual,
               (SELECT COUNT(E.enrollId)
                  FROM acdEnroll E
            INNER JOIN acdGroup G
                    ON G.groupId = E.groupId
            INNER JOIN acdLearningPeriod LP
                    ON LP.learningPeriodId = G.learningPeriodId
                 WHERE E.contractId = C.contractId
                   AND LP.periodId = p_periodId
                   AND E.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT,
                                      getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
                                      getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::INT,
                                      getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS')::INT)) AS quantidade_disciplinas_confirmadas_no_periodo,
               --incentivos no periodo
               (SELECT array_to_string(array_agg(DISTINCT IT.description), ', ') 
                  FROM finincentive I 
            INNER JOIN ONLY finIncentiveType IT 
                    ON IT.incentiveTypeId = I.incentiveTypeId 
                 WHERE contractId = C.contractId
                   AND (C.beginDate, C.endDate) 
                       OVERLAPS (I.startDate, I.endDate)
                   AND (I.cancellationDate IS NULL 
                    OR cancellationDate >= NOW()::date)) AS incentivos_no_periodo,
               --convenios no periodo
               (SELECT ', ' || array_to_string(array_agg(DISTINCT CON.description), ', ')
                  FROM finconvenantperson CP 
            INNER JOIN finConvenant CON 
                    ON CON.convenantId = CP.convenantId 
                 WHERE CP.personId = C.personId 
                   AND NOW()::DATE BETWEEN CP.begindate AND CP.enddate)  AS convenios_no_periodo,
               -- carga horaria requerida do curso
               acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) AS carga_horaria_total_cursada,
               acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas(C.contractId) AS carga_horaria_total_cursada_atividades_complementares,
               acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, p_periodId) AS carga_horaria_total_matriculada_no_periodo, 
               CV.hourrequired::NUMERIC AS carga_horaria_requerida_do_curso,

               -- CARGA HORARIA RESTANTE PARA CONCLUSAO, comparando com o requerido.
               -- ch obrigatoria - carga horaria cumprida
               -- Sabendo que o curso pode oferecer mais horas do que o realmente requerido (hourrequired e hourTotal),
               -- é necessário controlar que sejam cumpridas ao menos as horas requeridas de disciplinas e de ATVC.
               -- O que o aluno fez a mais em disciplinas, não pode compensar horas não feitas em ATVC.
               -- Então abaixo há um controle que, caso o aluno tenha feito em disciplinas mais horas do que o requerido,
               -- utilize para o cálculo somente o requerido.
               -- Depois disso some as horas requeridas e "cursadas" de ATVC.
               -- A lógica é repetida duas vezes pois, é necessário verificar se o aluno cursou mais que o necessário,
               -- para não exibir carga horária negativa, sendo assim, utilizada mesma lógica para comparação e para utilização.
               (CASE WHEN (CV.hourrequired -
                           ((CASE WHEN ((acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) + 
                                         acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, '2015/1')) > (CV.hourrequired - 
                                         acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)))
                                  THEN
                                       CV.hourrequired - acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)
                                  ELSE
                                       (acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) + 
                                        acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, '2015/1'))
                             END) + acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas(C.contractId))) < 0
                     THEN
                          0
                     ELSE 
                          (CV.hourrequired -
                           ((CASE WHEN ((acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) + 
                                         acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, p_periodId)) > (CV.hourrequired - 
                                         acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)))
                                  THEN
                                       CV.hourrequired - acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)
                                  ELSE
                                       (acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) + 
                                        acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, p_periodId))
                             END) + acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas(C.contractId)))
                END)::NUMERIC AS carga_horaria_restante_para_conclusao,

               ((acd_obterCargaHorariaTotalCursadaDoContrato(C.contractId) +
                 acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas(C.contractId) +
                 acd_obterCargaHorariaTotalMatriculadaDoContrato(C.contractId, p_periodId)) >= 
                (CV.hourrequired - 
                 (SELECT acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)))) AS provavel_concluinte_no_periodo,
               (SELECT acdLearningPeriod.learningPeriodId 
                       FROM acdMovementContract 
                  LEFT JOIN acdLearningPeriod 
                      USING (learningPeriodId) 
                      WHERE contractId = C.contractId 
                   ORDER BY stateTime 
                 DESC LIMIT 1) as codigo_periodo_letivo_ultima_movimentacao,
                    (SELECT acdLearningPeriod.description
                       FROM acdMovementContract 
                  LEFT JOIN acdLearningPeriod 
                      USING (learningPeriodId) 
                      WHERE contractId = C.contractId 
                   ORDER BY stateTime 
                 DESC LIMIT 1) as periodo_letivo_ultima_movimentacao,
                 (SELECT acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(C.courseId, C.courseVersion, C.turnId, C.unitId)) AS carga_horaria_total_de_atividades_complementares_do_curso,
                 (SELECT acd_obterQuantidadeDeDisciplinasObrigatoriasRestantesACursar(C.contractId)) AS quantidade_disciplinas_obrigatorias_restantes_a_cursar,
                 (SELECT acd_obterQuantidadeDeDisciplinasBloqueadasPorRequisito(C.contractId, p_periodId)) AS quantidade_de_disciplinas_bloqueadas_por_requisito,
                 (SELECT description
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId)) AS descricao_forma_de_ingresso,
                 (SELECT learningperiodid
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId)) AS periodo_letivo_de_ingresso,
                 (SELECT statecontractid
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId)) AS estado_de_ingresso,
                 (SELECT statetime
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId)) AS statetime_de_ingresso,
                 (SELECT description
                  FROM acdStateContract
                 WHERE stateContractId = getContractStateByPeriod(C.contractId, p_periodId)) AS estado_contratual_no_periodo
          FROM (SELECT distinct xx1.*, xx2.begindate, xx2.enddate, xx4.prevPeriodId
                  FROM acdContract xx1 
             LEFT JOIN acdMovementContract MC
                    ON MC.contractId = xx1.contractId
             LEFT JOIN acdperiod xx4
                    ON xx4.periodid = p_periodId
             LEFT JOIN acdlearningperiod xx2
                    ON (xx2.courseid = xx1.courseid
                        AND xx2.courseversion = xx1.courseversion
                        AND xx2.turnid = xx1.turnid
                        AND xx2.unitid = xx1.unitid
                        AND xx2.periodid = xx4.periodid)
             LEFT JOIN acdLearningPeriod xx3
                    ON (xx3.courseid = xx1.courseid
                        AND xx3.courseversion = xx1.courseversion
                        AND xx3.turnid = xx1.turnid
                        AND xx3.unitid = xx1.unitid
                        AND xx3.periodId = xx4.prevPeriodId)
                   AND (CASE WHEN p_courseId IS NULL THEN TRUE ELSE xx1.courseId = p_courseId END)
                   AND (CASE WHEN p_courseVersion IS NULL THEN TRUE ELSE xx1.courseVersion = p_courseVersion END)
                   AND (CASE WHEN p_turnId IS NULL THEN TRUE ELSE xx1.turnId = p_turnId END)
                   AND (CASE WHEN p_unitId IS NULL THEN TRUE ELSE xx1.unitId = p_unitId END)
                   AND (CASE WHEN p_contractId IS NULL THEN TRUE ELSE xx1.contractId = p_contractId END)) C
    INNER JOIN ONLY basPhysicalPersonstudent PP
            ON PP.personId = C.personId
    INNER JOIN acdCourse CO
            ON CO.courseId = C.courseId
    INNER JOIN acdCourseVersion CV
            ON CV.courseId = C.courseId
           AND CV.courseVersion = C.courseVersion
    INNER JOIN basunit UT
            ON UT.unitid = C.unitid
    INNER JOIN basturn TU
            ON TU.turnid = C.turnid
    );
END;
$BODY$
LANGUAGE plpgsql;
