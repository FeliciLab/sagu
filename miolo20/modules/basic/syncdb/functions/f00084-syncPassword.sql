
CREATE OR REPLACE FUNCTION syncPassword()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: syncphones
  DESCRIPTION: sincroniza o password entre a basperson e a miolo_user.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       31/08/2012 Jonas Guilherme Dahmer   1. Trigger criada.
******************************************************************************/
BEGIN
    IF TG_OP = 'UPDATE' THEN
        IF TG_TABLE_NAME = 'basperson' THEN 
            IF OLD.password != NEW.password THEN
                    UPDATE miolo_user SET m_password = NEW.password WHERE login = NEW.miolousername;
            END IF;
        END IF;
        IF TG_TABLE_NAME = 'miolo_user' THEN
            IF OLD.m_password != NEW.m_password THEN
                    UPDATE basperson SET password = NEW.m_password WHERE miolousername = NEW.login;
            END IF;	
        END IF;
    END IF;
 RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql;
