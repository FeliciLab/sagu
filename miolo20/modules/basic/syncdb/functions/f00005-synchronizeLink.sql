CREATE OR REPLACE FUNCTION synchronizeLink() RETURNS trigger
LANGUAGE plpgsql AS
$BODY$
    DECLARE
        v_LEVEL VARCHAR;
        v_COMMAND VARCHAR;
        v_RESULT VARCHAR;
        v_CONFIG VARCHAR;

    BEGIN
        -- Obtém a configuração da base de dados.
        v_CONFIG := value FROM basConfig WHERE parameter = 'GNUTECA_DATABASE_ADRESS';

        IF ( length(v_CONFIG) > 0 AND v_CONFIG != 'FALSE' )
        THEN
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
            THEN
                IF NEW.level IS NOT NULL
                THEN
                    v_LEVEL := '''' || NEW.level || '''';
                ELSE
                    v_LEVEL := 'NULL';
                END IF;
            END IF;

            IF TG_OP = 'INSERT'
            THEN
                v_COMMAND := 'INSERT INTO basLink ( linkId,
                                                    description,
                                                    level,
                                                    isVisibleToPerson )
                                             VALUES (' || NEW.linkId ||','''
                                                       || convert_to(NEW.description::text, 'LATIN1') || ''','
                                                       || v_LEVEL || ',
                                                       true);';
            ELSE
                IF TG_OP = 'UPDATE'
                THEN
                    v_COMMAND := 'UPDATE basLink
                                     SET description = ''' || NEW.description || ''',
                                               level = ' || v_LEVEL || '
                                   WHERE linkId = ' || OLD.linkId || ';';
                ELSE
                    v_COMMAND := 'DELETE FROM basLink WHERE linkId = ' || OLD.linkId || ';';
                END IF;
            END IF;

            -- Executa o comando na base do Gnuteca.
            SELECT INTO v_RESULT dblink_exec(v_CONFIG, v_COMMAND );
        END IF;

        RETURN NULL;
    END;
$BODY$;
