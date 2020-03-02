CREATE OR REPLACE FUNCTION MAIN_ATTENDANCE_REPORT(p_group_id integer)
RETURNS TABLE(groupid integer,
              curricularComponent text,
              professor text,
              periodId varchar,
              unit text,
              lessonNumberHours float,
              minimumNumberHours float,
              room text,
              center text,
              classId varchar,
              coursename text,
              minimumFrequency float,
              curricularComponentUnblocks text,
              courseId VARCHAR,
              className TEXT) AS
$BODY$
/******************************************************************************
  NAME: MAIN_ATTENDANCE_REPORT
  DESCRIPTION: FUNÇÃO que retorna dados de disciplina (utilizado em relatórios jasper)

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       11/11/2010 Fabiano Tomasini  1. FUNÇÃO criada.
  1.1       06/07/2011 Moises Heberle    2. Alterado para nao duplicar registros
******************************************************************************/
DECLARE
    v_select text;
BEGIN
    v_select := 'SELECT DISTINCT B.groupId,
                                 E.curricularComponentId || '' - '' || E.name as curricularComponent,
                                 ARRAY_TO_STRING(ARRAY(SELECT DISTINCT J.personId || '' - '' || J.name as professor
                                                                  FROM acdSchedule _F
                                                            INNER JOIN acdScheduleProfessor I
                                                                    ON _F.scheduleId = I.scheduleId
                                                       INNER JOIN ONLY basPhysicalPersonProfessor J
                                                                    ON I.professorId = J.personId
                                                            INNER JOIN acdLearningPeriod C
                                                                    ON (B.learningPeriodId = C.learningPeriodId)
                                                                 WHERE B.groupId = _F.groupId), '', '') AS professor,

                                 C.periodId,
                                 K.description as unit,
                                 E.lessonNumberHours,
                                 ROUND(CAST ((C.minimumFrequency * E.lessonNumberHours / 100) AS numeric), 2)::FLOAT as minimumNumberHours,
                                 G.room || '' - '' || G.building as room,
                                 L.name as center,
                                 B.classId,
                                 M.name as coursename,
                                 C.minimumFrequency,
                                 ( SELECT array_to_string(array_agg(''('' || X.curricularComponentId || '' - '' || X.name || '')''), '', '')
				     FROM ( SELECT DISTINCT CC.curricularComponentId, CC.name
						       FROM acdCurricularComponentUnblock AA
					         INNER JOIN acdCurriculum BB
						      USING (curriculumId)
					         INNER JOIN acdCurricularComponent CC
						         ON (CC.curricularComponentId = AA.curricularComponentId 
						        AND CC.curricularComponentVersion = AA.curricularComponentVersion)
					         INNER JOIN acdCurricularComponent DD
						         ON (DD.curricularComponentId = BB.curricularComponentId 
						        AND DD.curricularComponentVersion = BB.curricularComponentVersion)
						      WHERE BB.curricularComponentId = E.curricularComponentId ) X
				    WHERE X.curricularComponentId <> E.curricularComponentId ) AS curricularComponentUnblocks,
                                 H.courseId,
                                 getclassname(B.classId) as className
                            FROM acdGroup B
                      INNER JOIN acdLearningPeriod C
                              ON (B.learningPeriodId = C.learningPeriodId)
                      INNER JOIN acdCurriculum D
                              ON (B.curriculumId = D.curriculumId)
                      INNER JOIN acdCurricularComponent E
                              ON (D.curricularComponentId = E.curricularComponentId AND
                                 D.curricularComponentVersion = E.curricularComponentVersion)
                       LEFT JOIN acdSchedule F
                              ON (B.groupId = F.groupId)
                       LEFT JOIN insPhysicalResource G
                              ON (G.physicalResourceId = F.physicalResourceId AND
                                  G.physicalResourceVersion = F.physicalResourceVersion)
                      INNER JOIN acdCourse H
                              ON (H.courseId = D.courseId)
                      INNER JOIN basUnit K
                              ON (C.unitId = K.unitId)
                       LEFT JOIN acdCenter L
                              ON (L.centerId = H.centerId)
                      INNER JOIN acdCourse M
                              ON (M.courseId = D.courseId)
                           WHERE  B.groupId = '''||p_group_id||'''';

    RETURN QUERY EXECUTE v_select;
END;
$BODY$ language 'plpgsql';
