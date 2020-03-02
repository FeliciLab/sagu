CREATE OR REPLACE VIEW rptprofessorch AS (
     select distinct p.name AS professor,
     cc.academicnumberhours AS cargahoraria,
     (   select extract(hours from sum(_h.numberminutes))
         from rpthorarios _h
         where _h.groupid = g.groupid
     ) AS cargahorariarealizada,
     lp.periodid,
     lp.learningperiodid
     from acdschedule s
     inner join acdscheduleprofessor sp on sp.scheduleid = s.scheduleid
     inner join only basphysicalpersonprofessor p on p.personid = sp.professorid
     inner join acdgroup g on g.groupid = s.groupid
     inner join acdcurriculum cu on cu.curriculumid = g.curriculumid
     inner join acdcurricularcomponent cc on (cu.curricularcomponentid, cu.curricularcomponentversion) = (cc.curricularcomponentid, cc.curricularcomponentversion)
     inner join acdlearningperiod lp on lp.learningperiodid = g.learningperiodid
);
