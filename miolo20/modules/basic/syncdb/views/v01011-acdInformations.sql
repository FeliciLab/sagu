CREATE OR REPLACE VIEW acdinformations AS
      SELECT g.isclosed,
             co.name AS coursename,
             cu.courseid,
             cu.courseversion,
             cu.turnid,
             cu.unitid,
             fl.description AS formationlevel,
             lp.periodid,
             lp.learningperiodid,
             lp.description AS learningperiod,
             professorid,
             uprof.login AS professorlogin,
             uprof.name AS professorname,
             sp.scheduleprofessorid,
             spc.scheduleprofessorcontentid,
             spc.timeid AS spc_timeid,
             spc.date AS spc_date,
             spc.description AS spc_description,
             spc.visitingprofessorid AS spc_visitingprofessorid,
             spc.classoccurred AS spc_classoccurred,
             spc.isinternal AS spc_isinternal,
             spc.realstartdate AS spc_realstartdate,
             spc.realenddate AS spc_realenddate,
             spc.substituteprofessorid AS spc_substituteprofessorid,
             spc.curricularcomponentcategoryid AS spc_curricularcomponentcategoryid,
             s.scheduleid,
             s.occurrencedates,
             s.timeids,
             groupid,
             cc.curricularcomponentid AS curricularcomponentid,
             cc.curricularcomponentversion,
             cc.name AS curricularcomponentname,
             cu.curriculumid,
             cu.semester AS curriculumsemester,
             enrollid,
             es.statusid AS enrollstatusid,
             es.description AS enrollstatus,
             e.dateenroll || ' ' || e.hourenroll AS enrolldate,
             e.datetime AS enrolldatetime,
             e.datecancellation || ' ' || e.hourcancellation AS datetimecancellation,
             e.reasoncancellationid,
             e.finalnote,
             rc.description AS reasoncancellation,
             contractid,
             us.personid,
             us.login AS personlogin,
             us.name AS personname,
             de.degreeid,
             de.degreeenrollid,
             de.note,
             de.concept,
             fe.frequencydate,
             fe.frequency,
             (de.note IS NOT NULL OR de.concept IS NOT NULL) AS hasnoteorconcept,
             (fe.frequencydate IS NOT NULL AND fe.frequency IS NOT NULL) AS hasfrequency,
             ROUND(((e.frequency * 100) / cc.academicNumberHours)::NUMERIC, 2) || '%' AS frequencypercent,
             g.classid
    FROM acdgroup g
    inner join acdlearningperiod lp using(learningperiodid)
    inner join acdcurriculum cu using(curriculumid)
    inner join acdcurricularcomponent cc using(curricularcomponentid)
    left join acdschedule s using(groupid)
    left join acdscheduleprofessor sp using(scheduleid)
    left join acdscheduleprofessorcontent spc using(scheduleprofessorid)
    left join user_sagu uprof on (professorid = personid)
    left join acdenroll e using (groupid)
    left join acdenrollstatus es using (statusid)
    left join acddegreeenroll de using(enrollid)
    left join acdfrequenceenroll fe using(enrollid)
    left join acdreasoncancellation rc using(reasoncancellationid)
    left join acdcontract using(contractid)
    left join acdcourse co on (acdcontract.courseid = co.courseid)
    left join acdformationlevel fl on (fl.formationlevelid = co.formationlevelid)
    left join user_sagu us on (acdcontract.personid = us.personid);
