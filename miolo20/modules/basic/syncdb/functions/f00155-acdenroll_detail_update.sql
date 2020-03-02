CREATE OR REPLACE FUNCTION acdenroll_detail_update()
  RETURNS trigger AS
$BODY$
/*************************************************************************************
  NAME: acdenroll_detail_update
  PURPOSE: Caso a matrícula não tenha estado detalhado, coloca o estado detalhado padrão.
**************************************************************************************/
DECLARE
    v_enrollid integer;
    v_oldDetailEnrollStatusId integer;
BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
    THEN
        v_enrollid := NEW.enrollid;
    END IF;

    IF TG_OP = 'INSERT'
    THEN
        v_oldDetailEnrollStatusId := NULL;
    ELSE
        v_oldDetailEnrollStatusId := OLD.detailenrollstatusid;
    END IF;

    -- Atualiza estado detalhado para o padrão da tabela acddetailenrollstatus
    IF  (NEW.detailenrollstatusid IS NULL)
    AND (v_oldDetailEnrollStatusId IS NULL)
    AND ((SELECT detailenrollstatusid FROM acdenroll WHERE enrollid = v_enrollid) is NULL) 
    AND ((SELECT COUNT(*) FROM acddetailenrollstatus WHERE parentstatus = NEW.statusid ) > 0 )
        THEN
        NEW.detailenrollstatusid = (
        SELECT detailenrollstatusid 
        FROM acddetailenrollstatus 
        WHERE defaultstatus = TRUE
        AND parentstatus = NEW.statusid
        );
    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
