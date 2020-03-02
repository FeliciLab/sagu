CREATE OR REPLACE FUNCTION getPendingCurriculumIds(p_contractid int, p_semesterlimit int)
RETURNS SETOF int AS
$BODY$
/*********************************************************************************************
  NAME: getPendingCurriculumIds
  PURPOSE: Obtem as disciplinas pendentes, que nao foram cursadas no semestre
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY (SELECT A.curriculumId
                  FROM acdCurriculum A
            INNER JOIN acdContract B
                    ON (B.courseId = A.courseId
                    AND B.courseVersion = A.courseVersion
                    AND B.turnId = A.turnId
                    AND B.unitId = A.unitId)
            INNER JOIN acdCurricularComponent C
                    ON (C.curricularComponentId = A.curricularComponentId
                    AND C.curricularComponentVersion = A.curricularComponentVersion)
                 WHERE B.contractId = p_contractid
                   AND A.semester BETWEEN 1 AND p_semesterlimit
                   AND NOT EXISTS (SELECT 'x'
                                     FROM acdEnroll X
                                    WHERE X.contractId = B.contractId
                                      AND X.curriculumId = A.curriculumId
                                      AND X.statusId::varchar = ANY(STRING_TO_ARRAY(GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPR_OR_EXC'), ',')))
              ORDER BY A.curricularComponentId || '' || A.curricularComponentVersion || '' || C.name);
END
$BODY$
LANGUAGE 'plpgsql';
