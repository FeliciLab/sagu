CREATE OR REPLACE FUNCTION obterPontuacaoAcumuladaPorSemestre(p_contractId INT, p_learningPeriodId INT, p_semester INT)
RETURNS INT AS
$BODY$
DECLARE
    v_enroll_status_approved INT := getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT;
    v_enroll_status_excused INT := getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT;
    v_enroll_status_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT;
    v_enroll_status_pre_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT;
BEGIN
    RETURN (SELECT SUM(X.pontos)
	      FROM (--Busca por oferecimentos da matriz do aluno.
	            SELECT COUNT(DISTINCT B.curriculumId) AS pontos
		      FROM acdContract A
		INNER JOIN acdCurriculum B 
			ON (B.courseId,
			    B.courseVersion,
			    B.turnId,
			    B.unitId) = (A.courseId,
					 A.courseVersion,
					 A.turnId,
					 A.unitId)
		       AND B.semester = p_semester    
		INNER JOIN acdGroup C
			ON C.curriculumId = B.curriculumId 
		       AND C.learningPeriodId = p_learningPeriodId	       
		 LEFT JOIN acdEnroll D
			ON D.curriculumId = B.curriculumId 
		       AND D.contractId = A.contractId 
		       AND D.statusId IN (v_enroll_status_approved, v_enroll_status_excused, v_enroll_status_enrolled, v_enroll_status_pre_enrolled) 
		     WHERE A.contractId = p_contractId
		       AND D.enrollId IS NULL
		       
		 UNION ALL
		 
		     --Busca por oferecimentos das optativas da matriz do aluno.
		    SELECT COUNT(DISTINCT D.curriculumId) AS pontos
		      FROM acdContract A
		INNER JOIN acdCurriculum B 
			ON (B.courseId,
			    B.courseVersion,
			    B.turnId,
			    B.unitId) = (A.courseId,
					 A.courseVersion,
					 A.turnId,
					 A.unitId)
		       AND B.semester = p_semester
		       AND B.curricularComponentTypeId = getparameter('ACADEMIC', 'CURRICULAR_COMPONENT_TYPE_ELECTIVE')::int --optativa
		       
		--Obtém os curriculumsIds das disciplinas optativas/eletivas disponíveis no curso do aluno.       
		INNER JOIN acdCurriculum C
			ON (C.courseId,
			    C.courseVersion,
			    C.turnId,
			    C.unitId) = (B.courseId,
					 B.courseVersion,
					 B.turnId,
					 B.unitId)
		       AND C.semester = 0 --eletivas/optativas
		       AND C.curricularComponentGroupDocumentEndCourseId = B.curricularComponentGroupElectiveId

		 --Obtém os curriculumsIds de todas disciplinas eletivas do sistema, se que sejam do mesmo turno/unidade/código da disciplina e versão do curso do aluno.
		INNER JOIN acdCurriculum D
			ON (D.curricularComponentId,
			    D.curricularComponentVersion,
			    D.turnId,
			    D.unitId) = (C.curricularComponentId,
					 C.curricularComponentVersion,
					 C.turnId,
					 C.unitId)
		       AND D.semester = 0 --eletivas/optativas
		       AND D.curricularComponentGroupDocumentEndCourseId = B.curricularComponentGroupElectiveId
		INNER JOIN acdGroup E
			ON E.curriculumId = D.curriculumId 
		       AND E.learningPeriodId IN (SELECT learningPeriodId
						    FROM acdLearningPeriod
						   WHERE periodId = (SELECT periodId
								       FROM acdLearningPeriod
								      WHERE learningPeriodId = p_learningPeriodId))
		 LEFT JOIN acdEnroll F
			ON F.curriculumId = C.curriculumId 
		       AND F.contractId = A.contractId 
		       AND F.statusId IN (v_enroll_status_approved, v_enroll_status_excused, v_enroll_status_enrolled, v_enroll_status_pre_enrolled)
		     WHERE A.contractId = p_contractId
		       AND F.enrollId IS NULL

		 UNION ALL

		    --Busca por oferecimentos dos vínculos de currículos da matriz do aluno.
		    SELECT COUNT(DISTINCT B.curriculumId) AS pontos
		      FROM acdContract A
		INNER JOIN acdCurriculum B 
			ON (B.courseId,
			    B.courseVersion,
			    B.turnId,
			    B.unitId) = (A.courseId,
					 A.courseVersion,
					 A.turnId,
					 A.unitId)
		       AND B.semester = p_semester
		INNER JOIN acdCurriculumLink C
		     USING (curriculumId)
		INNER JOIN acdGroup D
			ON D.curriculumId = C.curriculumLinkId 
		       AND D.learningPeriodId IN (SELECT learningPeriodId
						    FROM acdLearningPeriod
						   WHERE periodId = (SELECT periodId
								       FROM acdLearningPeriod
								      WHERE learningPeriodId = p_learningPeriodId))
		 LEFT JOIN acdEnroll E
			ON E.curriculumId = B.curriculumId 
		       AND E.contractId = A.contractId 
		       AND E.statusId IN (v_enroll_status_approved, v_enroll_status_excused, v_enroll_status_enrolled, v_enroll_status_pre_enrolled)
		     WHERE A.contractId = p_contractId
		       AND E.enrollId IS NULL) X)::INT;
END;
$BODY$
LANGUAGE plpgsql;
