CREATE OR REPLACE FUNCTION acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas(p_contractId INT)
RETURNS NUMERIC AS
$BODY$
/*********************************************************************************************
  NAME: acd_obterCargaHorariaTotalDeAtividadesComplementaresCursadas
  PURPOSE: Obtém a carga horária total cursada de atividades complementares do contrato, relacionadas
	   as atividades complementares obrigatórias da matriz curricular do curso do aluno.
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
		SUM(X.carga_horaria_total_cursada)
	   FROM (SELECT C.curriculumId,
			CC.academicNumberHours,
			(CASE WHEN SUM(CA.totalHours) > CC.academicNumberHours
			      THEN
				   CC.academicNumberHours
			      ELSE
				   SUM(CA.totalHours)
			 END) AS carga_horaria_total_cursada
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
	     INNER JOIN acdcomplementaryactivities CA
		     ON CA.enrollId = E.enrollId
		  WHERE E.contractId = p_contractId
		    AND E.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
				       getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
		GROUP BY C.curriculumId, CC.academicNumberHours) X;

    RETURN COALESCE(v_carga_horaria_total_cursada, 0);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
