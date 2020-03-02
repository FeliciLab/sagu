-- Function: get_estado_contratual_censo(integer, character varying)
CREATE OR REPLACE FUNCTION get_estado_contratual_censo(
    integer,
    character varying)
  RETURNS integer AS
$BODY$
select
CASE WHEN matriculados.estado_contratual IS NOT NULL 
             THEN matriculados.estado_contratual
             WHEN trancamentos.estado_contratual IS NOT NULL 
             THEN trancamentos.estado_contratual
             ELSE evasoes.estado_contratual
        END AS situacao_de_vinculo
from
( SELECT a.personid AS id_aluno, co.idcursoinep AS id_curso, a.turnid AS turno, semestreconclusaocursopessoacenso(a.contractid) AS semestre_de_conclusao, curso.degree as grau_academico, 
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
             ) trancamentos ON trancamentos.contrato = sel.contrato    

where sel.contrato=$1 and sel.periodo=$2    $BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION get_estado_contratual_censo(integer, character varying)
  OWNER TO postgres;
