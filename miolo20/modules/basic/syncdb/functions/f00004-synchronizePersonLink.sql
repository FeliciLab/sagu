-- Trigger de sincronização de vínculos
CREATE OR REPLACE FUNCTION synchronizePersonLink() RETURNS trigger
LANGUAGE plpgsql AS 
$BODY$
    DECLARE
        v_VALIDATE DATE;
        v_COMMAND VARCHAR;
        v_RESULT VARCHAR;
        v_CONFIG VARCHAR;

    BEGIN
        -- Obtém a configuração da base de dados.
        v_CONFIG := value FROM basConfig WHERE parameter = 'GNUTECA_DATABASE_ADRESS';
        
        IF ( length(v_CONFIG) > 0 ) AND ( UPPER(v_CONFIG) <> 'FALSE' )
        THEN
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
            THEN
                IF NEW.datevalidate IS NOT NULL
                THEN
                    v_VALIDATE := NEW.datevalidate;
                ELSE
                    -- SOMA 50 anos na data atual caso não tenha data de validade.
                    v_VALIDATE := (now() + interval '50 year')::date;
                END IF;
            END IF;

            IF TG_OP = 'INSERT'
            THEN
                v_COMMAND := 'INSERT INTO basPersonLink ( personId,
                                                          linkId,
                                                          datevalidate )
                                             VALUES (' || NEW.personId ||','
                                                       || NEW.linkId || ',''' 
                                                       || v_VALIDATE || ''');';
            ELSE 
                IF TG_OP = 'UPDATE'
                THEN
                    v_COMMAND := 'UPDATE basPersonLink 
                                     SET dateValidate = ''' || v_VALIDATE || '''
                                   WHERE personId = ' || OLD.personId || '
                                     AND linkId = ' || OLD.linkId || ';';
                ELSE
                    v_COMMAND := 'DELETE FROM basPersonLink WHERE personId = ' || OLD.personId || '
                                                              AND linkId = ' || OLD.linkId || ';';
                END IF;
            END IF;
                                                      
            -- Executa o comando na base do Gnuteca.
            SELECT INTO v_RESULT dblink_exec(v_CONFIG, v_COMMAND );
        END IF;
        
        RETURN NULL;
    END;
$BODY$;

