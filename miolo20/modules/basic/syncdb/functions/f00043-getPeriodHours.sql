CREATE OR REPLACE FUNCTION getPeriodHours(p_courseid varchar, p_courseversion int, p_turnid int, p_unitid int, p_semester int)
RETURNS FLOAT AS
$BODY$
/*********************************************************************************************
  NAME: getperiodhours
  PURPOSE: Obtem carga horaria do periodo
*********************************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT SUM(B.academicNumberHours)
                  FROM acdCurriculum A
            INNER JOIN acdCurricularComponent B
                    ON (B.curricularComponentId = A.curricularComponentId
                   AND  B.curricularComponentVersion = A.curricularComponentVersion)
                 WHERE A.courseId = p_courseid
                   AND A.courseVersion = p_courseversion
                   AND A.turnId = p_turnid
                   AND A.unitId = p_unitid
                   AND A.semester = p_semester
                   AND A.curriculumTypeId::varchar = ANY(STRING_TO_ARRAY(GETPARAMETER('ACADEMIC', 'CURRICULUM_TYPE_NON_OPTIONAL'), ','))
                   );
END
$BODY$
LANGUAGE 'plpgsql';
