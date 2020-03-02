CREATE OR REPLACE FUNCTION atualizaVinculoFuncionario()
RETURNS TRIGGER AS
$BODY$
/*************************************************************************************
  NAME: atualizavinculofuncionario
  PURPOSE: Insere ou exclui o vinculo
**************************************************************************************/
BEGIN
    IF TG_OP = 'DELETE'
    THEN
        UPDATE basPersonLink SET dateValidate = NOW()::date WHERE personId = OLD.personId;
        RETURN OLD;
    ELSE
        INSERT INTO basPersonLink (personId, linkId) VALUES (NEW.personId, GETPARAMETER('BASIC', 'PERSON_LINK_EMPLOYEE')::int);
        RETURN NEW;
    END IF;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_atualizavinculofuncionario ON basphysicalpersonemployee;
CREATE TRIGGER trg_atualizavinculofuncionario AFTER INSERT OR DELETE ON basphysicalpersonemployee FOR EACH ROW EXECUTE PROCEDURE atualizavinculofuncionario();

