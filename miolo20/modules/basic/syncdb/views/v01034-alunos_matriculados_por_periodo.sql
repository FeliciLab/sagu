CREATE OR REPLACE VIEW alunos_matriculados_por_periodo AS (
		SELECT A.contractId as contrato,
		       X.description as unidade,
		       X.unitId,
		       A.courseId,
		       E.name as curso,
		       A.courseVersion,
		       F.description as turno,
		       F.turnId,
		       D.personid as codigo_pessoa,
		       D.name as nome,
		       d.email,
		       d.residentialphone as telefone_residencial,
		       d.workphone as telefone_trabalho,
		       CASE
			   WHEN L.contractId IS NOT NULL THEN 'Não renovados'
			   WHEN M.contractId IS NOT NULL THEN 'Cancelamentos'
			   WHEN K.contractId IS NOT NULL THEN 'Trancamentos'
			   WHEN J.contractId IS NOT NULL THEN 'Tranferidos (S)'
			   WHEN I.contractId IS NOT NULL THEN 'Tranferidos (E)'
			   WHEN H.contractId IS NOT NULL THEN 'Reingressos'
			   WHEN C.contractId IS NOT NULL THEN 'Renovados'
			   WHEN N.contractId IS NOT NULL THEN 'Pré-matriculados'
			   WHEN B.contractId IS NOT NULL THEN 'Matriculados'
		       END as status,
		       G.periodid
		  FROM acdContract A
       INNER JOIN ONLY basPhysicalPerson D
	            ON D.personid = A.personid
	    INNER JOIN acdCourse E
		    ON (E.courseId = A.courseId)
	    INNER JOIN basTurn F
		    ON (F.turnId = A.turnId)
	    INNER JOIN acdLearningPeriod G
		    ON (G.courseId = A.courseId
		   AND G.courseVersion = A.courseVersion
		   AND G.turnId = A.turnId
		   AND G.unitId = A.unitId)
	     LEFT JOIN basUnit X
		    ON (A.unitid = X.unitid)

	     LEFT JOIN acdContract B --Matriculados
		    ON ( A.contractid = B.contractId
		   AND ( SELECT COUNT(SSM.*) <= 1
			   FROM ( SELECT SM.learningperiodid
				    FROM acdMovementContract SM
				    --Pode ter mais de um contrato devido a uma troca de turno ou versão
				   WHERE SM.contractid IN (SELECT contractId FROM acdContract WHERE personId = A.personId AND courseId = A.courseId)
				     AND SM.stateContractId != GETPARAMETER('BASIC','WRITING_STATE_CONTRACT')::INTEGER
	             		      --Caso tenha mais de 1 contrato deve possuir movimentações de TRANSFERENCIA INTERNA DE e TRANSFERENCIA INTERNA PARA indicando que houve uma transferencia
				     AND (CASE WHEN (SELECT count(*) > 1 FROM acdContract WHERE personId = A.personId AND courseId = A.courseId)
			       		      THEN
					          SM.statecontractid = GETPARAMETER('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INTEGER --Matricula
					      ELSE
						  TRUE
					  END)
				GROUP BY SM.learningPeriodId) AS SSM )
	           AND isacademicenrolledinperiod(A.contractid, G.periodid)
		   AND isfinanceenrolledinperiod(A.contractid, G.periodid))


	     LEFT JOIN acdContract N --Pré-Matriculados
		    ON (( A.contractId = N.contractId)
	           AND getcontractstatebyperiod(A.contractId, G.periodid) = GETPARAMETER('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INTEGER)

	     LEFT JOIN acdContract C --Renovacoes
		    ON ( A.contractid = C.contractId
		   AND ( SELECT COUNT(SSR.*) > 1
			   FROM ( SELECT SR.learningperiodid
				    FROM acdMovementContract SR
				   WHERE SR.contractid=A.contractId
				     AND SR.stateContractId != GETPARAMETER('BASIC','WRITING_STATE_CONTRACT')::INTEGER
				GROUP BY SR.learningPeriodId) AS SSR )
		   AND isacademicenrolledinperiod(A.contractid, G.periodid)
		   AND isfinanceenrolledinperiod(A.contractid, G.periodid))

	     LEFT JOIN acdContract H --Reingresso
	            ON ( A.contractid = H.contractId
	           AND ( SELECT COUNT(SSI.*) >= 1
		            FROM (SELECT SI.learningperiodid
			            FROM acdMovementContract SI
			           WHERE SI.contractid=A.contractId
				     AND SI.stateContractId = GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_UNLOCKED')::INTEGER
				     AND ( SI.statetime BETWEEN G.beginDate AND G.endDate OR
				           SI.learningperiodid = G.learningperiodid )
			        GROUP BY SI.learningPeriodId) AS SSI )
	           AND isacademicenrolledinperiod(A.contractid, G.periodid)
	           AND isfinanceenrolledinperiod(A.contractid, G.periodid))

	     LEFT JOIN acdContract I --Transferidos (E)
	            ON ( A.contractid = I.contractId
	           AND ( SELECT COUNT(SSE.*) >= 1
		           FROM (SELECT SE.learningperiodid
			           FROM acdMovementContract SE
			          WHERE SE.contractid=A.contractId
				    AND SE.stateContractId = GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_EXTERNAL_TRANSFER_FROM')::INTEGER --Transferencia externa de
				    AND ( SE.statetime BETWEEN G.beginDate AND G.endDate OR
				          SE.learningperiodid = G.learningperiodid )
			       GROUP BY SE.learningPeriodId) AS SSE )
	           AND isacademicenrolledinperiod(A.contractid, G.periodid)
	           AND isfinanceenrolledinperiod(A.contractid, G.periodid))

	     LEFT JOIN acdContract J --Transferidos (S)
	            ON ( A.contractid = J.contractId AND getcontractstatebyperiod(J.contractid, G.periodid) = GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_EXTERNAL_TRANSFER_TO')::INTEGER)

	     LEFT JOIN acdContract K --Trancamentos
	            ON (A.contractid = K.contractId AND getcontractstatebyperiod(K.contractid, G.periodid) = GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_LOCKED')::INTEGER) --

	     LEFT JOIN acdContract M --Cancelamentos
	            ON ( A.contractId = M.contractId
		   AND getcontractstatebyperiod(M.contractid, G.periodid) = GETPARAMETER('ACADEMIC','STATE_CONTRACT_ID_LOCKED')::INTEGER
		   AND (SELECT COUNT(SSM.*) > 0
		          FROM ( SELECT SM.learningperiodid
			           FROM acdMovementContract SM
			          WHERE SM.contractid=A.contractId
			            AND SM.stateContractId = GETPARAMETER('BASIC','WRITING_STATE_CONTRACT')::INTEGER
			            AND (SM.learningperiodid = G.learningPeriodId OR
				         SM.stateTime BETWEEN G.beginDate AND G.endDate)
			       GROUP BY SM.learningPeriodId) AS SSM))

		LEFT JOIN acdContract L --Não renovados
		       ON ( A.contractid = L.contractId
			    AND getcontractstatebyperiod(L.contractId, G.periodid) IS NULL
			    AND (isfinanceenrolledinperiod(L.contractId, G.periodid) IS FALSE OR
				 isacademicenrolledinperiod(L.contractId, G.periodid) IS FALSE)
			    AND isacademicenrolledinperiod(A.contractId, (SELECT DISTINCT periodid FROM acdlearningperiod WHERE periodid < G.periodid ORDER BY periodid DESC LIMIT 1)) IS TRUE --Esteja matriculado no periodo anterior
			    AND getcontractstate(A.contractId) IN (SELECT statecontractid FROM acdstatecontract WHERE isclosecontract IS FALSE ))



		 WHERE (B.contractId IS NOT NULL OR C.contractId IS NOT NULL OR H.contractId IS NOT NULL OR I.contractId IS NOT NULL OR J.contractId IS NOT NULL OR K.contractId IS NOT NULL
		    OR L.contractId IS NOT NULL OR M.contractId IS NOT NULL OR N.contractId IS NOT NULL)
	      GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
              ORDER BY x.description, E.name, F.description, status, d.name );
