CREATE OR REPLACE FUNCTION obterPercentualDeAprovacoes( p_contractId int, p_periodId varchar )
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterPercentualDeAprovacoes
  PURPOSE: Retorna um percentual(INT) de aprovações entre elas.
**************************************************************************************/
DECLARE
	v_enrolls record;
	v_count INT;
	v_aprovacoes INT;
BEGIN
	v_count := 0;
	
	FOR v_enrolls IN ( SELECT DISTINCT G.name,
				F.semester, 				
				(SELECT count(*) FROM acdcontract AA				    
					    INNER JOIN acdenroll DD
						    ON (AA.contractid = DD.contractid 
							AND DD.statusid::text = getparameter('ACADEMIC', 'ENROLL_STATUS_APPROVED'))
					    INNER JOIN acdgroup EE
						    ON DD.groupid = EE.groupid
					    INNER JOIN acdlearningperiod BB
						    ON BB.learningPeriodId = EE.learningPeriodId
					    INNER JOIN acdcurriculum FF      
						    ON EE.curriculumid = FF.curriculumid
														    
						 WHERE AA.contractid = p_contractId
						   AND BB.periodid = p_periodId
						   AND CASE WHEN getParameter('ACADEMIC', 'PERCENTUAL_DE_APROVACAO_DE_RENOVACAO_DE_CONTRATO') = '2'
							    THEN AA.contractid = DD.contractid ELSE (FF.semester = (SELECT MAX(CU.semester)
														   FROM acdEnroll EN
													     INNER JOIN acdGroup GR
														     ON GR.groupId = EN.groupId
													     INNER JOIN acdLearningPeriod LP
														     ON LP.learningPeriodId = GR.learningPeriodId
													     INNER JOIN acdCurriculum CU
														     ON ( CU.courseId,
															  CU.courseVersion,
															  CU.turnId,
															  CU.unitId ) = ( A.courseid,
																	  A.courseversion,
																	  A.turnid,
																	  A.unitid )
														    AND CU.curriculumId = GR.curriculumId
														  WHERE EN.contractId = A.contractid
														    AND LP.periodId = B.periodId)) END ) AS aprovado
			  FROM acdcontract A
		    INNER JOIN acdcurriculum F 
			    ON ( F.courseId,
				 F.courseVersion,
				 F.turnId,
				 F.unitId ) = ( A.courseid,
						A.courseversion,
						A.turnid,
						A.unitid )
		    INNER JOIN ONLY acdcurricularcomponent G
			         ON (G.curricularcomponentid,
			             G.curricularcomponentversion) = (F.curricularcomponentid,
								      F.curricularcomponentversion)
			      INNER JOIN acdenroll H
				      ON H.curriculumid = F.curriculumid
			      INNER JOIN acdgroup L
			              ON L.groupid = H.groupid 	
			      INNER JOIN acdlearningperiod B
				      ON B.learningPeriodId = L.learningPeriodId		      
			     			
			     WHERE A.contractid = p_contractId
			       AND B.periodId = p_periodId
			       AND CASE WHEN getParameter('ACADEMIC', 'PERCENTUAL_DE_APROVACAO_DE_RENOVACAO_DE_CONTRATO') = '2'
				        THEN (A.contractid = H.contractid AND H.statusId::TEXT != getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')) ELSE (F.semester = (SELECT MAX(CU.semester)
                                                                                                                                                                            FROM acdEnroll EN
                                                                                                                                                                        INNER JOIN acdGroup GR
                                                                                                                                                                                ON GR.groupId = EN.groupId
                                                                                                                                                                        INNER JOIN acdLearningPeriod LP
                                                                                                                                                                                ON LP.learningPeriodId = GR.learningPeriodId
                                                                                                                                                                        INNER JOIN acdCurriculum CU
                                                                                                                                                                                ON ( CU.courseId,
                                                                                                                                                                                    CU.courseVersion,
                                                                                                                                                                                    CU.turnId,
                                                                                                                                                                                    CU.unitId ) = ( A.courseid,
                                                                                                                                                                                                    A.courseversion,
                                                                                                                                                                                                    A.turnid,
                                                                                                                                                                                                    A.unitid )
                                                                                                                                                                                AND CU.curriculumId = GR.curriculumId
                                                                                                                                                                            WHERE EN.contractId = A.contractid
                                                                                                                                                                                AND LP.periodId = B.periodId)) END

			  GROUP BY G.name, F.semester, A.courseid, A.courseversion, A.turnid, A.unitid, A.contractid, B.periodid
			  ORDER BY G.name
 )
	LOOP
		v_count := v_count + 1;

		IF ( v_aprovacoes IS NULL )
		THEN
			v_aprovacoes := v_enrolls.aprovado;
		END IF;
	END LOOP;

	RETURN (v_aprovacoes * 100) / v_count;
END;
$BODY$
LANGUAGE 'plpgsql';
--
