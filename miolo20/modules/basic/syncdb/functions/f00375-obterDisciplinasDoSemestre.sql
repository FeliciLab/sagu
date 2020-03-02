CREATE OR REPLACE FUNCTION obterDisciplinasDoSemestre(p_configuracaoDeMatricula INT, p_contrato INT, p_periodoLetivo INT, p_turma TEXT, p_tipoMatricula INT, p_eletivas BOOLEAN)
RETURNS SETOF DisciplinasSemestre AS
/*************************************************************************************
  NAME: obterDisciplinasDoSemestre
  PURPOSE: Obtem todas as disciplinas oferecidas no semestre para o aluno.
  DESCRIPTION: vide "PURPOSE".
               Tipo de matrícula: 1 - Sistema (academic).
                                  2 - Web (portal/services).
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       10/02/2015 Luís F. Wermann   1. Buscar disicplinas oferecidas no semestre.
**************************************************************************************/
$BODY$
DECLARE

--SQL para buscar disciplinas
v_SQL TEXT;

--Sinal para busca do semestre 0 (eletivas)
v_sinalSemester TEXT;
    
BEGIN

    --Caso estejamos procurando pelas eletivas vamos mudar o sinal do WHERE no semester > 0 para = 0, assim virão apenas as eletivas
    v_sinalSemester := '>';
    IF ( p_eletivas )
    THEN
        v_sinalSemester := '=';
    END IF;

    --Libera disciplinas de outros cursos
    IF ( obterConfiguracaoDeMatricula(p_configuracaoDeMatricula, 'showOtherCoursesGroups', 'showOotherCoursesGroupsExternal', p_tipoMatricula) )
    THEN
        v_SQL := ' SELECT DISTINCT (SELECT AC.curriculumId 
                      FROM acdCurriculum AC
                     WHERE AC.courseId = CT.courseId
                       AND AC.courseVersion = CT.courseVersion 
                       AND AC.turnId = CT.turnId
                       AND AC.unitId = CT.unitId
                       AND AC.curricularComponentId = A.curricularComponentId 
                       AND AC.curricularComponentVersion = A.curricularComponentVersion) AS curriculumId, 
                   (SELECT AC.semester
                      FROM acdCurriculum AC
                     WHERE AC.courseId = CT.courseId
                       AND AC.courseVersion = CT.courseVersion 
                       AND AC.turnId = CT.turnId
                       AND AC.unitId = CT.unitId
                       AND AC.curricularComponentId = A.curricularComponentId 
                       AND AC.curricularComponentVersion = A.curricularComponentVersion) AS semester, ';
        RAISE NOTICE 'Liberando disciplinas de outros cursos';
    ELSE
        v_SQL := 'SELECT DISTINCT A.curriculumId,
                                  A.semester, ';
    END IF;

    --Monta início da query
    v_SQL := v_SQL || 
                    ' A.curricularComponentId,
                    A.curricularComponentVersion,
                    C.name AS curricularComponentName,
                    A.turnId,
                    getTurnDescription(A.turnId) AS turnDescription,
                    A.unitId,
                    getUnitDescription(A.unitId) AS unitDescription,
                    G.groupId,
                    G.classId,
                    P.periodId,
                    P.learningPeriodId,
                    COALESCE((SELECT COUNT(enrollId) 
                                FROM acdEnroll 
                               WHERE groupId = G.groupId 
                                 AND statusId <> (getparameter(\'ACADEMIC\', \'ENROLL_STATUS_CANCELLED\')::INT)), 0)::INT AS totalEnrolled,
                    G.vacant,
                    G.regimenId,
                    RG.description AS regimenDescription,
                    getGroupStartDate(G.groupId) AS startDate,
                    getGroupEndDate(G.groupId) AS endDate,
                    K.name AS className,
                    E.enrollId AS enrollId,
                    E.statusId AS enrollStatusId,
                    getEnrollStatusDescription(E.statusId) AS enrollStatusDescription,
                    C.academicCredits,
                    C.lessonNumberHours,
                    A.curricularComponentTypeId,
                    C.academicNumberHours,
                    curriculumPossuiVinculoDeMatricula(A.curriculumId, P.periodId) AS possuiVinculo,
                    A.curriculumTypeId,
                    CUT.description AS curriculumTypeDescription,
                    CCT.description AS curricularComponentTypeDescription,
                    A.courseId::VARCHAR,
                    A.courseVersion::VARCHAR,
                    getCourseName(A.courseId) AS courseName,
                    curriculumEstaPendente(CT.contractId, A.curriculumId, P.learningPeriodId) AS estaPendente
               FROM acdCurriculum A
         INNER JOIN acdCurricularComponent C
                 ON (A.curricularComponentId,
                     A.curricularComponentVersion) = (C.curricularComponentId,
                                                      C.curricularComponentVersion)
         INNER JOIN acdGroup G
                 ON (G.curriculumId = A.curriculumId)
                AND (G.isCancellation IS FALSE)
         INNER JOIN acdClass K
                 ON (G.classId = K.classId)
         INNER JOIN acdContract CT
                 ON (CT.contractId = ' || p_contrato || ')
         INNER JOIN acdCurricularComponentType CCT
                 ON (A.curricularComponentTypeId = CCT.curricularComponentTypeId)
         INNER JOIN acdCurriculumType CUT
                 ON (A.curriculumTypeId = CUT.curriculumTypeId) 
          LEFT JOIN acdEnroll E
                 ON (E.contractId = CT.contractId)
                AND (E.groupId = G.groupId)
         INNER JOIN acdLearningPeriod P
                 ON (P.learningPeriodId = G.learningPeriodId)
                AND (P.isClosed IS FALSE)
         INNER JOIN acdRegimen RG
                 ON (RG.regimenId = G.regimenId) ';

    --Libera disciplinas de outros turnos
    IF ( obterConfiguracaoDeMatricula(p_configuracaoDeMatricula, 'showOtherTurnsGroups', 'showOtherTurnsGroupsExternal', p_tipoMatricula) )
    THEN
        RAISE NOTICE 'Liberando disciplinas de outros turnos';
    ELSE
        v_SQL := v_SQL || ' INNER JOIN basTurn T
                                   ON (A.turnId = T.turnId AND CT.turnId = A.turnId) ';
    END IF;

    --Libera disciplinas de outras unidades
    IF ( obterConfiguracaoDeMatricula(p_configuracaoDeMatricula, 'showOtherUnitsGroups', 'showOotherUnitsGroupsExternal', p_tipoMatricula) )
    THEN
        RAISE NOTICE 'Liberando disciplinas de outras unidades';
    ELSE
        v_SQL := v_SQL || ' INNER JOIN basUnit U
                                   ON (A.unitId = U.unitId AND CT.unitId = A.unitId) ';
    END IF;

    --Inicia WHERE
    v_SQL := v_SQL || ' WHERE CT.contractId = ' || p_contrato ||
                        ' AND P.periodId IN (SELECT periodId 
                                               FROM acdLearningPeriod
                                              WHERE learningPeriodId = ' || p_periodoLetivo || ')';

    --Continuação do WHERE, negar oferecidas que estejam DISPENSADAS ou APROVADAS
    v_SQL := v_SQL ||  ' AND A.curricularComponentId NOT IN ( SELECT BB.curricularcomponentid 
                                                                 FROM acdEnroll AA
                                                           INNER JOIN acdCurriculum BB
                                                                   ON (BB.curriculumId = AA.curriculumId)
                                                           INNER JOIN acdContract CC
                                                                   ON (CC.contractId = AA.contractId)
                                                                WHERE CC.contractId = ' || p_contrato || '
                                                                  AND CC.courseId = BB.courseId
                                                                  AND CC.courseVersion = BB.courseVersion
                                                                  AND CC.unitId = BB.unitId
                                                                  AND CC.turnId = BB.turnId
                                                                  AND BB.curricularcomponentid = A.curricularComponentId
                                                                  AND BB.curricularcomponentversion = A.curricularComponentversion
                                                                  AND AA.statusId IN ((SELECT getParameter(''' || 'ACADEMIC' || ''', ''' || 'ENROLL_STATUS_APPROVED' || ''')::INT), 
                                                                                      (SELECT getParameter(''' || 'ACADEMIC' || ''',''' || 'ENROLL_STATUS_EXCUSED' || ''')::INT)))';

    --Libera disciplinas de outras turmas
    IF ( obterConfiguracaoDeMatricula(p_configuracaoDeMatricula, 'showOtherClassesGroups', 'showOtherClassesGroupsExternal', p_tipoMatricula ) )
    THEN
        RAISE NOTICE 'Liberando disciplinas de outras turmas';
    ELSE
        v_SQL := v_SQL || ' AND G.classId = ''' || p_turma || '''';
    END IF;

    --Libera disciplinas de outros cursos
    IF ( obterConfiguracaoDeMatricula(p_configuracaoDeMatricula, 'showOtherCoursesGroups', 'showOotherCoursesGroupsExternal', p_tipoMatricula) )
    THEN
        --Só pode procurar por disciplinas que existam na matriz do aluno (o que diz se a disciplina é da matriz curricular são os dois
        --ids do componentCurricular
        v_SQL := v_SQL || ' AND EXISTS ( SELECT curriculumId
                                           FROM acdCurriculum
                                          WHERE ( courseId,
                                                  courseVersion,
                                                  turnId,
                                                  unitid ) = ( CT.courseId,
                                                               CT.courseVersion,
                                                               CT.turnId,
                                                               CT.unitId )
                                            AND curricularComponentId = A.curricularComponentId
                                            AND curricularComponentVersion = A.curricularComponentVersion
                                            AND semester ' || v_sinalSemester || ' 0 )';
    ELSE
        v_SQL := v_SQL || ' AND CT.courseId = A.courseId
                            AND CT.courseVersion = A.courseVersion 
                            AND EXISTS ( SELECT curriculumId
                                           FROM acdCurriculum
                                          WHERE ( courseId,
                                                  courseVersion,
                                                  turnId,
                                                  unitid ) = ( A.courseId,
                                                               A.courseVersion,
                                                               A.turnId,
                                                               A.unitId )
                                            AND curricularComponentId = A.curricularComponentId
                                            AND curricularComponentVersion = A.curricularComponentVersion
                                            AND semester ' || v_sinalSemester || ' 0 ) ';
    END IF;

    --Caso não esteja buscando eletivas, desconsidera o semestre
    IF ( p_eletivas IS FALSE )
    THEN
        v_SQL := v_SQL || ' AND A.semester ' || v_sinalSemester || ' 0 ';
    END IF;

    --Se o parâmetro ENROLL_SHOW_ONLY_WITH_SCHEDULE estiver habilitado
    --a oferecida precisa ter pelo menos um schedule cadastrado
    IF ( (SELECT getParameter('ACADEMIC', 'ENROLL_SHOW_ONLY_WITH_SCHEDULE')::BOOLEAN) IS TRUE )
    THEN
        v_SQL := v_SQL || ' AND (SELECT COUNT(*)
                                   FROM acdSchedule
                                  WHERE groupId = G.groupId) > 0 ';
    END IF;
    
    ------ATENÇÃO, LEMBRAR QUE AO MUDAR ALGUMA COISA AQUI, COMO ADICIONAR UM COLUNA, DEVE SER FEITO O MESMO PARA O SQL DAS SUBSTITUTAS E DOS VÍNCULOS------

    --Busca substitutas e faz UNION ALL
    v_SQL := v_SQL || ' UNION ALL ' || obterSqlParaBuscarSubstitutasOferecidas(p_contrato, p_periodoLetivo, p_eletivas);

    --Busca vinculadas e faz UNION ALL, a não ser que esteja buscando eletivas (essas não podem ser cadastradas como vínculo)
    IF ( p_eletivas IS FALSE )
    THEN
        v_SQL := v_SQL || ' UNION ALL ' || obterSqlParaBuscarVinculadasOferecidas(p_contrato, p_periodoLetivo);
    END IF;

    --Adicionar ORDER BY
    v_SQL := v_SQL || ' ORDER BY semester, curricularComponentName, unitDescription, turnDescription ';
    
   --RAISE EXCEPTION '%', v_SQL;

RETURN QUERY EXECUTE v_SQL;

END;
$BODY$ LANGUAGE plpgsql;