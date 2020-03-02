-- Criaç?o das triggers para controle da ocorrencia de curso
CREATE OR REPLACE FUNCTION acdEnrollConfig_checkEnroll() RETURNS TRIGGER AS $acdenrollconfig_checkEnroll$
DECLARE
    _enrollconfigid INTEGER;

BEGIN
    SELECT INTO _enrollconfigid enrollconfigid 
	   FROM acdenrollconfig 
	  WHERE courseid = new.courseid 
	    AND courseversion = new.courseversion 
	    AND turnid=new.turnid 
	    AND unitid=new.unitid 
	    AND ( SELECT ( new.begindate, COALESCE(new.enddate, NOW()::DATE)::DATE) 
	        OVERLAPS ( COALESCE(begindate, '1900-01-01'::DATE)::DATE, COALESCE(enddate, '3000-01-01'::DATE)::DATE ) ) 
	    AND new.enrollconfigid != enrollconfigid;

    IF FOUND THEN
        RAISE EXCEPTION 'Este curso possui uma configuraç?o ativa.';
    END IF;

    IF GETPARAMETER('BASIC', 'ATIVAR_MULTIUNIDADE') <> 't' THEN
        SELECT INTO _enrollconfigid enrollconfigid 
               FROM acdenrollconfig 
	      WHERE courseid IS NULL 
	        AND ( SELECT (new.begindate, COALESCE(new.enddate, NOW()::DATE)::DATE) 
	            OVERLAPS ( COALESCE(begindate, '1900-01-01'::DATE)::DATE, COALESCE(enddate, '3000-01-01'::DATE)::DATE ) ) 
	        AND new.enrollconfigid != enrollconfigid 
                AND new.courseid IS NULL;
    ELSE
        SELECT INTO _enrollconfigid enrollconfigid 
               FROM acdenrollconfig 
	      WHERE courseid IS NULL 
	        AND ( SELECT (new.begindate, COALESCE(new.enddate, NOW()::DATE)::DATE) 
	            OVERLAPS ( COALESCE(begindate, '1900-01-01'::DATE)::DATE, COALESCE(enddate, '3000-01-01'::DATE)::DATE ) ) 
	        AND new.enrollconfigid != enrollconfigid 
                AND new.courseid IS NULL
                AND unitid = obterunidadelogada();
    END IF;

    IF FOUND THEN
        RAISE EXCEPTION 'Já existe uma configuraç?o ativa para este intervalo de datas.';
    END IF;

    RETURN NEW;

END 
$acdenrollconfig_checkEnroll$ LANGUAGE PLPGSQL;
