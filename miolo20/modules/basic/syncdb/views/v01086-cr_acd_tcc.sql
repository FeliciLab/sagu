CREATE OR REPLACE VIEW cr_acd_tcc AS
	(SELECT DISTINCT A.*,
		B.finalexaminationtheme AS titulo_tcc,
		CASE WHEN G.useConcept
                             THEN ( SELECT COALESCE( O.concept, '')::TEXT FROM acdDegreeEnroll O INNER JOIN acdDegree P ON( O.degreeId = P.degreeId AND P.parentDegreeId IS NULL AND O.enrollId = B.enrollId) ORDER BY O.degreeEnrollId DESC LIMIT 1 )
                             ELSE ( SELECT ROUND( O.note::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::INT )::TEXT FROM acdDegreeEnroll O INNER JOIN acdDegree P ON( O.degreeId = P.degreeId AND P.parentDegreeId IS NULL AND O.enrollId = B.enrollId) ORDER BY O.degreeEnrollId DESC LIMIT 1 )
                END AS nota,
	        getPersonName(E.personId) AS orientador,
	        CASE F.posgraduacao
	            WHEN 2 THEN 'ESPECIALIZAÇÃO'
	            WHEN 3 THEN 'MESTRADO'
	            WHEN 4 THEN 'DOUTORADO'
	            WHEN 1 THEN ' - '
	            WHEN NULL THEN ' - '
	        END AS posgraduacao,
                TO_CHAR(NOW()::DATE, getParameter('BASIC', 'MASK_DATE')) AS data_hoje,
                A.data_hoje_extenco AS data_hoje_por_extenco,
                TO_CHAR(LP.beginDate, getParameter('BASIC', 'MASK_DATE')) AS data_inicial_tcc,
                dataPorExtenso(LP.beginDate) AS data_extenco_inicial_tcc,
                TO_CHAR(LP.endDate, getParameter('BASIC', 'MASK_DATE')) AS data_final_tcc,
                dataPorExtenso(LP.endDate) AS data_extenco_final_tcc,
                E.personId AS codigo_pessoa_orientador,
                G.groupId AS codigo_disciplina_oferecida,
                (SELECT TO_CHAR(MIN(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
		   FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
			   FROM acdSchedule Z
			  WHERE Z.groupId = G.groupId) X) AS data_inicio_disciplina_oferecida,
			  
		(SELECT TO_CHAR(MAX(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
		   FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
			   FROM acdSchedule Z
			  WHERE Z.groupId = G.groupId) X) AS data_fim_disciplina_oferecida,
                b.enrollid
	   FROM rptContrato A
     INNER JOIN acdEnroll B
             ON A.contractId = B.contractId
            AND B.statusId <> GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT
     INNER JOIN acdGroup G
             ON B.groupId = G.groupId
     INNER JOIN acdCurriculum D
             ON B.curriculumId = D.curriculumId
            AND D.curricularComponentTypeId = GETPARAMETER('ACADEMIC','ACD_CURRICULUM_TYPE_FINAL_EXAMINATION')::INT
     INNER JOIN acdLearningPeriod LP
             ON LP.learningPeriodId = G.learningPeriodId
      LEFT JOIN acdFinalExaminationDirectors E
             ON B.enrollid = E.enrollid
 LEFT JOIN ONLY basPhysicalPersonProfessor F
             ON E.personId = F.personId
	);