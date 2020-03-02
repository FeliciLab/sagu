CREATE OR REPLACE FUNCTION atualizaVinculoProfessor()
RETURNS TRIGGER AS
$BODY$
/*************************************************************************************
  NAME: atualizavinculoprofessor
  PURPOSE: Insere ou exclui o vinculo
**************************************************************************************/
DECLARE
    v_date DATE;
BEGIN
    IF TG_OP = 'DELETE'
    THEN
        DELETE FROM basPersonLink WHERE personId = OLD.personId AND linkId = GETPARAMETER('BASIC', 'PERSON_LINK_PROFESSOR')::int;
        RETURN OLD;
    END IF;
    
    IF TG_OP = 'INSERT'
    THEN
        BEGIN
            IF NEW.enddate IS NOT NULL
            THEN
                v_date := NEW.enddate;
            END IF;
            EXCEPTION WHEN OTHERS THEN
        END;
        INSERT INTO basPersonLink (personId, linkId, datevalidate) VALUES (NEW.personId, GETPARAMETER('BASIC', 'PERSON_LINK_PROFESSOR')::int, v_date);
        RETURN NEW;
    END IF;
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_atualizavinculoprofessor ON basprofessorcommitment;
CREATE TRIGGER trg_atualizavinculoprofessor AFTER INSERT OR DELETE ON basprofessorcommitment FOR EACH ROW EXECUTE PROCEDURE atualizavinculoprofessor();

