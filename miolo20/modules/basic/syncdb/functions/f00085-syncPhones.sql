CREATE OR REPLACE FUNCTION syncPhones()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: syncphones
  DESCRIPTION: sincroniza os telefones entre a basphysicalperson e a basphone.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       27/08/2012 Jonas Guilherme Dahmer   1. Trigger criada.
******************************************************************************/
DECLARE
v_phone RECORD;
BEGIN

    IF TG_TABLE_NAME = 'basphysicalperson' THEN

        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN

            IF NEW.residentialphone IS NOT NULL THEN

                IF TG_OP = 'UPDATE' THEN
                    IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'RES' ) THEN
                        UPDATE basphone SET phone = NEW.residentialphone WHERE personid = NEW.personid AND type = 'RES' AND phone = OLD.residentialphone;
                    ELSE
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'RES', NEW.residentialphone);
                    END IF;

                ELSEIF TG_OP = 'INSERT' THEN

                
                    IF NOT EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'RES' AND phone = NEW.residentialphone ) THEN
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'RES', NEW.residentialphone);
                    END IF;
                END IF;

            ELSE
                IF TG_OP = 'UPDATE' THEN
                    IF OLD.residentialphone IS NOT NULL THEN
                        IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'RES' AND phone = OLD.residentialphone) THEN
                            DELETE FROM basphone WHERE personid = NEW.personid AND type = 'RES' AND phone = OLD.residentialphone;
                        END IF;
                    END IF;
                END IF;
            END IF;
            
            IF NEW.workphone IS NOT NULL THEN

                IF TG_OP = 'UPDATE' THEN
                    IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'PRO' ) THEN
                        UPDATE basphone SET phone = NEW.workphone WHERE personid = NEW.personid AND type = 'PRO' AND phone = OLD.workphone;
                    ELSE
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'PRO', NEW.workphone);                            
                    END IF;
                ELSE IF TG_OP = 'INSERT' THEN
                    IF NOT EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'PRO' AND phone = NEW.workphone ) THEN
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'PRO', NEW.workphone);                            
                    END IF;
                END IF;
            END IF;

            ELSE
                IF TG_OP = 'UPDATE' THEN
                    IF OLD.workphone IS NOT NULL THEN
                        IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'PRO' AND phone = OLD.workphone) THEN
                            DELETE FROM basphone WHERE personid = NEW.personid AND type = 'PRO' AND phone = OLD.workphone;
                        END IF;
                    END IF;
                END IF;
            END IF;

            IF NEW.cellphone IS NOT NULL THEN
                IF TG_OP = 'UPDATE' THEN
                    IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'CEL' ) THEN
                        UPDATE basphone SET phone = NEW.cellphone WHERE personid = NEW.personid AND type = 'CEL' AND phone = OLD.cellphone;
                    ELSE
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'CEL', NEW.cellphone);
                    END IF;

                ELSEIF TG_OP = 'INSERT' THEN
                    IF NOT EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'CEL' AND phone = NEW.cellphone ) THEN                        
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'CEL', NEW.cellphone);                        
                    END IF;
                END IF;

            ELSE
                IF TG_OP = 'UPDATE' THEN
                    IF OLD.cellphone IS NOT NULL THEN       
                        IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'CEL' AND phone = OLD.cellphone) THEN
                            DELETE FROM basphone WHERE personid = NEW.personid AND type = 'CEL' AND phone = OLD.cellphone;
                        END IF;
                    END IF;        
                END IF;
            END IF;
            
            IF NEW.messagephone IS NOT NULL THEN

                IF TG_OP = 'UPDATE' THEN
                    IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'REC' ) THEN
                        UPDATE basphone SET phone = NEW.messagephone WHERE personid = NEW.personid AND type = 'REC' AND phone = OLD.messagephone;
                    ELSE
                        INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'REC', NEW.messagephone);
                    END IF;

                ELSEIF TG_OP = 'INSERT' THEN
                    IF NOT EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'REC' AND phone = NEW.messagephone ) THEN
                        IF NEW.cellphone IS NOT NULL THEN
                            INSERT INTO basphone (personid, type, phone) VALUES (NEW.personid, 'REC', NEW.messagephone);
                        END IF;
                    END IF;
                END IF;

            ELSE
                IF TG_OP = 'UPDATE' THEN
                    IF OLD.messagephone IS NOT NULL THEN
                        IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = NEW.personid AND type = 'REC' AND phone = OLD.messagephone) THEN
                            DELETE FROM basphone WHERE personid = NEW.personid AND type = 'REC' AND phone = OLD.messagephone;
                        END IF;
                    END IF;
                END IF;
            END IF;
            
        END IF;
        
        IF TG_OP = 'DELETE' THEN
            IF EXISTS ( SELECT phoneid FROM basphone WHERE personid = OLD.personid ) THEN
                DELETE FROM basphone WHERE personid = OLD.personid;
            END IF;
        END IF;
    
    END IF;
    
    
    IF TG_TABLE_NAME = 'basphone' THEN

        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
        
            IF NEW.type = 'RES' THEN
                IF NOT EXISTS ( SELECT 1 FROM basphysicalperson WHERE personid = NEW.personid AND residentialphone = NEW.phone )
                THEN
                    UPDATE basphysicalperson SET residentialphone = NEW.phone WHERE personid = NEW.personid;
                END IF;
            END IF;
            
            IF NEW.type = 'PRO' THEN
                IF NOT EXISTS ( SELECT 1 FROM basphysicalperson WHERE personid = NEW.personid AND workphone = NEW.phone )
                THEN
                    UPDATE basphysicalperson SET workphone = NEW.phone WHERE personid = NEW.personid;
                END IF;
            END IF;
            
            IF NEW.type = 'CEL' THEN
                IF NOT EXISTS ( SELECT 1 FROM basphysicalperson WHERE personid = NEW.personid AND cellphone = NEW.phone )
                THEN
                    UPDATE basphysicalperson SET cellphone = NEW.phone WHERE personid = NEW.personid;
                END IF;
            END IF;
            
            IF NEW.type = 'REC' THEN
                IF NOT EXISTS ( SELECT 1 FROM basphysicalperson WHERE personid = NEW.personid AND messagephone = NEW.phone )
                THEN
                    UPDATE basphysicalperson SET messagephone = NEW.phone WHERE personid = NEW.personid;
                END IF;
            END IF;
        
        END IF;
        
        IF TG_OP = 'DELETE' THEN

            IF OLD.type = 'RES' THEN
                UPDATE basphysicalperson SET residentialphone = '' WHERE personid = OLD.personid;
            END IF;
            
            IF OLD.type = 'PRO' THEN
                UPDATE basphysicalperson SET workphone = '' WHERE personid = OLD.personid;
            END IF;
            
            IF OLD.type = 'CEL' THEN
                UPDATE basphysicalperson SET cellphone = '' WHERE personid = OLD.personid;
            END IF;
            
            IF OLD.type = 'REC' THEN
                UPDATE basphysicalperson SET messagephone = '' WHERE personid = OLD.personid;
            END IF;
        
        END IF;
    
    END IF;

 RETURN NEW;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS syncphones_tkey ON basphone;
CREATE TRIGGER syncphones_tkey
  AFTER INSERT OR UPDATE
  ON basphone
  FOR EACH ROW
  EXECUTE PROCEDURE syncphones();
