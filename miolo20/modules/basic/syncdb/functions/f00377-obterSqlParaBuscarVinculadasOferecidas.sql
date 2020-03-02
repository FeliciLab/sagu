CREATE OR REPLACE FUNCTION obterSqlParaBuscarVinculadasOferecidas(p_contrato INTEGER, p_periodoLetivo INTEGER)
RETURNS TEXT AS
/*************************************************************************************
  NAME: obterSqlParaBuscarVinculadasOferecidas
  PURPOSE: Obtém o SQL para buscar as disciplinas vinculadas do aluno.
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Função criada.
**************************************************************************************/
$BODY$
    DECLARE
    
    v_SQL TEXT;
    
    BEGIN

        v_SQL := ' SELECT DISTINCT A.curriculumId,
                    A.semester, 
                    KA.curricularComponentId,
                    KA.curricularComponentVersion,
                    C.name || ''' || ' VINC' || ''' || A.curriculumId  AS curricularComponentName,
                    KA.turnId,
                    getTurnDescription(KA.turnId) AS turnDescription,
                    KA.unitId,
                    getUnitDescription(KA.unitId) AS unitDescription,
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
                    KA.curricularComponentTypeId,
                    C.academicNumberHours,
                    TRUE AS possuiVinculo,
                    A.curriculumTypeId,
                    CUT.description AS curriculumTypeDescription,
                    CCT.description AS curricularComponentTypeDescription,
                    A.courseId::VARCHAR,
                    A.courseVersion::VARCHAR,
                    getCourseName(A.courseId) AS courseName,
                    FALSE AS estaPendente
               FROM acdCurriculum A
         INNER JOIN acdCurriculumLink CK
                 ON (A.curriculumId = CK.curriculumId AND CK.utilizaVinculoParaMatricula)
         INNER JOIN acdCurriculum KA
                 ON (KA.curriculumId = CK.curriculumLinkId)
         INNER JOIN acdCurricularComponent C
                 ON (KA.curricularComponentId,
                     KA.curricularComponentVersion) = (C.curricularComponentId,
                                                      C.curricularComponentVersion)
         INNER JOIN acdCurricularComponent KC
                 ON (A.curricularComponentId,
                     A.curricularComponentVersion) = (KC.curricularComponentId,
                                                      KC.curricularComponentVersion)
         INNER JOIN acdGroup G
                 ON (CK.curriculumLinkId = G.curriculumId)
                AND (G.isCancellation IS FALSE)
         INNER JOIN acdCurricularComponentType CCT
                 ON (A.curricularComponentTypeId = CCT.curricularComponentTypeId)
         INNER JOIN acdCurriculumType CUT
                 ON (A.curriculumTypeId = CUT.curriculumTypeId) 
         INNER JOIN acdClass K
                 ON (G.classId = K.classId)
         INNER JOIN acdContract CT
                 ON (CT.contractId = ' || p_contrato || ')
          LEFT JOIN acdEnroll E
                 ON (E.contractId = CT.contractId)
                AND (G.groupId = E.groupId)
         INNER JOIN acdLearningPeriod P
                 ON (P.learningPeriodId = G.learningPeriodId)
                AND (P.isClosed IS FALSE)
         INNER JOIN acdRegimen RG
                 ON (RG.regimenId = G.regimenId)
              WHERE A.courseId = CT.courseId
                AND A.courseVersion = CT.courseVersion
                AND A.turnId = CT.turnId
                AND A.unitId = CT.unitId
                AND P.periodId = (SELECT periodId 
                                    FROM acdLearningPeriod 
                                   WHERE learningPeriodId = ' || p_periodoLetivo || ')
                AND NOT EXISTS (SELECT enrollId
                                  FROM acdEnroll
                                 WHERE contractId = ' || p_contrato || '
                                   AND curriculumId = A.curriculumId
                                   AND statusId IN ((SELECT getParameter(''' || 'ACADEMIC' || ''', ''' || 'ENROLL_STATUS_APPROVED' || ''')::INT), 
                                                    (SELECT getParameter(''' || 'ACADEMIC' || ''',''' || 'ENROLL_STATUS_EXCUSED' || ''')::INT)))';

    --Se o parâmetro ENROLL_SHOW_ONLY_WITH_SCHEDULE estiver habilitado
    --a oferecida precisa ter pelo menos um schedule cadastrado
    IF ( (SELECT getParameter('ACADEMIC', 'ENROLL_SHOW_ONLY_WITH_SCHEDULE')::BOOLEAN) IS TRUE )
    THEN
        v_SQL := v_SQL || ' AND (SELECT COUNT(*)
                                   FROM acdSchedule
                                  WHERE groupId = G.groupId) > 0 ';
    END IF;    
    
    RETURN v_SQL;

    END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;
