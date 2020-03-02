-- Dias da semana dos horarios de uma disciplina
-- Ex.: select array_to_string(array(SELECT horario FROM rpthorariodias where groupid=1 group by diasemana,horario order by diasemana), ', ')
-- Retorno: TER (08:00 as 09:40, 09:50 as 11:30), QUA (08: 00 as 09:40, 09:50 as 11:30)
CREATE OR REPLACE VIEW rpthorariodias AS (
 SELECT ( SELECT ((obterdiaabreviado(date_part('dow'::text, x.occurrencedate)::integer)::text || ' ('::text) || array_to_string(ARRAY( SELECT DISTINCT (rpthorarios.beginhour::text || ' - '::text) || rpthorarios.endhour::text
                   FROM rpthorarios
                  WHERE rpthorarios.scheduleid = x.scheduleid
                  ORDER BY (rpthorarios.beginhour::text || ' - '::text) || rpthorarios.endhour::text), ', '::text)) || ')'::text) AS horario, 
        x.groupid, 
        x.beginhour, 
        x.endhour, 
        x.occurrencedate, 
        date_part('dow'::text, x.occurrencedate)::integer AS diasemana,
        obterdiaabreviado(date_part('dow'::text, x.occurrencedate)::integer)::text AS dia_semana_abreviado,
        obterDiaExtenso(date_part('dow'::text, x.occurrencedate)::integer) AS dia_semana,
        x.room AS sala,
        x.building AS predio,
        x.physicalresourceid AS codigo_recurso_fisico,
        x.physicalresourceversion AS versao_recurso_fisico,
        x.description AS recurso_fisico,
        (SELECT string_agg(DISTINCT SP.professorId || ' - ' || PPP.name, ', ')
		       FROM acdSchedule S
		 INNER JOIN acdScheduleProfessor SP
		      USING (scheduleId)
            INNER JOIN ONLY basPhysicalPersonProfessor PPP
			 ON PPP.personId = SP.professorId
		      WHERE S.groupId = x.groupid) AS professor
   FROM rpthorarios x);
