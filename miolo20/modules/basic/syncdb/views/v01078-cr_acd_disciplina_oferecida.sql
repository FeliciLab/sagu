CREATE OR REPLACE VIEW cr_acd_disciplina_oferecida AS (
    SELECT groupid AS codigo_oferecida,
           curricularcomponentid AS codigo_disciplina,
           curricularcomponentversion AS versao_disciplina,
           disciplina AS disciplina,
           courseid AS codigo_curso, 
           coursename AS curso,
           courseversion AS versao_curso,
           turnid AS codigo_turno,
           turn AS turno,
           unitId AS codigo_unidade,
           unit AS unidade,
           periodId AS periodo,
           regimenid AS codigo_regime,
           regime,
           curso_de_ferias,
	   (SELECT TO_CHAR(MIN(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
	      FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
		      FROM acdSchedule Z
		     WHERE Z.groupId = rptDisciplina.groupId) X) AS data_inicio,
	   (SELECT TO_CHAR(MAX(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
	      FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
		      FROM acdSchedule Z
		     WHERE Z.groupId = rptDisciplina.groupId) X) AS data_fim,
	   iscancellation AS esta_cancelada,
	   isclosed AS esta_fechada,
           codigo_tipo_de_disciplina,
           tipo_de_disciplina,
           e_disciplina_de_tcc,
           classId AS codigo_turma,
           vagas
      FROM rptDisciplina
  GROUP BY groupid,
           curricularcomponentid,
           curricularcomponentversion,
           disciplina,
           courseid, 
           coursename,
           courseversion,
           turnid,
           turn,
           unitId,
           unit,
           periodId,
           regimenid,
           regime,
           curso_de_ferias,
	   iscancellation,
	   isclosed,
           codigo_tipo_de_disciplina,
           tipo_de_disciplina,
           e_disciplina_de_tcc,
           classId,
           vagas
);
