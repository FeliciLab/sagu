CREATE OR REPLACE VIEW cr_acd_rptcontratosomente AS
            (SELECT *, 
                   ((ch_cursada/ch_requerida_curso)*100) AS percentualcursado,
                   obterMediaGlobal(XX.contractId) AS media_global_aprovacoes_e_dispensas,
                   obterMediaGlobal(XX.contractId, true) AS media_global_aprovacoes_dispensas_e_reprovacoes
              FROM (SELECT C.contractId,
                    T.description AS turn,
                    U.description AS unit,
                    COALESCE(CI.name, '-') AS unitcity,
                    CO.name AS course,
                    C.subscriptionid,
                    COALESCE(C.obs, '-') AS obs,
                    CO.name AS coursename,
                    COALESCE( ( SELECT datetouser(min(statetime::date)) FROM acdmovementcontract WHERE contractid = C.contractId and statecontractid = 4 LIMIT 1), '-') AS dtadmissao,
                    COALESCE(datetouser(C.formationdate), '-') AS formationdate,
                    COALESCE(datetouser(C.diplomadate), '-') AS diplomadate,
                    COALESCE(datetouser(C.conclusionDate), '-') AS conclusionDate,
                    COALESCE(RC.documentoreconhecimento, '-') AS portariavigente,
                    COALESCE(datetouser(RC.datareconhecimento), '-') AS portariadatareconhecimento,
                    COALESCE(CO.degree, '-') AS coursedegree,
                    COALESCE(get_semester_contract(C.contractId)::varchar, '-') AS semestre,
                    COALESCE(getContractClassId(C.contractId), '-') AS classId,
                    COALESCE(PP.name, '-') AS coordinatorName,
                    PP.personId AS coordinatorPersonId,
                    CO.courseId,
                    RC.datareconhecimento,
                    C.formationDate AS formationDateDb,
                    COALESCE(( SELECT datetouser(statetime::date) FROM acdmovementcontract where contractid = C.contractid AND statecontractid = 11 LIMIT 1 ), '-') AS datacolacao,
                    CI.name AS unitcityname,
                    SPR.description AS ingresso_procsel,
                    DATETOUSER(SPR.endofprocessdate::date) AS procsel_concluido_em,
                    (SELECT SO.position FROM spr.subscriptionOption SO WHERE SO.subscriptionId = S.subscriptionId AND SO.position IS NOT NULL LIMIT 1) AS procsel_classif_curso,
                    (SELECT SSI.position FROM spr.subscriptionStepInfo SSI WHERE SSI.subscriptionId = S.subscriptionId LIMIT 1) AS procsel_classif_geral,
                    (SELECT SSI.totalPoints FROM spr.subscriptionStepInfo SSI WHERE SSI.subscriptionId = S.subscriptionId LIMIT 1) AS nota_vestibular,
                    CI.stateid AS estado,
                    C.courseversion,
                    C.turnid,
                    C.unitid,
                    C.formationperiodid,
                    (SELECT stateContractId FROM acdMovementContract LEFT JOIN acdLearningPeriod USING (learningPeriodId) WHERE contractId = C.contractid ORDER BY stateTime DESC LIMIT 1) as cod_ultima_movimentacao,
		    (SELECT acdstatecontract.description FROM acdMovementContract LEFT JOIN acdLearningPeriod USING (learningPeriodId) INNER JOIN acdstatecontract USING(statecontractid) WHERE contractId = C.contractid ORDER BY stateTime DESC LIMIT 1) as descricao_ultima_movimentacao,
		    (SELECT statetime FROM acdMovementContract WHERE contractId = C.contractid ORDER BY stateTime DESC LIMIT 1) as data_ultima_movimentacao,
		    (SELECT datetouser(beginDate) FROM acdLearningPeriod WHERE learningPeriodId IN (SELECT initiallearningperiodid FROM acdClassPupil AA INNER JOIN acdClass USING(classId) WHERE contractId = C.contractId LIMIT 1)) as data_inicial_da_turma,
		    (SELECT X.data_usuario 
                      FROM (SELECT DISTINCT datetouser((SELECT MAX(x) FROM UNNEST(occurrencedates) x)) as data_usuario,
		                  (SELECT MAX(x) FROM UNNEST(occurrencedates) x) as data_ordenacao
                              FROM acdschedule 
                        INNER JOIN acdGroup
                             USING (groupId)
                        INNER JOIN acdClassPupil
                             USING (classId)
                             WHERE contractId = C.contractId
		          ORDER BY 2 DESC
                             LIMIT 1) X) as data_final_da_turma,
                    (SELECT LPI.periodId
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId) MCI
                 INNER JOIN acdLearningPeriod LPI
                         ON LPI.learningPeriodId = MCI.learningPeriodId) AS periodo_de_ingresso,
                    (SELECT SUM(CC.academicnumberhours) 
                       FROM acdEnroll AA
                 INNER JOIN acdCurriculum BB 
                         ON (AA.curriculumid = BB.curriculumid)
                 INNER JOIN acdCurricularComponent CC 
                         ON (BB.curricularComponentId, BB.curricularComponentVersion) = (CC.curricularComponentId, CC.curricularComponentVersion) 
                      WHERE AA.contractid = C.contractId 
                        AND AA.statusId IN (GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
                        AND NOT EXISTS (SELECT 1 FROM acdcomplementaryactivities WHERE enrollid = AA.enrollid)
			AND NOT EXISTS(SELECT 1 FROM acdexploitation WHERE exploitationenrollid = AA.enrollid)) AS ch_cursada,
                    CV.hourtotal AS ch_total_curso, 
                    CV.hourrequired AS ch_requerida_curso,
                    FL.description AS grau_de_formacao,
                    SPR.periodId AS procsel_periodo,
                    (SELECT acdLearningPeriod.learningPeriodId 
                       FROM acdMovementContract 
                  LEFT JOIN acdLearningPeriod 
                      USING (learningPeriodId) 
                      WHERE contractId = C.contractid 
                   ORDER BY stateTime 
                 DESC LIMIT 1) as codigo_periodo_letivo_ultima_movimentacao,
                    (SELECT acdLearningPeriod.description
                       FROM acdMovementContract 
                  LEFT JOIN acdLearningPeriod 
                      USING (learningPeriodId) 
                      WHERE contractId = C.contractid 
                   ORDER BY stateTime 
                 DESC LIMIT 1) as periodo_letivo_ultima_movimentacao,
                    dataporextenso(now()::date) as data_hoje_extenco,
                    (SELECT SUM(CC.academiccredits) 
                       FROM acdEnroll AA
                 INNER JOIN acdCurriculum BB 
                         ON (AA.curriculumid = BB.curriculumid)
                 INNER JOIN acdCurricularComponent CC 
                         ON (BB.curricularComponentId, BB.curricularComponentVersion) = (CC.curricularComponentId, CC.curricularComponentVersion) 
                      WHERE AA.contractid = C.contractId 
                        AND AA.statusId IN (GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
                        AND NOT EXISTS (SELECT 1 FROM acdcomplementaryactivities WHERE enrollid = AA.enrollid)
			AND NOT EXISTS(SELECT 1 FROM acdexploitation WHERE exploitationenrollid = AA.enrollid)) AS creditos_cursados,
                    (CASE WHEN (SELECT COUNT(*) > 0
				  FROM finIncentive
				 WHERE contractId = C.contractId
				   AND endDate >= NOW()::DATE)
			  THEN
			       'SIM'
			  ELSE
                               'NÃO'
		     END) AS possui_beneficios_financeiros,
                    (SELECT description
                       FROM obterMovimentacaoContratualDeIngressoDoAluno(C.contractId)) AS forma_de_ingresso,
                    (SELECT name
                       FROM acdClass
                      WHERE classId = getContractClassId(C.contractId)) AS nome_turma,
                    (SELECT quantidade_de_semestres
                       FROM obterSemestreDoContratoPelasMovimentacoesDeMatricula(C.contractId)) AS quantidade_semestres_cursados,
                    isContractClosed(C.contractId) AS contrato_esta_fechado,
                    (SELECT MDADA.mensagem
                       FROM acdTestEndCourseContract TECC
                 INNER JOIN acdMensagemDeAvaliacaoDosAlunos MDADA
                         ON MDADA.mensagemDeAvaliacaoDosAlunosId = TECC.mensagemDeAvaliacaoDosAlunosId
                      WHERE TECC.contractId = C.contractId
                        AND TECC.testEndCourseTypeId = getParameter('ACADEMIC', 'TIPO_AVALIACAO_MEC_ENADE')::INT
                   ORDER BY TECC.testEndCourseDate DESC
                      LIMIT 1) AS mensagem_avaliacao_mec_enade,
                      C.personid as codigo_da_pessoa
               FROM acdContract C
         INNER JOIN basTurn T
                 ON T.turnId = C.turnId
         INNER JOIN basUnit U
                 ON U.unitId = C.unitId
          LEFT JOIN basLocation L
                 ON L.locationId = U.locationId
          LEFT JOIN basCity CI
                 ON CI.cityId = L.cityId
         INNER JOIN acdCourseVersion CV
                 ON CV.courseid = C.courseid
                AND CV.courseversion = C.courseversion
         INNER JOIN acdCourse CO
                 ON C.courseid = CO.courseid
          LEFT JOIN acdreconhecimentodecurso RC
                 ON RC.reconhecimentodecursoid = COALESCE ( ( SELECT reconhecimentodecursoid
                                                                FROM acdreconhecimentodecurso
                                                               WHERE courseid = C.courseId
								 AND courseversion = C.courseversion
								 AND turnid = C.turnid
								 AND unitid = C.unitid
                                                                 AND ( SELECT statetime::DATE
									 FROM acdmovementcontract
									WHERE contractid = C.contractid
									  AND statecontractid = 11
									LIMIT 1 ) BETWEEN datainicial AND datafinal
                                                            ORDER BY datareconhecimento
                                                               LIMIT 1 ),  -- Colação de grau

                                                            ( SELECT reconhecimentodecursoid
                                                                FROM acdreconhecimentodecurso
                                                               WHERE courseid = C.courseId
								 AND courseversion = C.courseversion
								 AND turnid = C.turnid
								 AND unitid = C.unitid
								 AND NOW()::DATE BETWEEN datainicial AND datafinal
                                                            ORDER BY datareconhecimento
                                                               LIMIT 1 ) ) -- Para alunos não formados.
         INNER JOIN acdLearningPeriod LP
                    ON LP.learningPeriodId = (SELECT learningPeriodId
                                                FROM acdLearningPeriod
                                                WHERE courseId = C.courseId
                                                AND courseVersion = C.courseVersion
                                                AND turnId = C.turnId
                                                AND unitId = C.unitId
                                                ORDER BY beginDate DESC
                                                LIMIT 1)
          LEFT JOIN acdCourseCoordinator CC
                 ON CC.courseId = C.courseId
                AND CC.courseVersion = C.courseVersion
                AND CC.turnId = C.turnId
                AND CC.unitId = C.unitId
                AND ((cc.begindate is null OR cc.begindate <= now()::date) AND (cc.enddate is null OR cc.enddate >= now()::date) )
     LEFT JOIN ONLY basPhysicalPersonProfessor PP
                 ON PP.personId = CC.coordinatorId
          LEFT JOIN spr.subscription S
                 ON S.subscriptionid = C.subscriptionid
          LEFT JOIN spr.selectiveProcess SPR
                 ON SPR.selectiveprocessid = S.selectiveprocessid
         INNER JOIN acdformationlevel FL
                 ON CO.formationlevelid = FL.formationlevelid
) AS XX);