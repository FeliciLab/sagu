CREATE OR REPLACE FUNCTION obterHoras(p_interval interval)
/*************************************************************************************
  NAME: obterHoras
  PURPOSE: Obtém o número de horas de um intervalo de tempo.
  AUTOR: Bruno Fuhr
**************************************************************************************/
RETURNS double precision AS $BODY$
DECLARE
    v_hours double precision;
    v_minutes double precision;
    v_seconds double precision;
BEGIN

    SELECT INTO v_hours EXTRACT(HOURS FROM p_interval);
    SELECT INTO v_minutes EXTRACT(MINUTES FROM p_interval);
    SELECT INTO v_seconds EXTRACT(SECONDS FROM p_interval);
    
    v_minutes := v_minutes + (v_seconds / 60);
    v_hours := v_hours + (v_minutes / 60);
    
    RETURN v_hours;

END;
$BODY$ language plpgsql;
--
