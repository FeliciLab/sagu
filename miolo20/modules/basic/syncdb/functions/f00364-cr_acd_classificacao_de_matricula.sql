CREATE OR REPLACE FUNCTION cr_acd_classificacao_de_matricula(p_groupId INT)
RETURNS SETOF ClassificacaoMatricula AS
$BODY$
/*************************************************************************************
  NAME: cr_acd_classificacao_de_matricula
  PURPOSE: Executa o processo de classificação de matrícula, retornando a listagem
           de alunos classificados e não classificados em uma disciplina oferecida,
           exibindo suas devidas posições.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       23/03/2015 Augusto A. Silva  1. Função criada.
**************************************************************************************/
BEGIN
    RETURN QUERY (
        SELECT (row_number() OVER (PARTITION BY 1))::TEXT || 'º' AS posicao,
	       Z.personId AS codigo_aluno,
	       Z.nome_aluno,
	       Z.codigo_curso_disciplina_oferecida,
	       Z.versao_curso_disciplina_oferecida,
	       Z.codigo_turno_disciplina_oferecida,
	       Z.turno_disciplina_oferecida,
	       Z.codigo_unidade_disciplina_oferecida,
	       Z.unidade_disciplina_oferecida,
	       Z.status AS status_atual_na_disciplina_oferecida,
	       Z.disciplinaDoCurso AS e_disciplina_do_curso,
	       Z.coeficienteDeClassificacao AS coeficiente_de_classificacao,
	       Z.dateenroll AS data_da_matricula_na_disciplina_oferecida,
	       Z.hourEnroll AS horario_da_matricula_na_disciplina_oferecida,
	       (CASE (CASE WHEN (row_number() OVER (PARTITION BY 1) > Z.vacant)
			   THEN
			        FALSE
			   ELSE
				TRUE
		      END)
		     WHEN TRUE
		     THEN
			  'CLASSIFICADO'
		     ELSE
		          'CANCELADO'
		END) AS status_previsto,
	       Z.contractId AS codigo_contrato,
	       (CASE WHEN (row_number() OVER (PARTITION BY 1) > Z.vacant)
		     THEN
			  FALSE
		     ELSE
			  TRUE
		END) AS classificado_na_disciplina_oferecida,
	       Z.vacant AS vagas_disciplina_oferecida,
	       Z.courseId AS codigo_curso_contrato,
	       Z.curso AS curso_contrato,
	       Z.courseVersion AS versao_curso_contrato,
	       Z.turnId AS codigo_turno_contrato,
	       Z.turno AS turno_contrato,
	       Z.unitId AS codigo_unidade_contrato,
	       Z.unidade AS unidade_contrato,
	       Z.groupId AS codigo_disciplina_oferecida,
	       Z.curricularComponentId AS codigo_disciplina,
	       Z.curricularComponentVersion AS versao_disciplina,
	       Z.name AS disciplina
	  FROM (SELECT E.personId,
		       E.name AS nome_aluno,
		       B.courseId AS codigo_curso_disciplina_oferecida,
		       B.courseVersion AS versao_curso_disciplina_oferecida,
		       B.turnId AS codigo_turno_disciplina_oferecida,
		       getturndescription(B.turnId) AS turno_disciplina_oferecida,
		       B.unitId AS codigo_unidade_disciplina_oferecida,
		       getunitdescription(B.unitId) AS unidade_disciplina_oferecida,
		       F.description AS status,
		       (SELECT COUNT(*) > 0
			   FROM acdCurriculum X
			  WHERE X.courseId = D.courseId
			    AND X.courseversion = D.courseVersion
			    AND X.turnId = D.turnId
			    AND X.unitId = D.unitId
			    AND (X.curricularComponentId, X.curricularComponentVersion) = (G.curricularComponentId, G.curricularComponentVersion)  
		       ) AS disciplinaDoCurso,
		       D.coeficienteDeClassificacao::NUMERIC,
		       C.dateenroll,
		       C.hourEnroll,
		       D.contractId,
		       A.vacant,
		       D.courseId,
		       getCourseName(D.courseId) AS curso,
		       D.courseVersion,
		       D.turnId,
		       getTurnDescription(D.turnId) AS turno,
		       D.unitId,
		       getUnitDescription(D.unitId) AS unidade,
		       F.statusId,
		       A.groupId,
		       CC.curricularComponentId,
		       CC.curricularComponentVersion,
		       CC.name
		  FROM acdGroup A
	    INNER JOIN acdLearningPeriod B
		    ON (A.learningPeriodId = B.learningPeriodId)
	    INNER JOIN acdEnroll C
		    ON (A.groupId = C.groupId)
	    INNER JOIN acdEnrollStatus F
		    ON (C.statusId = F.statusId)
	    INNER JOIN acdContract D
		    ON (C.contractId = D.contractId)
       INNER JOIN ONLY basphysicalpersonstudent E
		    ON (D.personId = E.personId)
	    INNER JOIN acdCurriculum G
		    ON (A.curriculumId = G.curriculumId)
            INNER JOIN acdCurricularComponent CC
                    ON (CC.curricularComponentId,
                        CC.curricularComponentVersion) = (G.curricularComponentId,
							  G.curricularComponentVersion)
		 WHERE A.groupId = p_groupId
		   AND (C.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INTEGER 
		    OR  C.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INTEGER)
	      ORDER BY status ASC, 
		       disciplinaDoCurso DESC, 
		       coeficienteDeClassificacao ASC, 
		       C.dateenroll ASC, 
		       C.hourEnroll ASC) Z
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
