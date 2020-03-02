CREATE OR REPLACE FUNCTION obterSqlParaBuscarSubstitutasOferecidas(p_contrato INTEGER, p_periodoLetivo INTEGER, p_eletivas BOOLEAN)
RETURNS TEXT AS
/*************************************************************************************
  NAME: obterSqlParaBuscarSubstitutasOferecidas
  PURPOSE: Obtém o SQL para buscar as disciplinas substituídas/desbloqueadas do aluno.
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Função criada.
**************************************************************************************/
$BODY$
DECLARE
    v_SQL TEXT;
    v_sinalSemester TEXT := '>';

BEGIN
    --Se estiver buscando eletivas precisa considerar o semestre > 0 
    IF ( p_eletivas IS TRUE )
    THEN
        v_sinalSemester := '=';
    END IF;

    v_SQL := '   SELECT DISTINCT 
                        FT.curriculumId,
                        Z.semester,
                        FT.curricularComponentId,
                        A.curricularComponentVersion,
                        C.name || ''' || ' DESB' || ''' || FT.curriculumId  AS curricularComponentName,
                        A.turnId,
                        getTurnDescription(A.turnId) AS turnDescription,
                        A.unitId,
                        getUnitDescription(A.unitId) AS unitName,
                        D.groupId,
                        D.classId,
                        (SELECT periodId FROM acdLearningPeriod WHERE D.learningPeriodId = acdLearningPeriod.learningPeriodId),
                        D.learningPeriodId,
                        COALESCE((SELECT COUNT(enrollId) 
                                FROM acdEnroll 
                               WHERE groupId = D.groupId 
                                 AND statusId <> (getparameter(\'ACADEMIC\', \'ENROLL_STATUS_CANCELLED\')::INT)), 0)::INT AS totalEnrolled,
                        D.vacant,
                        D.regimenId,
                        H.description AS regimenDescription,
                        null AS startDate,
                        null AS endDate,
                        K.name AS className,
                        E.enrollId,
                        E.statusId AS enrollstatusId,
                        getEnrollStatusDescription(E.statusId) AS enrollStatusDescription,
                        C.academiccredits,
                        C.lessonnumberhours,
                        A.curricularcomponenttypeid,
                        C.academicNumberHours,
                        FALSE as possuiVinculo,
                        A.curriculumTypeId,
                        CUT.description AS curriculumTypeDescription,
                        CCT.description AS curricularComponentTypeDescription,
                        A.courseId::VARCHAR,
                        A.courseVersion::VARCHAR,
                        getCourseName(A.courseId) AS courseName,
                        FALSE AS estaPendente
                 FROM acdCurricularComponentUnblock FT
           INNER JOIN acdCurricularComponent C
                   ON (C.curricularComponentId = FT.curricularComponentId
                  AND C.curricularComponentVersion = FT.curricularComponentVersion )
           INNER JOIN acdCurriculum A
                   ON (C.curricularComponentId = A.curricularComponentId
                  AND C.curricularComponentVersion = A.curricularComponentVersion )
           INNER JOIN acdCurricularComponentType CCT
                   ON (A.curricularComponentTypeId = CCT.curricularComponentTypeId)
           INNER JOIN acdCurriculumType CUT
                   ON (A.curriculumTypeId = CUT.curriculumTypeId) 
           INNER JOIN acdCurriculum Z
                   ON ( Z.curriculumId = FT.curriculumId )
           INNER JOIN acdCurricularComponent W
                   ON (W.curricularComponentId = Z.curricularComponentId
                           AND W.curricularComponentVersion = Z.curricularComponentVersion )

           INNER JOIN acdGroup D
                  ON (A.curriculumId = D.curriculumId)
           INNER JOIN basTurn F
                   ON F.turnId = A.turnId 
           INNER JOIN basUnit G
                   ON G.unitId = A.unitId  
           INNER JOIN acdRegimen H
                   ON H.regimenId = D.regimenId
           INNER JOIN acdClass K
                   ON K.classId = D.classId
            LEFT JOIN acdEnroll E
                   ON (E.contractId = ' || p_contrato || ')
                  AND (E.groupId = D.groupId)
                WHERE FT.contractid = ' || p_contrato || '
                  AND D.isCancellation IS FALSE
                  AND D.learningPeriodId IN (SELECT X.learningPeriodId 
                                              FROM acdLearningPeriod X
                                             WHERE periodId IN (SELECT periodId 
                                                                  FROM acdLearningPeriod 
                                                                 WHERE learningPeriodId = ' || p_periodoLetivo || ')                                                   
                                                                    AND NOT X.isClosed) 
                   AND FT.learningPeriodId IN (SELECT X.learningPeriodId 
                                              FROM acdLearningPeriod X
                                             WHERE periodId IN (SELECT periodId 
                                                                  FROM acdLearningPeriod 
                                                                 WHERE learningPeriodId = ' || p_periodoLetivo || ')
                                               AND NOT X.isClosed)
                   AND A.curriculumId' || v_sinalSemester || ' 0 ';

    --Se o parâmetro ENROLL_SHOW_ONLY_WITH_SCHEDULE estiver habilitado
    --a oferecida precisa ter pelo menos um schedule cadastrado
    IF ( (SELECT getParameter('ACADEMIC', 'ENROLL_SHOW_ONLY_WITH_SCHEDULE')::BOOLEAN) IS TRUE )
    THEN
        v_SQL := v_SQL || ' AND (SELECT COUNT(*)
                                   FROM acdSchedule
                                  WHERE groupId = D.groupId) > 0 ';
    END IF;

    RETURN v_SQL;
END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;