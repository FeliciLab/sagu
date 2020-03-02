CREATE OR REPLACE FUNCTION acd_obterCargaHorariaTotalMatriculadaDoContrato(p_contractId INT, p_periodId CHAR)
RETURNS NUMERIC AS
$BODY$
/*********************************************************************************************
  NAME: acd_obterCargaHorariaTotalMatriculadaDoContrato
  PURPOSE: Obtém a carga horária total matriculada e pré-matricula do contrato do aluno.
	   do curso do contrato.
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       16/03/2014 Augusto A. SIlva  1. Função criada
*********************************************************************************************/
DECLARE
    v_carga_horaria_total_matriculada NUMERIC;
BEGIN
    SELECT INTO v_carga_horaria_total_matriculada
		SUM(CC.academicnumberhours) AS carga_horaria_total_matriculada_no_periodo
	   FROM unit_acdEnroll AA
     INNER JOIN unit_acdCurriculum BB 
	     ON (AA.curriculumid = BB.curriculumid)
		
     INNER JOIN acdCurricularComponent CC 
	     ON (BB.curricularComponentId, 
		 BB.curricularComponentVersion) = (CC.curricularComponentId, 
						   CC.curricularComponentVersion) 
     INNER JOIN unit_acdGroup G
	     ON G.groupId = AA.groupId
     INNER JOIN unit_acdLearningPeriod LP
	     ON LP.learningPeriodId = G.learningPeriodId
	  WHERE AA.contractId = p_contractId
	    AND LP.periodId = p_periodId
	    AND AA.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT,
				getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT)
	    AND NOT EXISTS (SELECT enrollid 
			      FROM acdcomplementaryactivities 
			     WHERE enrollid = AA.enrollid)
	    AND AA.finalNote IS NULL
       GROUP BY AA.contractid, LP.periodId;

    RETURN COALESCE(v_carga_horaria_total_matriculada, 0);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
