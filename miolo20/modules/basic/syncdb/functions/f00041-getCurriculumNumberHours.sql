drop type if exists getCurriculumNumberHours cascade;
create type getCurriculumNumberHours AS (academicnumberhours float, practicalnumberhours float, theoreticalnumberhours float);
CREATE OR REPLACE FUNCTION getCurriculumNumberHours(p_curriculumid int)
RETURNS SETOF getCurriculumNumberHours AS
$BODY$
/*********************************************************************************************
  NAME: getCurriculumNumberHours
  PURPOSE: Gets the different number hours of a curriculum
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY (SELECT B.academicNumberHours,
                         B.practicalNumberHours,
                         B.theoreticalNumberHours
                   FROM acdCurriculum A
             INNER JOIN acdCurricularComponent B
                     ON (B.curricularComponentId = A.curricularComponentId AND
                         B.curricularComponentVersion = A.curricularComponentVersion)
                  WHERE A.curriculumId = p_curriculumid );
END
$BODY$
LANGUAGE 'plpgsql';
