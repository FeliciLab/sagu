CREATE OR REPLACE FUNCTION cr_acd_historico_escolar_matriz_curricular(p_contractId INT, p_historico_completo BOOLEAN DEFAULT TRUE)
RETURNS TABLE (
    codigo_contrato INT,
    codigo_curso VARCHAR,
    versao_curso INT,
    curso TEXT,
    codigo_curriculo_matriz INT,
    codigo_disciplina_matriz VARCHAR,
    versao_disciplina_matriz INT,
    nome_disciplina_matriz TEXT,
    codigo_curriculo_cursado INT,
    codigo_disciplina_cursada VARCHAR,
    versao_disciplina_cursada INT,
    nome_disciplina_cursada TEXT,
    disciplina_formatada TEXT,
    codigo_e_disciplina_formatada TEXT,
    semestre_disciplina_matriz INT,
    categoria_disciplina_matriz INT,
    periodo_disciplina_cursada VARCHAR,
    nota_final_disciplina_cursada VARCHAR,
    percentual_frequencia_disciplina_cursada NUMERIC,
    codigo_status_matricula INT,
    status_matricula TEXT,
    status_matricula_abreviado VARCHAR,
    status_matricula_abreviado_2 VARCHAR,
    carga_horaria_disciplina_matriz NUMERIC,
    creditos_academicos_disciplina_matriz NUMERIC,
    codigo_matricula INT,
    disciplina_da_matriz_do_curso_do_aluno BOOLEAN
) AS
$BODY$
BEGIN
    RETURN QUERY (
        --Disciplinas cursadas e não cursadas pelo aluno, do curriculo.
	SELECT CO.contractId AS codigo_contrato,
	       CO.courseId AS codigo_curso,
	       CO.courseVersion AS versao_curso,
	       COU.name AS curso,
	       CU.curriculumId AS codigo_curriculo_matriz,
	       CU.curricularComponentId AS codigo_disciplina_matriz,
	       CU.curricularComponentVersion AS versao_disciplina_matriz,
	       CC.name AS disciplina_matriz,
	       DOM.codigo_curriculo_oferecida AS codigo_curriculo_cursado,
	       DOM.codigo_disciplina AS codigo_disciplina_cursada,
	       DOM.versao_disciplina AS versao_disciplina_cursada,
	       DOM.nome_disciplina AS disciplina_cursada,
	       (CASE WHEN CC.name <> DOM.nome_disciplina
		     THEN CC.name || ' (' || DOM.nome_disciplina || ')'
		     ELSE CC.name
		END)::TEXT AS disciplina_formatada,
	       (CASE WHEN CC.curricularComponentId <> DOM.codigo_disciplina
		     THEN CC.curricularComponentId || ' - ' || CC.name || ' (' || DOM.codigo_disciplina || ' - ' || DOM.nome_disciplina || ')'
		     ELSE CC.curricularComponentId || ' - ' || CC.name
		END)::TEXT AS codigo_e_disciplina_formatada,
	       CU.semester AS semestre_disciplina_matriz,
	       CU.curricularComponentTypeId AS categoria_disciplina_matriz,
	       DOM.periodo AS periodo_disciplina_cursada,
	       DOM.nota_ou_conceito_final AS nota_final_disciplina_cursada,
	       DOM.frequencia_percentual::NUMERIC AS percentual_frequencia_disciplina_cursada,
	       DOM.codigo_status_aluno AS codigo_status_matricula,
	       DOM.status_aluno AS status_matricula,
	       DOM.status_matricula_abreviado,
               (CASE DOM.codigo_status_aluno
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT THEN 'MAT'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT THEN 'APR'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT 
		     THEN 
			  (CASE WHEN (SELECT COUNT(*) > 0
					FROM acdExploitation
				       WHERE enrollId = DOM.codigo_matricula
				         AND exploitationtype = 'I')
				THEN
				     'API'
				ELSE
				     'APV'
			   END)
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::INT THEN 'REP'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS')::INT THEN 'RPF'
                     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT THEN 'CAN'
		     ELSE DOM.status_matricula_abreviado
		END)::VARCHAR AS status_matricula_abreviado_2,
	       CC.academicNumberHours::NUMERIC AS carga_horaria_disciplina_matriz,
	       CC.academiccredits::NUMERIC AS creditos_academicos_disciplina_matriz,
	       DOM.codigo_matricula,
	       TRUE AS disciplina_da_matriz_do_curso_do_aluno
	  FROM acdContract CO
    INNER JOIN acdCourse COU
	    ON COU.courseId = CO.courseId
    INNER JOIN acdCurriculum CU
	    ON (CU.courseId,
		CU.courseVersion,
		CU.turnId,
		CU.unitId) = (CO.courseId,
			      CO.courseVersion,
			      CO.turnId,
			      CO.unitId)
    INNER JOIN acdCurricularComponent CC
	    ON (CC.curricularComponentId,
		CC.curricularComponentVersion) = (CU.curricularComponentId,
						  CU.curricularComponentVersion)  
     LEFT JOIN cr_acd_disciplina_oferecida_matricula(NULL, p_contractId) DOM
	    ON DOM.codigo_curriculo = CU.curriculumId
	 WHERE CO.contractId = p_contractId
	   AND (CASE WHEN p_historico_completo
		     THEN
		          TRUE
		     ELSE
			  DOM.codigo_disciplina IS NOT NULL
		END)
	 
     UNION ALL
     
     --Disciplinas cursadas pelo aluno, fora de curriculo.
	SELECT CO.contractId AS codigo_contrato,
	       CO.courseId AS codigo_curso,
	       CO.courseVersion AS versao_curso,
	       COU.name AS curso,
	       DOM.codigo_curriculo AS codigo_curriculo_matriz,
	       DOM.codigo_disciplina_matriz,
	       DOM.versao_disciplina_matriz,
	       DOM.nome_disciplina_matriz,
	       DOM.codigo_curriculo_oferecida AS codigo_curriculo_cursado,
	       DOM.codigo_disciplina AS codigo_disciplina_cursada,
	       DOM.versao_disciplina AS versao_disciplina_cursada,
	       DOM.nome_disciplina AS nome_disciplina_cursada,
	       (CASE WHEN DOM.nome_disciplina_matriz <> DOM.nome_disciplina
		     THEN DOM.nome_disciplina_matriz || ' (' || DOM.nome_disciplina || ')'
		     ELSE DOM.nome_disciplina_matriz
		END)::TEXT AS disciplina_formatada,
	       (CASE WHEN DOM.codigo_disciplina_matriz <> DOM.codigo_disciplina
		     THEN DOM.codigo_disciplina_matriz || ' - ' || DOM.nome_disciplina_matriz || ' (' || DOM.codigo_disciplina || ' - ' || DOM.nome_disciplina || ')'
		     ELSE DOM.codigo_disciplina_matriz || ' - ' || DOM.nome_disciplina_matriz
		END)::TEXT AS codigo_e_disciplina_formatada,
	       DOM.semestre_disciplina AS semestre_disciplina_matriz,
	       DOM.codigo_tipo_disciplina_matriz AS categoria_disciplina_matriz,
	       DOM.periodo AS periodo_disciplina_cursada,
	       DOM.nota_ou_conceito_final AS nota_final_disciplina_cursada,
	       DOM.frequencia_percentual::NUMERIC AS percentual_frequencia_disciplina_cursada,
	       DOM.codigo_status_aluno AS codigo_status_matricula,
	       DOM.status_aluno AS status_matricula,
	       DOM.status_matricula_abreviado,
               (CASE DOM.codigo_status_aluno
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT THEN 'MAT'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT THEN 'APR'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT 
		     THEN 
			  (CASE WHEN (SELECT COUNT(*) > 0
					FROM acdExploitation
				       WHERE enrollId = DOM.codigo_matricula
				         AND exploitationtype = 'I')
				THEN
				     'API'
				ELSE
				     'APV'
			   END)
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::INT THEN 'REP'
		     WHEN getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS')::INT THEN 'RPF'
		     ELSE DOM.status_matricula_abreviado
		END)::VARCHAR AS status_matricula_abreviado_2,
	       DOM.carga_horaria_disciplina_matriz::NUMERIC,
	       DOM.creditos_disciplina_matriz::NUMERIC AS creditos_academicos_disciplina_matriz,
	       DOM.codigo_matricula,
	       FALSE AS disciplina_da_matriz_do_curso_do_aluno
	  FROM cr_acd_disciplina_oferecida_matricula(NULL, p_contractId) DOM
    INNER JOIN acdContract CO	
	    ON CO.contractId = DOM.codigo_contrato
    INNER JOIN acdCourse COU
	    ON COU.courseId = CO.courseId
     LEFT JOIN acdCurriculum CU
	    ON (CU.courseId,
		CU.courseVersion,
		CU.turnId,
		CU.unitId) = (CO.courseId,
			      CO.courseVersion,
			      CO.turnId,
			      CO.unitId)
	   AND CU.curriculumId = DOM.codigo_curriculo
	 WHERE CO.contractId = p_contractId
	   AND CU.curriculumId IS NULL
    );
END;
$BODY$
LANGUAGE plpgsql;
