CREATE OR REPLACE VIEW rptDisciplinaAluno AS (
        (SELECT D.groupid,
                D.curriculumid,
                D.classid,
                D.disciplina,
                D.disciplinacod,
                D.disciplinacomcod,
                D.profresponsavel,
                D.profpersonid,
                D.professorresponsible,
                D.cargahoraria,
                D.creditos,
                D.curricularcomponentid,
                D.periodid,
                D.courseandversion,
                D.turn,
                D.unit,
                D.learningperiodid,
                D.learningperiod,
                D.datahoje,
                D.courseid,
                D.coursename,
                D.horasaula,
                E.contractid,
                C.personid,
                E.enrollid,
                E.statusid AS enrollstatusid,
                obternotaouconceitofinal(E.enrollid) AS nota,
                (E.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int) AS aprovado,
                (E.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::int) AS reprovado,
                (E.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::int) AS cursando,
                (E.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int) AS dispensado,
                D.courseversion,
                D.turnid,
                D.unitid,
                D.curricularcomponentversion,
                ES.description AS enroll_status,
                E.finalnote,
                D.isclosed,
                D.codigo_tipo_de_disciplina,
                D.tipo_de_disciplina,
                D.e_disciplina_de_tcc,
                ROUND((COALESCE(obterPercentualDeFrequenciaRealDoAluno(E.enrollid)::NUMERIC, E.frequency::NUMERIC)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)::VARCHAR AS frequencia_percentual,
                getClassName(D.classid) AS nome_turma,
                semetre_disciplina,
                obterStatusDeMatriculaAbreviado(E.statusId) AS status_abreviado,
                CU.curriculumId AS curriculumId_disciplina_matriz_aluno,
                CU.semester AS semestre_disciplina_matriz_aluno,
                CU.curricularcomponentid AS codigo_disciplina_matriz_aluno,
                CC.name AS nome_disciplina_matriz_aluno,
                D.datas_de_avaliacao,
                ES.abbreviation,
                CC.curricularComponentVersion AS versao_disciplina_matriz_aluno,
                CU.curricularComponentTypeId AS codigo_tipo_disciplina_matriz_aluno,
                CC.academicNumberHours AS carga_horaria_disciplina_matriz_aluno,
                CC.academicCredits AS creditos_disciplina_matriz_aluno
           FROM rptDisciplina D
     INNER JOIN acdEnroll E ON E.groupid = D.groupid
     INNER JOIN acdContract C ON C.contractid = E.contractid
     INNER JOIN acdEnrollStatus ES
             ON ES.statusId = E.statusId
     INNER JOIN acdCurriculum CU
             ON CU.curriculumId = E.curriculumId
     INNER JOIN acdCurricularComponent CC
             ON (CC.curricularComponentId,
                 CC.curricularComponentVersion) = (CU.curricularComponentId,
                                                   CU.curricularComponentVersion))
          UNION ( SELECT NULL,
                         NULL,
                         NULL,
                         D.name AS disciplina,
                         D.curricularcomponentid AS cod_disciplina,
                         D.curricularcomponentid || ' - ' || D.name AS disciplinacomcod,
                         NULL,
                         NULL,
                         NULL,
                         D.academicnumberhours AS carga_horaria,
			 D.academiccredits AS creditos,
			 D.curricularcomponentid,
			 E.periodid,
			 E.courseid || ' - ' || E.courseversion AS courseandversion,
			 NULL,
			 NULL,
			 E.learningperiodid,
			 E.description,
			 datetouser(now()::date) AS datahoje,
			 C.courseid,
			 NULL,
			 D.academicnumberhours AS horasaula,
			 A.contractid,
			 F.personid,
			 A.enrollid,
			 A.statusid,
			 obternotaouconceitofinal(A.enrollid) AS nota,
			 (A.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPROVED')::int) AS aprovado,
			 (A.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED')::int) AS reprovado,
			 (A.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::int) AS cursando,
			 (A.statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int) AS dispensado,
                         C.courseversion,
                         C.turnid,
                         C.unitid,
                         C.curricularcomponentversion,
                         ES.description AS enroll_status,
                         A.finalnote,
                         NULL,
                         C.curricularComponentTypeId AS codigo_tipo_de_disciplina,
                         CCT.description AS tipo_de_disciplina,
                         (CASE C.curricularComponentTypeId
                               WHEN getParameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_FINAL_EXAMINATION')::INT
                               THEN
                                    TRUE
                               ELSE
                                    FALSE
                          END) AS e_disciplina_de_tcc,
                         ROUND((COALESCE(obterPercentualDeFrequenciaRealDoAluno(A.enrollid)::NUMERIC, A.frequency::NUMERIC)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)::VARCHAR AS frequencia_percentual,
                         NULL::VARCHAR AS nome_turma,
                         C.semester AS semetre_disciplina,
                         obterStatusDeMatriculaAbreviado(A.statusId) AS status_abreviado,
                         C.curriculumId AS curriculumId_disciplina_matriz_aluno,
                         C.semester AS semestre_disciplina_matriz_aluno,
                         D.curricularcomponentid AS codigo_disciplina_matriz_aluno,
                         D.name AS nome_disciplina_matriz_aluno,
                         NULL as datas_de_avaliacao,
                         ES.abbreviation,
                         D.curricularComponentVersion AS versao_disciplina_matriz_aluno,
                         C.curricularComponentTypeId AS codigo_tipo_disciplina_matriz_aluno,
                         D.academicNumberHours AS carga_horaria_disciplina_matriz_aluno,
                         D.academicCredits AS creditos_disciplina_matriz_aluno
		    FROM acdenroll A
	      INNER JOIN acdexploitation B
		      ON A.enrollid = B.enrollid
	      INNER JOIN acdcurriculum C
		      ON A.curriculumid = C.curriculumid
	      INNER JOIN acdcurricularcomponent D
		      ON C.curricularcomponentid = D.curricularcomponentid
		     AND C.curricularcomponentversion = D.curricularcomponentversion
	      INNER JOIN acdlearningperiod E
	              ON A.learningperiodid = E.learningperiodid
	      INNER JOIN acdcontract F
	              ON A.contractid = F.contractid
              INNER JOIN acdEnrollStatus ES
                      ON ES.statusId = A.statusId 
              INNER JOIN acdCurricularComponentType CCT
                      ON CCT.curricularComponentTypeId = C.curricularComponentTypeId)
);
