CREATE OR REPLACE VIEW cr_acd_disciplina_oferecida_horario_data AS (
    SELECT DISTINCT groupid AS codigo_oferecida,
		    occurrencedate AS data,
		    beginhour AS horario_inicial,
		    endhour AS horario_final,
		    predio,
		    sala,
		    recurso_fisico,
		    codigo_recurso_fisico,
		    versao_recurso_fisico,
		    professor
	       FROM rpthorariodias
);
