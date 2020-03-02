CREATE OR REPLACE FUNCTION trg_atualiza_identificador()
RETURNS TRIGGER AS
$BODY$
BEGIN
    IF NEW.documenttypeid = 2 AND ( GETPARAMETER('BASIC', 'IDENTIFICADOR_PADRAO_PESSOA') = 'CPF' )
    THEN
        UPDATE basphysicalperson SET identifier = new.content WHERE personid = new.personid;
    END IF;

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_atualiza_identificador ON basdocument;
CREATE TRIGGER trg_atualiza_identificador BEFORE UPDATE ON basdocument FOR EACH ROW EXECUTE PROCEDURE trg_atualiza_identificador();

