CREATE OR REPLACE VIEW acdgradedehorario AS
 SELECT a.groupid,
        g.curricularcomponentid AS cod_disciplina,
        g.name || ' (' || (SELECT description FROM basunit WHERE unitid = e.unitid) || ')' AS nome_disciplina,
        g.curricularcomponentversion AS versao_disciplina,
        f.courseid AS cod_curso,
        i.name AS nome_curso,
        f.courseversion AS versao_curso,
        f.turnid AS cod_turno_curso,
        j.description AS nome_turno_curso,
        c.professorid AS cod_professor,
        l.name AS nome_professor,
        CASE date_part('dow'::text, b.datas)
            WHEN 0 THEN 'Dom'::text
            WHEN 1 THEN 'Seg'::text
            WHEN 2 THEN 'Ter'::text
            WHEN 3 THEN 'Qua'::text
            WHEN 4 THEN 'Qui'::text
            WHEN 5 THEN 'Sex'::text
            WHEN 6 THEN 'Sáb'::text
            ELSE NULL::text
        END AS dia_semana_aula,
       date_part('dow'::text, b.datas) AS ordem_dia_semana,
       d.beginhour AS hora_inicio,
       d.endhour AS hora_fim,
       f.semester AS semestre,
       e.periodid,
       e.learningperiodid,
       f.unitid as cod_unidade,
       m.description as nome_unidade_curso
   FROM acdgroup a
   JOIN ( SELECT x.groupid, unnest(x.occurrencedates) AS datas, unnest(x.timeids) AS horario, x.scheduleid
           FROM acdschedule x) b ON a.groupid = b.groupid
   JOIN acdscheduleprofessor c ON c.scheduleid = b.scheduleid
   JOIN acdtime d ON d.timeid = b.horario
   JOIN acdlearningperiod e ON e.learningperiodid = a.learningperiodid
   JOIN acdcurriculum f ON f.curriculumid = a.curriculumid AND f.courseid::text = e.courseid::text AND f.courseversion = e.courseversion AND f.turnid = e.turnid AND f.unitid = e.unitid
   JOIN acdcurricularcomponent g ON g.curricularcomponentid::text = f.curricularcomponentid::text AND g.curricularcomponentversion = f.curricularcomponentversion
   JOIN acdcourse i ON i.courseid::text = e.courseid::text
   JOIN basturn j ON j.turnid = e.turnid
   JOIN basunit m ON m.unitid = f.unitid
   JOIN ONLY basperson l ON l.personid = c.professorid
  GROUP BY a.groupid, g.curricularcomponentid, g.name, g.curricularcomponentversion, f.courseid, i.name, f.courseversion, f.turnid, f.unitid, j.description, c.professorid, m.description, l.name,
CASE date_part('dow'::text, b.datas)
    WHEN 0 THEN 'Dom'::text
    WHEN 1 THEN 'Seg'::text
    WHEN 2 THEN 'Ter'::text
    WHEN 3 THEN 'Qua'::text
    WHEN 4 THEN 'Qui'::text
    WHEN 5 THEN 'Sex'::text
    WHEN 6 THEN 'Sáb'::text
    ELSE NULL::text
END, date_part('dow'::text, b.datas), d.beginhour, d.endhour, f.semester, e.periodid, e.learningperiodid, e.unitid;

ALTER TABLE acdgradedehorario
  OWNER TO postgres;
