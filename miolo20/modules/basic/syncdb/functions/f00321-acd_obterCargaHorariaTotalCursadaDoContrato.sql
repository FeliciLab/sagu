CREATE OR REPLACE FUNCTION acd_obterCargaHorariaTotalCursadaDoContrato(p_contractId INT)
RETURNS NUMERIC AS
$BODY$
/*********************************************************************************************
  NAME: acd_obterCargaHorariaTotalCursadaDoContrato
  PURPOSE: Obtém a carga horária total cursada, pelas disciplinas obrigatótias da matriz 
	   do curso do contrato.
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       16/03/2014 Augusto A. SIlva  1. Função criada
*********************************************************************************************/
DECLARE
    v_carga_horaria_total_cursada NUMERIC;
BEGIN
    SELECT INTO v_carga_horaria_total_cursada
		SUM(CC.academicnumberhours) AS carga_horaria_total_cursada
	   FROM unit_acdEnroll E
     INNER JOIN unit_acdContract CO
	     ON E.contractId = CO.contractId
     INNER JOIN unit_acdCurriculum C 
	     ON (E.curriculumid = C.curriculumid)
	    AND (C.courseId,
		 C.courseVersion,
		 C.turnId,
		 C.unitId) = (CO.courseId,
			      CO.courseVersion,
		              CO.turnId,
		              CO.unitId)
     INNER JOIN acdCurricularComponent CC 
             ON (C.curricularComponentId, 
	         C.curricularComponentVersion) = (CC.curricularComponentId, 
						   CC.curricularComponentVersion) 
          WHERE E.contractId = p_contractId
            AND E.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
			       getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
            AND C.curriculumTypeId <> getParameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::INT
            AND NOT EXISTS (SELECT enrollid 
			      FROM acdcomplementaryactivities 
			     WHERE enrollid = E.enrollid);

    RETURN COALESCE(v_carga_horaria_total_cursada, 0);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
