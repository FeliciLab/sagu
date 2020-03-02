CREATE OR REPLACE VIEW view_censo_docente_cursos AS
select distinct
       a.professorid as id_professor,
       d.courseid as curso,
       COALESCE(co.idcursoinep, '') AS idcursoinep,

       ARRAY_TO_STRING(ARRAY[
           (CASE WHEN co.idcursoinep IS NULL THEN 'Nao foi informado o ID do curso no INEP (cadastro em Ocorrencia de curso)' ELSE NULL END)
       ], ', ') AS erros

  from acdscheduleprofessor a
 inner join acdschedule b using (scheduleid)
 inner join acdgroup c using (groupid)
 inner join acdlearningperiod d using (learningperiodid)
 inner join acdcourseoccurrence co on (co.courseid, co.courseversion, co.turnid, co.unitid) = (d.courseid, d.courseversion, d.turnid, d.unitid)
 where d.periodid = '2014/2'
 order by 1;
