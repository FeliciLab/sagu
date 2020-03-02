CREATE OR REPLACE VIEW confirmacao_matricula_detalhada AS(
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
		    a.turnid AS turno_id, q.learningperiodid,
	    	    COALESCE(t.name, ( SELECT bascity.name FROM bascity WHERE bascity.cityid = r.cityid)) AS cidadeinstituicao,
                    r.name AS nome_instituicao,
                    dataporextenso(now()::date) AS data_extenso,
                    replace(replace(replace(array_agg(DISTINCT n.name)::text, '}', ''), '{', ''), '"', '') AS professor,
                    TO_CHAR(UNNEST(L.occurrenceDates), GETPARAMETER('BASIC', 'MASK_DATE')) as dias_de_aula,
                   ARRAY_TO_STRING(ARRAY(
                            SELECT timetouser(T.beginHour) || '-' || timetouser(T.endHour)
                                FROM acdTime T
			    WHERE T.timeId = ANY(L.timeIds)
                            ORDER BY 1
                       ), '   ') as horarios,
                    q.periodId
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
     INNER JOIN acdschedule l
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
      LEFT JOIN baslegalperson r
	     ON r.personid = (( SELECT aa.personid
				  FROM bascompanyconf aa
                                 WHERE aa.companyid = getparameter('BASIC', 'DEFAULT_COMPANY_CONF')::integer))
  WHERE g.statusid = getparameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::integer OR g.statusid = getparameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::integer
    AND g.curriculumid = h.curriculumid
  GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26, L.occurrenceDates, L.timeIds, q.periodId
  ORDER BY b.personid, i.name );
