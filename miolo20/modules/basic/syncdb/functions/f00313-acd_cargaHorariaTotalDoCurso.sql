CREATE OR REPLACE FUNCTION acd_cargaHorariaTotalDoCurso(p_courseId VARCHAR, p_courseVersion INT, p_turnId INT, p_unitId INT)
RETURNS FLOAT AS
$BODY$
BEGIN
    RETURN (SELECT SUM(CC.academicNumberHours)
              FROM acdCurriculum CM
	INNER JOIN acdCurricularComponent CC
		ON (CC.curricularComponentId,
		    CC.curricularComponentVersion) = (CM.curricularComponentId,
						      CM.curricularComponentVersion)
	     WHERE (CM.courseId,
	            CM.courseVersion,
	            CM.turnId,
	            CM.unitId) = (p_courseId, 
				  p_courseVersion, 
				  p_turnId, 
				  p_unitId)
	       AND CM.semester <> 0);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
