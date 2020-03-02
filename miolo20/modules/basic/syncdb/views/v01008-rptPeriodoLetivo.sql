CREATE OR REPLACE VIEW rptperiodoletivo AS (
             select lp.learningperiodid,
                    lp.periodid,
                    lp.courseid,
                    lp.courseversion,
                    lp.turnid,
                    lp.unitid,
                    lp.description,
                    lp.formationlevelid ,
                    lp.previouslearningperiodid,
                    lp.begindate,
                    lp.enddate,
                    lp.begindatelessons,
                    lp.weekendexamsbegin,
                    lp.finalaverage,
                    lp.minimumfrequency,
                    lp.sagu1periodid,
                    lp.minimumcredits,
                    lp.minimumcreditsfreshman,
                    lp.minimumcreditsturn,
                    lp.sagu1previousperiodid,
                    lp.parcelsnumber,
                    t.description AS turn,
                    u.description AS unit,
                    datetouser(now()::date) AS datahoje,
                    timestamptouser(now()::timestamp) AS datahorahoje,
                    co.name AS coursename
               from acdlearningperiod lp
         inner join basturn t
                 on t.turnId = lp.turnId
         inner join basunit u
                 on u.unitId = lp.unitId
         inner join acdcourse co
                 on lp.courseid = co.courseid
);
