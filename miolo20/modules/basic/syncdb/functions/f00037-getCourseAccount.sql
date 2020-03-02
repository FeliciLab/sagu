drop type if exists getCourseAccountType cascade;
create type getCourseAccountType AS (courseid varchar, coursversion int, coursename text, unitid int, accountSchemeId varchar, accountSchemeDescription text, costcenterid varchar, costCenterDescription text);

CREATE OR REPLACE FUNCTION getCourseAccount(p_courseid varchar, p_courseversion int, p_unitid int)
RETURNS SETOF getCourseAccountType AS
$BODY$
/*********************************************************************************************
  NAME: getCourseAccount
  PURPOSE: Get course account
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY   (SELECT A.courseId,
                           A.courseVersion,
                           B.name AS courseName,
                           A.unitId,
                           A.accountSchemeId,
                           C.description AS accountSchemeDescription,
                           A.costCenterId,
                           D.description AS costCenterDescription
                     FROM accCourseAccount A
               INNER JOIN acdCourse B
                       ON B.courseId = A.courseId
               INNER JOIN accAccountScheme C
                       ON C.accountSchemeId = A.accountSchemeId
               INNER JOIN accCostCenter D
                       ON D.costCenterId = A.costCenterId
                    WHERE A.courseId = p_courseid
                      AND A.courseVersion = p_courseversion
                      AND A.unitId = p_unitid );
END
$BODY$
LANGUAGE 'plpgsql';
