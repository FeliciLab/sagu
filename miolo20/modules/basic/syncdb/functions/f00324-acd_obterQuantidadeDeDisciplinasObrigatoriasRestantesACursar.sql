CREATE OR REPLACE FUNCTION acd_obterQuantidadeDeDisciplinasObrigatoriasRestantesACursar(p_contractId INT)
RETURNS INT AS
$BODY$
BEGIN
    RETURN (
        SELECT COUNT(C.curriculumId)
	  FROM acdCurriculum C
    INNER JOIN acdContract CO
	    ON CO.contractId = p_contractId
     LEFT JOIN acdEnroll E
	    ON E.curriculumId = C.curriculumId
	   AND E.contractId = CO.contractId
	   AND E.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
			      getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT,
			      getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT,
			      getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT)
	 WHERE (C.courseId,
		C.courseVersion,
		C.turnId,
		C.unitId) = (CO.courseId, 
                             CO.courseVersion, 
                             CO.turnId, 
                             CO.unitId)
	   AND C.curricularComponentTypeId = getParameter('ACADEMIC', 'CURRICULAR_COMPONENT_TYPE_CURRICULAR_COMPONENT')::INT
           AND C.curriculumTypeId = getParameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_MINIMUM')::INT
	   AND E.enrollId IS NULL
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
