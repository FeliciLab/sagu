CREATE OR REPLACE VIEW cr_acd_disciplina_oferecida_horario_semanal AS (
    SELECT DISTINCT groupid AS codigo_oferecida,
		    dia_semana,
		    dia_semana_abreviado AS dia_semana_abreviado,
		    diasemana AS cod_dia_semana,
		    beginhour AS horario_inicial,
		    endhour AS horiario_final,
		    sala,
		    predio,
		    codigo_recurso_fisico,
		    versao_recurso_fisico,
		    recurso_fisico,
		    professor
	       FROM rpthorariodias
);
