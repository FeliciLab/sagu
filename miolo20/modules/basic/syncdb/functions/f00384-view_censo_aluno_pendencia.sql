DROP TYPE IF EXISTS tabela_aluno;
CREATE TYPE tabela_aluno AS (id_aluno int, nome text, cpf text, data_nascimento text);

DROP TYPE IF EXISTS tabela_aluno_curso;
CREATE TYPE tabela_aluno_curso AS (id_curso int, id_aluno int, estado_contratual int, semestre_conclusao text, data_ingresso date,
contratoId int);

CREATE OR REPLACE FUNCTION view_censo_aluno_pendencia()
returns table(id_pessoa int, nome text, pendencia text) AS
$BODY$
/******************************************************************************
  NAME: view_censo_aluno_pendencia
  DESCRIPTION: Verifica as pendencias do censo_aluno

  REVISIONS: 
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       8/04/15   Felipe Ferreira         Função criada.
******************************************************************************/
DECLARE
    v_view_aluno tabela_aluno; --view aluno
    v_view_aluno_curso tabela_aluno_curso; --view aluno_curso
    v_nome_aluno_curso text;
BEGIN
    FOR v_view_aluno IN SELECT DISTINCT a.personid, a.name, replace(replace(b.content, '.'::text, ''::text), '-'::text, ''::text),
    to_char(a.datebirth::timestamp with time zone, 'ddmmyyyy'::text) FROM 
    basphysicalpersonstudent a LEFT JOIN basdocument b ON b.personid = a.personid
    LOOP
        --Cpf
        IF v_view_aluno.cpf = '' THEN
           RETURN QUERY SELECT v_view_aluno.id_aluno::integer, v_view_aluno.nome::text, 'Não informado CPF.'::text;
        END IF;
	IF v_view_aluno.data_nascimento = '' THEN
           RETURN QUERY SELECT v_view_aluno.id_aluno::integer, v_view_aluno.nome::text, 'Não informado data de nascimento.'::text;
        END IF;
	IF v_view_aluno.nome = '' THEN
           RETURN QUERY SELECT v_view_aluno.id_aluno::integer, v_view_aluno.nome::text, 'Não informado o nome.'::text;
        END IF;
    END LOOP;
 
    FOR v_view_aluno_curso IN SELECT sel.id_curso AS id_curso, sel.id_aluno AS id_aluno, matriculados.estado_contratual, sel.semestre_de_conclusao, ingresso.datahora, sel.contrato FROM ( SELECT a.personid AS id_aluno, co.idcursoinep AS id_curso, a.turnid AS turno, semestreconclusaocursopessoacenso(a.contractid) AS semestre_de_conclusao,
                CASE
                    WHEN c.value > 0::numeric THEN 1
                    ELSE 0
                END AS possui_financiamento,
                CASE
                    WHEN c.incentivetypeid = 3 THEN 1
                    ELSE 0
                END AS possui_fies,
                CASE
                    WHEN c.incentivetypeid = 4 AND c.valueispercent IS TRUE AND c.value = 100::numeric THEN 1
                    ELSE 0
                END AS possui_prouni_integral,
                CASE
                    WHEN c.incentivetypeid = 4 AND c.valueispercent IS TRUE AND c.value < 100::numeric THEN 1
                    ELSE 0
                END AS possui_prouni_parcial, a.contractid AS contrato, b.periodid AS periodo
           FROM acdcontract a
           JOIN acdcourseoccurrence co ON co.courseid::text = a.courseid::text AND co.courseversion = a.courseversion AND co.turnid = a.turnid AND co.unitid = a.unitid
           JOIN acdlearningperiod b ON b.courseid::text = a.courseid::text AND b.courseversion = a.courseversion AND b.turnid = a.turnid AND b.unitid = a.unitid
      LEFT JOIN finincentive c ON c.contractid = a.contractid AND (c.incentivetypeid = ANY (ARRAY[3, 4])) AND "overlaps"(b.begindate::timestamp with time zone, b.enddate::timestamp with time zone, c.startdate::timestamp with time zone, c.enddate::timestamp with time zone)
          WHERE b.periodid::text = '2014/2'::text
            AND a.contractid IN (SELECT max(contractid)
                                   FROM acdcontract xc
                                   JOIN acdcourseoccurrence xco ON (xc.courseid, xc.courseversion, xc.turnid, xc.unitid) = (xco.courseid, xco.courseversion, xco.turnid, xco.unitid)
                                  WHERE xc.personid = a.personid
                                    AND xco.idcursoinep = co.idcursoinep)
        ) sel
   LEFT JOIN ( SELECT a.contractid AS contrato, a.statecontractid AS estado, min(a.statetime) AS datahora
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
                  AND f.periodid::text = '2014/2'::text
             ) matriculados ON matriculados.contrato = sel.contrato AND matriculados.periodo::text = sel.periodo::text
    LOOP
        --Código curso
        IF v_view_aluno_curso.id_curso is null THEN
           SELECT ps.name INTO v_nome_aluno_curso FROM basphysicalpersonstudent ps WHERE v_view_aluno_curso.id_aluno = ps.personid; 
           RETURN QUERY SELECT v_view_aluno_curso.id_aluno, v_nome_aluno_curso::text, 'Não informado código do curso.'::text;
        END IF;
        --Semestre_conclusao
	IF v_view_aluno_curso.estado_contratual = 6 AND v_view_aluno_curso.semestre_conclusao = '' THEN
	SELECT ps.name INTO v_nome_aluno_curso FROM basphysicalpersonstudent ps WHERE v_view_aluno_curso.id_aluno = ps.personid;
           RETURN QUERY SELECT v_view_aluno_curso.id_aluno::integer, v_nome_aluno_curso::text, 'Não informado semestre de conclusão.'::text;
        END IF;
        --Data_ingresso
        IF to_char(v_view_aluno_curso.data_ingresso::date::timestamp with time zone, 'ddmmyyyy'::text) = '' THEN
           SELECT ps.name INTO v_nome_aluno_curso FROM basphysicalpersonstudent ps WHERE v_view_aluno_curso.id_aluno = ps.personid; 
           RETURN QUERY SELECT v_view_aluno_curso.id_aluno, v_nome_aluno_curso::text, 'Não informado código do curso.'::text;
        END IF;
    END LOOP;
END;
$BODY$
  LANGUAGE plpgsql;
