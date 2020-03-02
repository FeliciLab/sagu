CREATE OR REPLACE VIEW cr_spr_questionarioSocioEconomicoDoProcessoSeletivo AS
    ( SELECT X.selectiveProcessId,
         X.questionId,
         X.description AS question,
         X.answerTypeId,
         O.optionId,
         COALESCE(O.description, 'DESCRITIVA') AS option,
         ( SELECT DISTINCT COUNT(A.subscriptionId)
              FROM spr.subscription A
            INNER JOIN rshanswer B
             USING (personId)
             WHERE A.selectiveProcessId = X.selectiveProcessId
               AND B.questionId = O.questionId
               AND B.optionId = O.optionId ) AS quantResponderam,
         ( SELECT DISTINCT COUNT(A.subscriptionId)
             FROM spr.Subscription A
       INNER JOIN rshAnswer B
            USING (personId)
            WHERE A.selectiveProcessId = X.selectiveProcessId
              AND B.questionId = O.questionId
              AND B.optionId = O.optionId
              AND (CASE WHEN X.destinationModule = 2 --QUANTIDADE DE REPOSTAS DE ALUNOS MATRICULADOS ACADÊMICOS
                        THEN
                            A.personId IN (SELECT CO.personId
                                             FROM acdContract CO
                                       INNER JOIN acdLearningPeriod LP 
		                               ON (LP.courseId, 
			                           LP.courseversion, 
			                           LP.turnId, 
 			                           LP.unitId) = (CO.courseId, 
				                                 CO.courseversion, 
				                                 CO.turnId, 
				                                 CO.unitId)
				              AND LP.periodId = X.periodId
				       INNER JOIN acdEnroll EN
				               ON (CO.contractId = EN.contractId)
				       INNER JOIN acdGroup GP
				               ON (GP.groupId = EN.groupId)
				              AND (LP.learningPeriodId = GP.learningPeriodId)
                                            WHERE personId = A.personId)
                        WHEN X.destinationModule = 4 --QUANTIDADE DE REPOSTAS DE ALUNOS MATRICULADOS PEDAGÓGICO
                        THEN
			    A.personId IN (SELECT M.personId
			                     FROM acpMatricula M
			                    WHERE M.personId = A.personId
			                      AND M.situacao != 'C'
			                      AND M.dataMatricula BETWEEN X.periodoInicial AND X.periodoFinal )
                   END) ) AS quantResponderamMatriculados
    FROM ( SELECT SP.selectiveProcessId,
                  Q.questionId,
              Q.description,
              ANT.answerTypeId,
              SP.destinationModule,
              SP.periodId,
              SP.periodoInicial,
              SP.periodoFinal
         FROM rshForm F
       INNER JOIN rshQuestion Q
            USING (formId)
       INNER JOIN rshAnswerType ANT
            USING (answerTypeId)
       INNER JOIN spr.selectiveProcess SP
               ON SP.socialeconomicformid = F.formId ) X
   LEFT JOIN rshOption O
       USING (questionId) );
