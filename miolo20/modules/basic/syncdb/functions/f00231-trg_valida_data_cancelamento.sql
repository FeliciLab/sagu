CREATE OR REPLACE FUNCTION trg_valida_data_cancelamento()
  RETURNS trigger AS
$BODY$
/***************************************************************
  PURPOSE: Se uma matrícula que está cancelada, é editada para outro status diferente de CANCELADA,
	   apaga a data de cancelamento da matrícula. 
           Trigger criada para resolução ticket #28523.
****************************************************************/
DECLARE    
    
BEGIN
    IF OLD.datecancellation IS NOT NULL 
    THEN
        IF NEW.statusid != OLD.statusid 
        THEN
            IF OLD.statusid = getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int
            THEN
                UPDATE acdEnroll
                   SET datecancellation = NULL
                 WHERE enrollid = OLD.enrollid;
            END IF;
        END IF;
    END IF;
    
    RETURN NEW;	 
END;
$BODY$
  LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS valida_data_cancelamento ON acdenroll;
CREATE TRIGGER valida_data_cancelamento AFTER UPDATE ON acdenroll
    FOR EACH ROW EXECUTE PROCEDURE trg_valida_data_cancelamento();
