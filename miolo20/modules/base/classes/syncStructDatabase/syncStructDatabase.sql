--
-- ftomasini - Cria Tabela de diferenças caso ela não exista
--
--drop table dbChanges;
CREATE OR REPLACE FUNCTION CriaTabela() RETURNS integer AS $$
DECLARE
foiCriada INTEGER :=0;
tabela RECORD;
BEGIN

SELECT INTO tabela tablename FROM pg_tables where tablename='dbchanges' and schemaname = ANY (current_schemas(true));

IF tabela.tablename IS NULL
THEN
    CREATE TABLE dbChanges
        (changeId SERIAL,
         change TEXT NOT NULL,
         applied BOOLEAN NOT NULL DEFAULT 'f',
         applicationVersion INTEGER,
         orderchange integer,
         error text);

foiCriada = 1;

END IF;
RETURN foiCriada;
END;
$$ LANGUAGE plpgsql;

--
-- ftomasini - Cria tabela se não existe
--
SELECT CriaTabela();



CREATE OR REPLACE FUNCTION applyChanges(p_changes TEXT, p_changeid integer)
RETURNS BOOLEAN AS $body$
BEGIN
    EXECUTE p_changes;

    RETURN TRUE;
    EXCEPTION WHEN OTHERS
    THEN
        UPDATE dbchanges set error = SQLERRM WHERE changeid = p_changeid;

        RETURN FALSE;
    END;

$body$ language plpgsql;



CREATE OR REPLACE FUNCTION syncDataBase(p_applicationversion integer)
RETURNS BOOLEAN AS $body$
DECLARE
    line RECORD;
    result_change BOOLEAN;
BEGIN
    --Percorre comandos que não foram executados e aplica na base
    FOR line IN  SELECT * FROM dbchanges WHERE applied is false AND applicationversion <= p_applicationversion
    LOOP
        SELECT INTO result_change applyChanges(line.change, line.changeid);

        IF result_change IS TRUE
        THEN
            EXECUTE 'UPDATE dbchanges SET applied = true, error = null WHERE changeId = '|| line.changeid || '';
        END IF;

    END LOOP;

    RETURN True;
END;
$body$ language plpgsql;
