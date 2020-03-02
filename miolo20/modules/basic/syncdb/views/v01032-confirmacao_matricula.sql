CREATE OR REPLACE VIEW confirmacao_matricula AS(
             SELECT a.contractid AS contrato,
                    b.miolousername AS login,
                    b.personid AS cod_pessoa,
 		    b.name AS nome,
		    b.location AS endereco,
		    b.neighborhood AS bairro,
		    b.complement AS complemento,
		    c.name AS cidade,
		    COALESCE(b.zipcode, c.zipcode) AS cep,
		    b.number AS numero,
		    a.courseid AS curso_id,
		    d.name AS curso,
		    a.courseversion AS curso_version,
		    (SELECT AA.name FROM acdclass AA INNER JOIN acdclasspupil BB ON AA.classid = BB.classid WHERE BB.contractid = a.contractid AND (BB.endDate IS NULL OR BB.endDate <= NOW()::DATE) ORDER BY begindate DESC limit 1) as turma,
		    h.curricularcomponentid AS cod_disciplina,
		    h.curricularcomponentversion AS version_disciplina,
		    i.name AS disciplina,
	            i.academiccredits AS creditos,
		    i.academicnumberhours AS carga_horaria,
		    COALESCE(o.description, '-') AS sala,
		    COALESCE(p.description, '-') AS unidade,
		    a.unitid AS unidade_id,
		    a.turnid AS turno_id,
		    q.learningperiodid,
	    	    COALESCE(t.name, ( SELECT bascity.name FROM bascity WHERE bascity.cityid = r.cityid)) AS cidadeinstituicao,
                    r.name AS nome_instituicao,
                    dataporextenso(now()::date) AS data_extenso,
                    string_agg(DISTINCT COALESCE(n.name, '-'), ', ')::text AS professor,
                    q.periodId,
                    --busca os dias da semana com aula
                    (SELECT replace(replace(array_agg(ds.dias_aula)::text, '}', '')::text,'{','')
                       FROM (SELECT DISTINCT obterdiaextenso(EXTRACT(DOW FROM groupdate)::int) as dias_aula
					FROM (SELECT unnest(occurrencedates) as groupdate
						FROM acdschedule a
					       WHERE groupid = j.groupid
					         AND a.scheduleId = l.scheduleId) AS group_od) as ds) as dias_semana,
                    bdcpf.content as cpf,
                    b.email as email,
                    j.groupId,
                    (SELECT array_to_string(array_agg(TO_CHAR(TI.beginHour, getParameter('BASIC', 'MASK_TIME')) || ' às ' || TO_CHAR(TI.endHour, getParameter('BASIC', 'MASK_TIME'))), ', ')
		       FROM acdTime TI
		 INNER JOIN (SELECT UNNEST(SC.timeIds) AS timeId
			       FROM acdSchedule SC
			      WHERE SC.groupid = j.groupId
			        AND SC.scheduleId = l.scheduleId) X
				 ON X.timeId = TI.timeId
		      ORDER BY 1) AS horarios,
		      (SELECT description
			 FROM basTurn
			WHERE turnId = a.turnid) AS turno,
                H.semester as semestre_da_disciplina,
                COALESCE(o.room, '-') AS room,
                COALESCE(getunitdescription(o.unitid), '-') AS room_unit_description,
                CCG.curricularcomponentid AS cod_disciplina_cursada,
		CCG.curricularcomponentversion AS version_disciplina_cursada,
		CCG.name AS disciplina_cursada,
	        CCG.academiccredits AS creditos_disciplina_cursada,
		CCG.academicnumberhours AS carga_horaria_disciplina_cursada,
                ES.statusId AS codigo_status_matricula,
                ES.description AS status_matricula,
                (CASE WHEN h.curricularcomponentid <> CCG.curricularcomponentid
		     THEN h.curricularcomponentid || ' - ' || i.name || ' (' || CCG.curricularcomponentid || ' - ' || CCG.name || ')'
		     ELSE h.curricularcomponentid || ' - ' || i.name
		 END)::TEXT AS codigo_e_disciplina_formatada,
                EC.enrollconfigid
	   FROM acdcontract a
INNER JOIN ONLY basphysicalperson b
             ON a.personid = b.personid
     INNER JOIN bascity c
             ON b.cityid = c.cityid
     INNER JOIN acdcourse d
             ON a.courseid::text = d.courseid::text
     INNER JOIN acdenroll g
             ON a.contractid = g.contractid
     INNER JOIN acdcurriculum h
             ON g.curriculumid = h.curriculumid
     INNER JOIN acdcurricularcomponent i
             ON h.curricularcomponentid::text = i.curricularcomponentid::text
            AND h.curricularcomponentversion = i.curricularcomponentversion
     INNER JOIN acdgroup j
             ON g.groupid = j.groupid
     INNER JOIN acdCurriculum CG
	     ON CG.curriculumId = J.curriculumId
     INNER JOIN acdCurricularComponent CCG
	     ON (CCG.curricularComponentId,
		 CCG.curricularComponentVersion) = (CG.curricularComponentId,
     						    CG.curricularComponentVersion)
     INNER JOIN acdEnrollStatus ES
             ON ES.statusId = G.statusId
      LEFT JOIN acdschedule l -- Pode ser que as disciplinas ainda não possuam horários configurados.
             ON j.groupid = l.groupid
      LEFT JOIN acdscheduleprofessor m
             ON l.scheduleid = m.scheduleid
 LEFT JOIN ONLY basphysicalpersonprofessor n
             ON m.professorid = n.personid
      LEFT JOIN insphysicalresource o
             ON l.physicalresourceid = o.physicalresourceid
      LEFT JOIN basunit p
             ON a.unitid = p.unitid
      LEFT JOIN baslocation s
             ON p.locationid = s.locationid
      LEFT JOIN bascity t
             ON s.cityid = t.cityid
     INNER JOIN acdlearningperiod q
             ON j.learningperiodid = q.learningperiodid
      LEFT JOIN basdocument bdcpf
             ON (bdcpf.personid = b.personid
                 AND bdcpf.documenttypeid = 2)
      LEFT JOIN baslegalperson r
	     ON r.personid = (( SELECT aa.personid
				  FROM bascompanyconf aa
                                 WHERE aa.companyid = getparameter('BASIC', 'DEFAULT_COMPANY_CONF')::integer))
      LEFT JOIN acdenrollconfig EC
             ON EC.enrollconfigid = obterconfiguracaodematriculapelamatricula(G.enrollid)
  WHERE g.statusid = getparameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::integer OR g.statusid = getparameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::integer
  GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26, 
	   j.groupid, bdcpf.content, q.periodid, b.email, H.semester, o.room, o.unitid,
	   CCG.curricularcomponentid, CCG.curricularcomponentversion, CCG.name, CCG.academiccredits, CCG.academicnumberhours,
	   ES.statusId, ES.description, l.scheduleId, EC.enrollconfigid
  ORDER BY b.personid );
