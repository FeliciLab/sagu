CREATE OR REPLACE VIEW rptDisciplina AS
            (SELECT g.groupid,
                    g.curriculumid,
                    g.classid,
                    cc.name AS disciplina,
                    cu.curricularcomponentid AS disciplinacod,
                    cu.curricularcomponentid || ' - ' || cc.name AS disciplinacomcod,
                    (CASE WHEN prof.personid is not null THEN prof.name ELSE '-' END) AS profresponsavel,
                    prof.personid AS profpersonid,
                    g.professorresponsible,
                    cc.academicnumberhours AS cargahoraria,
                    cc.academiccredits AS creditos,
                    cc.curricularcomponentid,
                    lp.periodid,
                    lp.courseid || ' - ' || lp.courseversion AS courseandversion,
                    t.description AS turn,
                    u.description AS unit,
                    lp.learningperiodid,
                    lp.description AS learningperiod,
                    datetouser(now()::date) AS datahoje,
                    co.courseid,
                    co.name AS coursename,
                    academicnumberhours AS horasaula,
                    TO_CHAR((SELECT MIN(min_date) FROM UNNEST(S.occurrenceDates) min_date), getParameter('BASIC', 'MASK_DATE')) AS dataInicalOferecida,
                    TO_CHAR((SELECT MAX(max_date) FROM UNNEST(S.occurrenceDates) max_date),getParameter('BASIC', 'MASK_DATE')) AS dataFinalOferecida,
                    COC.coordinatorId AS codigoDoCoordenador,
                    getPersonName(COC.coordinatorId) AS nomeDoCoordenador,
                    t.turnid,
                    u.unitid,
                    g.regimenid,
                    r.description AS regime,
                    (CASE g.regimenid 
                          WHEN getParameter('ACADEMIC', 'REGIME_DE_FERIAS')::INT 
                          THEN 
                               TRUE
                          ELSE 
                               FALSE 
                     END) AS curso_de_ferias,
                    lp.courseversion,
                    cc.curricularcomponentversion,
                    g.iscancellation,
                    g.isclosed,
                    cu.curricularComponentTypeId AS codigo_tipo_de_disciplina,
                    CCT.description AS tipo_de_disciplina,
                    (CASE cu.curricularComponentTypeId
                          WHEN getParameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_FINAL_EXAMINATION')::INT
                          THEN
                               TRUE
                          ELSE
                               FALSE
                     END) AS e_disciplina_de_tcc,
                    cu.semester AS semetre_disciplina,
                    obterDatasDeProvaDaDisciplinaOferecida(G.groupId) AS datas_de_avaliacao,
                    g.vacant AS vagas
               from acdGroup g
         inner join acdlearningperiod lp
                 on lp.learningperiodid = g.learningperiodid
         inner join acdcurriculum cu
                 on g.curriculumid = cu.curriculumid
         inner join acdcurricularcomponent cc
                 on cu.curricularcomponentid = cc.curricularcomponentid
                and cu.curricularcomponentversion = cc.curricularcomponentversion
         inner join basTurn t
                 on t.turnId = lp.turnId
         inner join basUnit u
                 on u.unitId = lp.unitId
         inner join acdcourse co
                 on lp.courseid = co.courseid
         inner join acdRegimen r
                 on r.regimenid = g.regimenid
         INNER JOIN acdCurricularComponentType CCT
                 ON CCT.curricularComponentTypeId = cu.curricularComponentTypeId
     left join only basphysicalperson prof
                 on g.professorresponsible = prof.personid
          left join acdSchedule S
		 on S.groupId = g.groupId
	  left join acdcoursecoordinator COC
		 on (COC.courseId,
		     COC.courseVersion,
		     COC.turnId,
		     COC.unitId) = (lp.courseId,
				    lp.courseVersion,
				    lp.turnId,
				    lp.unitId)
);
