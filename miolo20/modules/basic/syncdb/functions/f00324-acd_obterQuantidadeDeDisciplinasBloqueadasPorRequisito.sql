CREATE OR REPLACE FUNCTION acd_obterQuantidadeDeDisciplinasBloqueadasPorRequisito(p_contractId INT, p_periodId CHAR)
RETURNS INT AS
$BODY$
BEGIN
    RETURN (
        SELECT COUNT(*)
	  FROM acdCurriculum C
    INNER JOIN acdContract CO
	    ON CO.contractId = p_contractId
    INNER JOIN acdCondition CON
	    ON CON.curriculumId = C.curriculumId
	 WHERE (C.courseId,
		C.courseVersion,
		C.turnId,
		C.unitId) = (CO.courseId,
			     CO.courseVersion,
			     CO.turnId,
			     CO.unitId)
	   AND (CASE WHEN CON.conditionCurriculumId IS NOT NULL
		     THEN
			  (SELECT COUNT(E.enrollId) > 0
			     FROM acdEnroll E
		       INNER JOIN acdGroup G
			       ON G.groupId = E.groupId
			    WHERE E.contractId = CO.contractId
			      AND E.curriculumId = CON.conditionCurriculumId
			      AND E.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
					         getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT,
					         getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT)) IS FALSE
		     ELSE
		          FALSE

		END)
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;

