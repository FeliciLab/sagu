CREATE OR REPLACE VIEW view_info_matricula AS (
	         SELECT B.personId,
	                getpersonname(B.personId) AS pessoa,
			A.enrollId,
			TO_CHAR(COALESCE(obterPercentualDeFrequenciaRealDoAluno(A.enrollId),0),'999') || '%' AS frequenciareal,
	                TO_CHAR(COALESCE(obterPercentualDeFrequenciaParcialDoAluno(A.enrollId),0),'999') || '%' AS frequenciaparcial,
	                TO_CHAR(COALESCE(obterPercentualDeFrequenciaPrevistaDoAluno(A.enrollId),0),'999') || '%' AS frequenciaprevista,
	                obternotaouconceitofinal(A.enrollid) AS notafinal,
                        A.statusId AS status_matricula,
	                A.groupid,
	                A.curriculumid,
	                D.name AS disciplina,
	                D.shortName AS abrev_disciplina,
	                C.curricularComponentId AS cod_disciplina, 
	                C.curricularComponentVersion AS cod_versao_disciplina,
	                D.academicnumberhours AS horas_academicas,
	                B.courseId,
	                B.courseVersion,
	                B.turnid,
	                B.unitId,
	                COALESCE(E.classId, '-') AS turma_atual,
	                COALESCE(F.classId, '-') AS turma_no_periodo,
	                COALESCE(H.classId, '-') AS turma_da_disciplina
	           FROM acdEnroll A
	     INNER JOIN acdContract B
	             ON A.contractId = B.contractId
	     INNER JOIN acdCurriculum C
	             ON A.curriculumId = C.curriculumId
	     INNER JOIN acdCurricularComponent D
	             ON (C.curricularComponentId, C.curricularComponentVersion) = (D.curricularComponentId, D.curricularComponentVersion)
	     INNER JOIN acdGroup H
	             ON A.groupId = H.groupId
	     INNER JOIN acdLearningPeriod I
	             ON H.learningPeriodId = I.learningPeriodId
	      LEFT JOIN acdClassPupil E
	             ON B.contractId = E.contractId
	            AND (E.endDate IS NULL OR E.endDate >= date(now()))
	      LEFT JOIN acdClassPupil F
	             ON B.contractId = F.contractId
	            AND (I.beginDate >= F.beginDate AND (I.endDate <= F.endDate OR F.endDate IS NULL))
	       ORDER BY D.name
	       );
