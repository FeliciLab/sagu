CREATE OR REPLACE VIEW cr_acd_possibilidade_de_matricula AS (
    SELECT sel.curricularcomponentid AS codigo_disciplina,
           sel.curricularcomponentversion AS versao_disciplina,
           sel.curriculumid AS codigo_curriculo,
           sel.name AS disciplina,
           sel.semester AS semestre_disciplina,
           (SELECT COUNT(curriculumid) > 0
              FROM acdcondition
             WHERE conditioncurriculumid = sel.curriculumid) AS e_disciplina_de_requisito,
           (SELECT COUNT(conditioncurriculumid) > 0
              FROM acdcondition
             WHERE curriculumid = sel.curriculumid) AS possui_requisitos,
           atende_requisitos(sel.curriculumid, sel.contractid) AS atende_requisitos,
           sel.contractid AS codigo_contrato,
           sel.personid AS codigo_aluno,
           getpersonname(sel.personid) as nome_aluno,
           aluno_mesmo_semestre,
           aluno_semestres_anteriores,
           aluno_semestres_posteriores,
           concluinte,
           aluno_inadimplente,
           (SELECT AVG(totalenrolled)
              FROM acdgroup xa
        INNER JOIN acdcurriculum xb 
                ON xb.curriculumid = sel.curriculumid
        INNER JOIN acdlearningperiod xc 
             USING (learningperiodid)
             WHERE xb.curricularcomponentid = sel.curricularcomponentid
               AND xb.curricularcomponentversion = sel.curricularcomponentversion
               AND (xc.begindate, xc.enddate) OVERLAPS ((now() - interval '1 year')::date, now()::date)
               AND xa.totalenrolled > 0) as media_por_oferecimento,
           sel.courseid AS codigo_curso,
           getCourseName(sel.courseid) AS nome_curso,
           sel.courseVersion AS versao_curso,
           sel.turnId AS codigo_turno,
           getTurnDescription(sel.turnId) AS turno,
           sel.unitId AS codigo_unidade,
           getUnitDescription(sel.unitId) AS unidade,
           get_semester_contract(sel.contractid) AS semestre_aluno,
           (SELECT TO_CHAR(statetime, getParameter('BASIC', 'MASK_DATE')) 
              FROM obterMovimentacaoContratualDeIngressoDoAluno(sel.contractid))::VARCHAR as data_ingresso,
           (SELECT description
              FROM acdStateContract
             WHERE stateContractId = getContractState(sel.contractId)) AS situacao_contratual,
           iscontractclosed(sel.contractId) AS ativo_no_curso
      FROM (SELECT b.curricularcomponentid,
                   b.curricularcomponentversion,
                   (SELECT name
                      FROM acdcurricularcomponent
                     WHERE curricularcomponentid = b.curricularcomponentid
                       AND curricularcomponentversion = b.curricularcomponentversion) AS name,
                   b.curriculumid,
                   d.contractid,
                   getcontractpersonid(d.contractid) AS personid,
                   b.semester,
                   (d.semester = b.semester) AS aluno_mesmo_semestre,
                   (d.semester < b.semester) AS aluno_semestres_anteriores,
                   (d.semester > b.semester) AS aluno_semestres_posteriores,
                   (c.periodtotal > 0 AND (d.semester >= (c.periodtotal - 1))) AS concluinte,
                   (EXISTS (SELECT invoiceid
                              FROM finreceivableinvoice
                             WHERE personid = (SELECT personid 
                                                 FROM acdcontract 
                                                WHERE contractid = d.contractid)
                               AND (maturitydate + interval '7 days')::date < now()::date
                               AND balance > 0)) AS aluno_inadimplente,
                   d.courseid,
                   d.courseVersion,
                   d.turnId,
                   d.unitId
              FROM acdcurriculum b
        INNER JOIN acdcourseversion c 
                ON (c.courseid,
                    c.courseversion) = (b.courseid, 
                                        b.courseversion)
        INNER JOIN (SELECT contractid, 
                           courseid, 
                           courseversion, 
                           turnid, 
                           unitid, 
                           COALESCE(MAX(y.semester), 0) + 1 AS semester
                      FROM acdcontract x
                 LEFT JOIN acdsemestercontractperiod y 
                     USING (contractid)
                     WHERE (SELECT isclosecontract IS NOT TRUE
                              FROM acdmovementcontract
                        INNER JOIN acdstatecontract 
                             USING (statecontractid)
                             WHERE contractId = x.contractId
                          ORDER BY statetime 
                        DESC LIMIT 1)
                  GROUP BY contractid, 
                           courseid, 
                           courseversion, 
                           turnid, 
                           unitid) d
                ON (d.courseId,
                    d.courseVersion,
                    d.turnId,
                    d.unitId) = (b.courseId,
                                 b.courseVersion,
                                 b.turnId,
                                 b.unitId)
             WHERE b.semester <> 0
               AND NOT EXISTS (SELECT enrollid 
                                 FROM acdenroll
                                WHERE statusid IN (getparameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT, 
                                                   getparameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT, 
                                                   getparameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT, 
                                                   getparameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT)
                                  AND curriculumid = b.curriculumid
                                  AND contractid = d.contractid)) AS sel
);
