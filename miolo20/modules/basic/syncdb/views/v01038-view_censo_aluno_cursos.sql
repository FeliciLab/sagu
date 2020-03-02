CREATE OR REPLACE VIEW view_censo_aluno_cursos AS
 SELECT DISTINCT
        sel.id_aluno as id_aluno,
        sel.id_curso AS id_curso,
        sel.grau_academico, 
        CASE WHEN sel.grau_academico ='LICENCIATURA' THEN '0' ELSE '' END as parfor,
        sel.contrato,
        CASE WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::int THEN '1'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::int THEN '2'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::int THEN '3'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::int THEN '4'::text
        END AS turno,
        coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) AS situacao_de_vinculo,       
        CASE
            WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 2 THEN '0'::text
            ELSE ''::text
        END AS forma_ingresso_pecg,
        CASE
            WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 2 THEN '0'::text
            ELSE ''::text
        END AS mobilidade_academica,
        CASE WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 6 THEN sel.semestre_de_conclusao ELSE ''::character varying END AS semestre_de_conclusao,
        CASE
            WHEN ingresso.datahora IS NOT NULL THEN to_char(ingresso.datahora::date::timestamp with time zone, 'ddmmyyyy'::text)
            ELSE COALESCE(( SELECT to_char(min(acdmovementcontract.statetime), 'ddmmyyyy'::text) AS to_char
               FROM acdmovementcontract
              WHERE acdmovementcontract.contractid = sel.contrato), '')
        END AS data_de_ingresso, 
        '0'::TEXT AS ingresso_por_enem,
        CASE
            WHEN ingresso.estado = 1 THEN '1'::TEXT
            ELSE '0'::TEXT
        END AS ingresso_por_vestibular,
        CASE
            WHEN ingresso.estado <> 1 OR ingresso.estado IS NULL THEN '1'::TEXT
            ELSE '0'::TEXT
        END AS outras_formas_de_ingresso,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '1'::text
            ELSE '0'::text
        END AS possui_financiamento,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.possui_fies) > 0 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS possui_fies,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.possui_prouni_integral) > 0 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS possui_prouni_integral,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.possui_prouni_parcial) > 0 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS possui_prouni_parcial,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '0'::text
            ELSE ''::text
        END AS financ_reembolsavel_estadual,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '0'::text
            ELSE ''::text
        END AS financ_reembolsavel_municipal,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.financ_reembolsavel_ies) > 0 THEN '1'::text
                ELSE '0'::text
            END            
            ELSE ''::text            
        END AS financ_reembolsavel_ies,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.financ_reembolsavel_externas) > 0 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS financ_reembolsavel_externas,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '0'::text
            ELSE ''::text
        END AS financ_n_reembolsavel_estadual,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '0'::text
            ELSE ''::text
        END AS financ_n_reembolsavel_municipal,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN
            CASE
                WHEN sum(sel.financ_n_reembolsavel_ies) > 0 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS financ_n_reembolsavel_ies,
        CASE
            WHEN sum(sel.possui_financiamento) > 0 THEN '0'::text
            ELSE ''::text
        END AS financ_n_reembolsavel_externas,
        CASE
            WHEN ingresso.datahora IS NOT NULL then
               case when to_char(ingresso.datahora, 'mm')::int<=6 then
                '01'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               else
                '02'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               end
            ELSE
                CASE WHEN EXISTS(SELECT 1 FROM acdmovementcontract WHERE acdmovementcontract.contractid = sel.contrato)
                THEN
                   ( select (case when EXTRACT('MONTH' from now()::date)::int <= 6 then '01' else '02' end)::text||to_char(now()::date, 'yyyy') AS ano_ingresso)
                 END
        END AS semestre_de_ingresso,
        CASE WHEN to_char(ingresso.datahora, 'yyyy') >= '2013' and tipo_escola_ensinoMedio = 2 THEN
                '1'::TEXT
            ELSE
                tipo_escola_ensinoMedio::TEXT
            END AS tipo_escola_ensinoMedio

FROM
(
 SELECT a.personid AS id_aluno, co.idcursoinep AS id_curso, a.turnid AS turno, semestreconclusaocursopessoacenso(a.contractid) AS semestre_de_conclusao,
        curso.degree as grau_academico, 
                CASE WHEN aluno.institutionidhs IS NOT NULL THEN
                   CASE WHEN escola.ispublic='t' THEN 1 --instituições públicas
                        WHEN escola.ispublic='f' THEN 0 --instituições privadas
		        ELSE 2                          --não informado
                   END
                END AS tipo_escola_ensinoMedio, 
                CASE
                    WHEN c.value > 0::numeric THEN 1
                    ELSE 0
                END AS possui_financiamento,
                CASE
                    WHEN c.incentivetypeid = 3 THEN 1
                    ELSE 0
                END AS possui_fies,
                CASE
                    WHEN c.incentivetypeid = 7 AND c.valueispercent IS TRUE AND c.value = 100::numeric THEN 1
                    ELSE 0
                END AS possui_prouni_integral,
                CASE
                    WHEN c.incentivetypeid = 7 AND c.valueispercent IS TRUE AND c.value < 100::numeric THEN 1
                    ELSE 0
                END AS possui_prouni_parcial, 
                CASE
                    WHEN c.incentivetypeid in (6) THEN 1
                    ELSE 0
                END AS financ_reembolsavel_ies,
                CASE
                    WHEN c.incentivetypeid in (10,11) THEN 1
                    ELSE 0
                END AS financ_reembolsavel_externas,
                CASE
                    WHEN c.incentivetypeid in (8,9,15,16) THEN 1
                    ELSE 0
                END AS financ_n_reembolsavel_ies,
                a.contractid AS contrato, 
                b.periodid AS periodo
           FROM acdcontract a
           JOIN acdcourse curso on a.courseid=curso.courseid
           JOIN only basphysicalpersonstudent aluno on aluno.personid=a.personid
      LEFT JOIN only baslegalperson escola on escola.personid=aluno.institutionidhs
           JOIN acdcourseoccurrence co ON co.courseid::text = a.courseid::text AND co.courseversion = a.courseversion AND co.turnid = a.turnid AND co.unitid = a.unitid
           JOIN acdlearningperiod b ON b.courseid::text = a.courseid::text AND b.courseversion = a.courseversion AND b.turnid = a.turnid AND b.unitid = a.unitid
      LEFT JOIN finincentive c ON c.contractid = a.contractid AND (c.incentivetypeid = ANY (ARRAY[3, 4])) AND "overlaps"(b.begindate::timestamp with time zone, b.enddate::timestamp with time zone, c.startdate::timestamp with time zone, c.enddate::timestamp with time zone)
          WHERE b.periodid::text ILIKE '%2014%'::text AND 
                a.contractid IN (SELECT max(contractid)
                                   FROM acdcontract xc
                                   JOIN acdcourseoccurrence xco ON (xc.courseid, xc.courseversion, xc.turnid, xc.unitid) = (xco.courseid, xco.courseversion, xco.turnid, xco.unitid)
                                  WHERE xc.personid = a.personid
                                    AND xco.idcursoinep = co.idcursoinep)
        ) sel
   LEFT JOIN ( 
               SELECT a.contractid AS contrato, a.statecontractid AS estado, min(a.statetime) AS datahora
                 FROM acdmovementcontract a
                 JOIN acdstatecontract b USING (statecontractid)
                WHERE upper(b.inouttransition::text) = 'I'::text
                GROUP BY a.contractid, a.statecontractid 
             ) ingresso ON ingresso.contrato = sel.contrato
   LEFT JOIN ( SELECT DISTINCT c.contractid AS contrato, f.periodid AS periodo, 2 AS estado_contratual
                 FROM acdcontract c
                 JOIN acdlearningperiod f ON f.courseid::text = c.courseid::text AND f.courseversion = c.courseversion AND f.unitid = c.unitid AND f.turnid = c.turnid
                WHERE (EXISTS ( SELECT aa.enrollid
                                  FROM acdenroll aa
                                  JOIN acdgroup bb USING (groupid)
                                  JOIN acdlearningperiod cc ON cc.learningperiodid = bb.learningperiodid
                                 WHERE aa.contractid = c.contractid
                                   AND cc.periodid::text = f.periodid::text
                                   AND (aa.statusid <> ALL (ARRAY[5, 6, 7]))))
                  AND f.periodid::text ilike ('%2014%'::text)
             ) matriculados ON matriculados.contrato = sel.contrato AND matriculados.periodo::text = sel.periodo::text
   LEFT JOIN ( SELECT DISTINCT COALESCE(d.periodid, ( SELECT acdlearningperiod.periodid
                                                        FROM acdlearningperiod
                                                       WHERE acdlearningperiod.courseid::text = c.courseid::text
                                                         AND acdlearningperiod.courseversion = c.courseversion
                                                         AND acdlearningperiod.unitid = c.unitid
                                                         AND acdlearningperiod.turnid = c.turnid
                                                         AND a.statetime >= acdlearningperiod.begindate
                                                         AND a.statetime <= acdlearningperiod.enddate)) AS periodo,
                                CASE
                                    WHEN a.statecontractid = 5 THEN 3
                                    WHEN a.statecontractid = 7 THEN 5
                                    WHEN a.statecontractid = ANY (ARRAY[9, 10, 11]) THEN 6
                                    WHEN a.statecontractid = ANY (ARRAY[12, 18]) THEN 4
                                    WHEN a.statecontractid = ANY (ARRAY[13, 17]) THEN 4
                                    ELSE 4
                                END AS estado_contratual,
                               c.contractid AS contrato
                 FROM acdmovementcontract a
                 JOIN acdstatecontract b USING (statecontractid)
                 JOIN acdcontract c USING (contractid)
                 JOIN basphysicalpersonstudent e USING (personid)
            LEFT JOIN acdlearningperiod d USING (learningperiodid)
                WHERE b.isclosecontract IS TRUE AND NOT (EXISTS ( SELECT aa.enrollid
                                                                    FROM acdenroll aa
                                                                    JOIN acdgroup bb USING (groupid)
                                                                    JOIN acdlearningperiod cc ON cc.learningperiodid = bb.learningperiodid
                                                                   WHERE aa.contractid = a.contractid
                                                                     AND (cc.periodid::text = d.periodid::text OR
                                                                           d.periodid IS NULL AND
                                                                           a.statetime::date >= cc.begindate
                                                                           AND a.statetime::date <= cc.enddate)
                                                                           AND (aa.statusid <> ALL (ARRAY[5, 6, 7])))
                                                        )
             ) evasoes ON evasoes.contrato = sel.contrato AND evasoes.periodo::text = sel.periodo::text
   LEFT JOIN ( SELECT DISTINCT COALESCE(d.periodid, ( SELECT acdlearningperiod.periodid
                                                        FROM acdlearningperiod
                                                       WHERE acdlearningperiod.courseid::text = c.courseid::text
                                                         AND acdlearningperiod.courseversion = c.courseversion
                                                         AND acdlearningperiod.unitid = c.unitid
                                                         AND acdlearningperiod.turnid = c.turnid
                                                         AND a.statetime >= acdlearningperiod.begindate
                                                         AND a.statetime <= acdlearningperiod.enddate)) AS periodo,
                                CASE
                                    WHEN a.statecontractid = 5 THEN 3
                                    WHEN a.statecontractid = 7 THEN 5
                                    WHEN a.statecontractid = ANY (ARRAY[9, 10, 11]) THEN 6
                                    WHEN a.statecontractid = ANY (ARRAY[12, 18]) THEN 4
                                    WHEN a.statecontractid = ANY (ARRAY[13, 17]) THEN 4
                                    ELSE 4
                                END AS estado_contratual,
                               c.contractid AS contrato
                 FROM acdmovementcontract a
                 JOIN acdstatecontract b USING (statecontractid)
                 JOIN acdcontract c USING (contractid)
                 JOIN basphysicalpersonstudent e USING (personid)
            LEFT JOIN acdlearningperiod d USING (learningperiodid)
                WHERE b.statecontractid=5
                  AND d.begindate>='2014/12/31'::date-interval '3 years' 
             ) trancamentos ON trancamentos.contrato = sel.contrato --AND trancamentos.periodo::text = sel.periodo::text  
WHERE substring(CASE
            WHEN ingresso.datahora IS NOT NULL then
               case when to_char(ingresso.datahora, 'mm')::int<=6 then
                '01'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               else
                '02'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               end
            ELSE
                CASE WHEN EXISTS(SELECT 1 FROM acdmovementcontract WHERE acdmovementcontract.contractid = sel.contrato)
                THEN
                   ( select (case when EXTRACT('MONTH' from now()::date)::int <= 6 then '01' else '02' end)::text||to_char(now()::date, 'yyyy') AS ano_ingresso)
                 END
        END from 3 for 4)::int<2015
    AND sel.periodo::text ilike (  '%2014%'::text ) 
    AND (evasoes.estado_contratual IS NOT NULL OR matriculados.estado_contratual IS NOT NULL OR trancamentos.estado_contratual IS NOT NULL)
    AND sel.id_curso IS NOT null
    AND ((matriculados.estado_contratual = 6 AND sel.semestre_de_conclusao <> '') OR matriculados.estado_contratual <> 6) 
    AND ((ingresso.datahora IS NULL AND ( SELECT to_char(min(acdmovementcontract.statetime), 'ddmmyyyy'::text) AS to_char
   				            FROM acdmovementcontract
				           WHERE acdmovementcontract.contractid = sel.contrato) IS NOT NULL) OR ingresso.datahora IS NOT NULL)
GROUP BY
        sel.id_aluno ,
        sel.id_curso ,
        sel.grau_academico, 
        sel.contrato,
        CASE WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_MATUTINO')::int THEN '1'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_VESPERTINO')::int THEN '2'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_NOTURNO')::int THEN '3'::text
             WHEN sel.turno = GETPARAMETER('BASIC', 'TURNO_INTEGRAL')::int THEN '4'::text
        END ,
        coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) ,       
        CASE
            WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 2 THEN '0'::text
            ELSE ''::text
        END ,
        CASE
            WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 2 THEN '0'::text
            ELSE ''::text
        END ,
        CASE WHEN coalesce(get_estado_contratual_censo(sel.contrato, '2014/2'), get_estado_contratual_censo(sel.contrato, '2014/1')) = 6 THEN sel.semestre_de_conclusao ELSE ''::character varying END ,
        CASE
            WHEN ingresso.datahora IS NOT NULL THEN to_char(ingresso.datahora::date::timestamp with time zone, 'ddmmyyyy'::text)
            ELSE COALESCE(( SELECT to_char(min(acdmovementcontract.statetime), 'ddmmyyyy'::text) AS to_char
               FROM acdmovementcontract
              WHERE acdmovementcontract.contractid = sel.contrato), '')
        END , 
        '0'::TEXT ,
        CASE
            WHEN ingresso.estado = 1 THEN '1'::TEXT
            ELSE '0'::TEXT
        END ,
        CASE
            WHEN ingresso.estado <> 1 OR ingresso.estado IS NULL THEN '1'::TEXT
            ELSE '0'::TEXT
        END ,
        CASE
            WHEN ingresso.datahora IS NOT NULL then
               case when to_char(ingresso.datahora, 'mm')::int<=6 then
                '01'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               else
                '02'||to_char(ingresso.datahora::date::timestamp with time zone, 'yyyy'::text)
               end
            ELSE
                CASE WHEN EXISTS(SELECT 1 FROM acdmovementcontract WHERE acdmovementcontract.contractid = sel.contrato)
                THEN
                   ( select (case when EXTRACT('MONTH' from now()::date)::int <= 6 then '01' else '02' end)::text||to_char(now()::date, 'yyyy') AS ano_ingresso)
                 END
        END ,
        CASE WHEN to_char(ingresso.datahora, 'yyyy') >= '2013' and tipo_escola_ensinoMedio = 2 THEN
                '1'::TEXT
            ELSE
                tipo_escola_ensinoMedio::TEXT
            END            
