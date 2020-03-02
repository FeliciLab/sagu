CREATE OR REPLACE VIEW view_censo_cursos AS
 SELECT cursos.curso,
        CASE
            WHEN cursos.teve_alunos IS TRUE THEN '1'::text
            ELSE '0'::text
        END AS teve_alunos,
        CASE
            WHEN cursos.teve_alunos IS FALSE THEN '4'::text
            ELSE ''::text
        END AS motivo_nao_teve_alunos,
        CASE
            WHEN curso_turno.matutino IS TRUE THEN
                CASE WHEN cursos.teve_alunos IS TRUE
                THEN 
                    '1'::TEXT
                ELSE
                    ''::TEXT
                END
            ELSE
                '0'::TEXT
        END AS turno_matutino,
        CASE
            WHEN curso_turno.matutino IS TRUE AND cursos.teve_alunos IS TRUE THEN '1'::text
            ELSE '0'::text
        END AS prazo_minimo_matutino,
        CASE
            WHEN curso_turno.matutino IS TRUE AND cursos.teve_alunos IS TRUE THEN COALESCE(curso_turno.vagas_ps_matutino::text, '0'::text)
            ELSE ''::text
        END AS vagas_ps_matutino,
        CASE
            WHEN curso_turno.matutino IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.inscritos_ps_matutino::text
            ELSE ''::text
        END AS inscritos_ps_matutino,
        CASE
            WHEN curso_turno.matutino IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_outras_vagas_matutino,
        CASE
            WHEN curso_turno.matutino IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_inscritos_outras_vagas_matutino,
        CASE
            WHEN curso_turno.vespertino IS TRUE THEN
                CASE WHEN cursos.teve_alunos IS TRUE
                THEN 
                    '1'::TEXT
                ELSE
                    ''::TEXT
                END
            ELSE
                '0'::TEXT
        END AS turno_vespertino,
        CASE
            WHEN curso_turno.vespertino IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.prazo_minimo_vespertino::text
            ELSE ''::text
        END AS prazo_minimo_vespertino,
        CASE
            WHEN curso_turno.vespertino IS TRUE AND cursos.teve_alunos IS TRUE THEN COALESCE(curso_turno.vagas_ps_vespertino::text, '0'::text)
            ELSE ''::text
        END AS vagas_ps_vespertino,
        CASE
            WHEN curso_turno.vespertino IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.inscritos_ps_vespertino::text
            ELSE ''::text
        END AS inscritos_ps_vespertino,
        CASE
            WHEN curso_turno.vespertino IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_outras_vagas_vespertino,
        CASE
            WHEN curso_turno.vespertino IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_inscritos_outras_vagas_vespertino,
        CASE
            WHEN curso_turno.noturno IS TRUE THEN
                CASE WHEN cursos.teve_alunos IS TRUE
                THEN 
                    '1'::TEXT
                ELSE
                    ''::TEXT
                END
            ELSE
                '0'::TEXT
        END AS turno_noturno,
        CASE
            WHEN curso_turno.noturno IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.prazo_minimo_noturno::text
            ELSE ''::text
        END AS prazo_minimo_noturno,
        CASE
            WHEN curso_turno.noturno IS TRUE AND cursos.teve_alunos IS TRUE THEN COALESCE(curso_turno.vagas_ps_noturno::text, '0'::text)
            ELSE ''::text
        END AS vagas_ps_noturno,
        CASE
            WHEN curso_turno.noturno IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.inscritos_ps_noturno::text
            ELSE ''::text
        END AS inscritos_ps_noturno,
        CASE
            WHEN curso_turno.noturno IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_outras_vagas_noturno,
        CASE
            WHEN curso_turno.noturno IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_inscritos_outras_vagas_noturno,
        CASE
            WHEN curso_turno.integral IS TRUE THEN
                CASE WHEN cursos.teve_alunos IS TRUE
                THEN 
                    '1'::TEXT
                ELSE
                    ''::TEXT
                END
            ELSE
                '0'::TEXT
        END AS turno_integral,
        CASE
            WHEN curso_turno.integral IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.prazo_minimo_integral::text
            ELSE ''::text
        END AS prazo_minimo_integral,
        CASE
            WHEN curso_turno.integral IS TRUE AND cursos.teve_alunos IS TRUE THEN COALESCE(curso_turno.vagas_ps_integral::text, '0'::text)
            ELSE ''::text
        END AS vagas_ps_integral,
        CASE
            WHEN curso_turno.integral IS TRUE AND cursos.teve_alunos IS TRUE THEN curso_turno.inscritos_ps_integral::text
            ELSE ''::text
        END AS inscritos_ps_integral,
        CASE
            WHEN curso_turno.integral IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_outras_vagas_integral,
        CASE
            WHEN curso_turno.integral IS TRUE AND cursos.teve_alunos IS TRUE THEN '0'::text
            ELSE ''::text
        END AS numero_inscritos_outras_vagas_integral,
        CASE
            WHEN cursos.teve_alunos = true THEN '0'::text
            ELSE ''::text
        END AS condicoes_ensino_aprendizagem,
        CASE
            WHEN cursos.teve_alunos = true THEN '0'::text
            ELSE ''::text
        END AS utiliza_instalacoes_aulas_praticas,
        CASE
            WHEN cursos.teve_alunos = true THEN '0'::text
            ELSE ''::text
        END AS oferece_disciplina_semipresencial, array_to_string(ARRAY[
        CASE
            WHEN 1 = 1 THEN 'Erro 1'::text
            ELSE NULL::text
        END,
        CASE
            WHEN 1 = 2 THEN 'Erro 2'::text
            ELSE NULL::text
        END,
        CASE
            WHEN 1 = 1 THEN 'Erro 3'::text
            ELSE NULL::text
        END], ', '::text) AS erros
   FROM ( SELECT DISTINCT
                 a.idcursoinep AS curso, (EXISTS ( SELECT aa.contractid
                                                  FROM acdenroll aa
                                                  JOIN acdcontract bb USING (contractid)
                                                  JOIN acdgroup cc USING (groupid)
                                                  JOIN acdlearningperiod dd ON dd.learningperiodid = cc.learningperiodid
                                                  JOIN acdcourseoccurrence ee ON ee.courseid = bb.courseid
                                                 WHERE ee.idcursoinep::text = a.idcursoinep::text
                                                   AND (aa.statusid <> ALL (ARRAY[5, 6, 7]))
                                                   AND (dd.periodid::text = ANY (ARRAY['2014/2'::character varying, '2014/1'::character varying]::text[])))) AS teve_alunos
            FROM acdcourseoccurrence a ) cursos
   JOIN ( SELECT DISTINCT
                 a.idcursoinep AS curso,
                 (EXISTS ( SELECT 1
                             FROM acdcourseoccurrence
                            WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                              AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::integer)) AS matutino,
                 ( SELECT COALESCE(min(acdcourseoccurrence.minimumconclusioncourse), 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::integer LIMIT 1) AS prazo_minimo_matutino,
                 (EXISTS ( SELECT 1
                             FROM acdcourseoccurrence
                            WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                              AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::integer)) AS vespertino,
                 ( SELECT COALESCE(min(acdcourseoccurrence.minimumconclusioncourse), 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::integer  LIMIT 1) AS prazo_minimo_vespertino,
                 (EXISTS ( SELECT 1
                             FROM acdcourseoccurrence
                            WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                              AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::integer  LIMIT 1)) AS noturno,
                 ( SELECT COALESCE(min(acdcourseoccurrence.minimumconclusioncourse), 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::integer  LIMIT 1) AS prazo_minimo_noturno,
                 (EXISTS ( SELECT 1
                             FROM acdcourseoccurrence
                            WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                              AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::integer)) AS integral,
                 ( SELECT COALESCE(min(acdcourseoccurrence.minimumconclusioncourse), 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = a.idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::integer LIMIT 1) AS prazo_minimo_integral,
                 ( SELECT DISTINCT
                          COALESCE(vagasinep, 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::integer LIMIT 1) AS vagas_ps_matutino,
                 ( SELECT DISTINCT
                          COALESCE(vagasinep, 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::integer LIMIT 1) AS vagas_ps_vespertino,
                 ( SELECT DISTINCT
                          COALESCE(vagasinep, 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::integer LIMIT 1) AS vagas_ps_noturno,
                 ( SELECT DISTINCT
                          COALESCE(vagasinep, 0::double precision) AS "coalesce"
                     FROM acdcourseoccurrence
                    WHERE acdcourseoccurrence.idcursoinep::text = idcursoinep::text
                      AND acdcourseoccurrence.turnid = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::integer LIMIT 1) AS vagas_ps_integral,
                 ( SELECT count(*) AS count
                     FROM spr.optioncourse oc
                LEFT JOIN spr.option o ON o.optionid = oc.optionid
                LEFT JOIN spr.subscriptionoption s ON s.optionid = o.optionid
                LEFT JOIN spr.selectiveprocess sp ON sp.selectiveprocessid = o.selectiveprocessid
                LEFT JOIN acdcourseoccurrence co ON co.courseid = oc.courseid AND co.courseversion = oc.courseversion AND co.turnid = oc.turnid AND co.unitid = oc.unitid
                    WHERE co.idcursoinep::text = a.idcursoinep::text
                      AND oc.turnid = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::integer
                      AND s.optionnumber = 1
                      AND (sp.periodid::text = ANY (ARRAY['2014/2'::character varying, '2014/1'::character varying]::text[]))) AS inscritos_ps_matutino,
                 ( SELECT count(*) AS count
                     FROM spr.optioncourse oc
                LEFT JOIN spr.option o ON o.optionid = oc.optionid
                LEFT JOIN spr.subscriptionoption s ON s.optionid = o.optionid
                LEFT JOIN spr.selectiveprocess sp ON sp.selectiveprocessid = o.selectiveprocessid
                LEFT JOIN acdcourseoccurrence co ON co.courseid = oc.courseid AND co.courseversion = oc.courseversion AND co.turnid = oc.turnid AND co.unitid = oc.unitid
                    WHERE co.idcursoinep::text = a.idcursoinep::text
                      AND oc.turnid = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::integer
                      AND s.optionnumber = 1
                      AND (sp.periodid::text = ANY (ARRAY['2014/2'::character varying, '2014/1'::character varying]::text[]))) AS inscritos_ps_vespertino,
                 ( SELECT count(*) AS count
                     FROM spr.optioncourse oc
                LEFT JOIN spr.option o ON o.optionid = oc.optionid
                LEFT JOIN spr.subscriptionoption s ON s.optionid = o.optionid
                LEFT JOIN spr.selectiveprocess sp ON sp.selectiveprocessid = o.selectiveprocessid
                LEFT JOIN acdcourseoccurrence co ON co.courseid = oc.courseid AND co.courseversion = oc.courseversion AND co.turnid = oc.turnid AND co.unitid = oc.unitid
                    WHERE co.idcursoinep::text = a.idcursoinep::text
                      AND oc.turnid = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::integer
                      AND s.optionnumber = 1
                      AND (sp.periodid::text = ANY (ARRAY['2014/2'::character varying, '2014/1'::character varying]::text[]))) AS inscritos_ps_noturno,
                 ( SELECT count(*) AS count
                     FROM spr.optioncourse oc
                LEFT JOIN spr.option o ON o.optionid = oc.optionid
                LEFT JOIN spr.subscriptionoption s ON s.optionid = o.optionid
                LEFT JOIN spr.selectiveprocess sp ON sp.selectiveprocessid = o.selectiveprocessid
                LEFT JOIN acdcourseoccurrence co ON co.courseid = oc.courseid AND co.courseversion = oc.courseversion AND co.turnid = oc.turnid AND co.unitid = oc.unitid
                    WHERE co.idcursoinep::text = a.idcursoinep::text
                      AND oc.turnid = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::integer
                      AND s.optionnumber = 1
                      AND (sp.periodid::text = ANY (ARRAY['2014/2'::character varying, '2014/1'::character varying]::text[]))) AS inscritos_ps_integral
   FROM acdcourseoccurrence a) curso_turno ON curso_turno.curso::text = cursos.curso::text
  WHERE cursos.curso IS NOT NULL
  ORDER BY cursos.curso;
