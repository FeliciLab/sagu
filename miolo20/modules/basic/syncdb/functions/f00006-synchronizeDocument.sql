CREATE OR REPLACE FUNCTION synchronizeDocument() RETURNS trigger
LANGUAGE plpgsql AS
$BODY$
    DECLARE
        v_DOCUMENTTYPE VARCHAR;
        v_CONTENT TEXT;
        v_ORGAN VARCHAR;
        v_DATEEXPEDITION VARCHAR;
        v_OBSERVATION TEXT;

        v_COMMAND VARCHAR;
        v_RESULT VARCHAR;
        v_CONFIG VARCHAR;

        v_EQUIV VARCHAR[]:= '{}';

    BEGIN
        -- Trigger de sincronização de documentos

        -- Obtém a configuração da base de dados.
        v_CONFIG := value FROM basConfig WHERE parameter = 'GNUTECA_DATABASE_ADRESS';

        IF ( length(v_CONFIG) > 0 AND v_CONFIG != 'FALSE' )
        THEN
            -- Equivalência no tipo de documento entre Gnuteca e Sagu.
            v_EQUIV[1] := 'RG';
            v_EQUIV[2] := 'CPF';
            v_EQUIV[3] := 'HISTORICO_ESCOLAR';
            v_EQUIV[4] := 'TITULO_ELEITOR';
            v_EQUIV[5] := 'QUITACAO_ELEITORAL';
            v_EQUIV[6] := 'DOCUMENTACAO_MILITAR';
            v_EQUIV[7] := 'FOTO';
            v_EQUIV[8] := 'HISTORICO_ORIGINAL';
            v_EQUIV[9] := 'ATESTADO_MEDICO';
            v_EQUIV[10] := 'DIPLOMA_AUTENTICADO';
            v_EQUIV[11] := 'SOLTEIRO_EMANCIPADO';

            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
            THEN
                IF NEW.content IS NOT NULL
                THEN
                    v_CONTENT := '''' || NEW.content || '''';
                ELSE
                    v_CONTENT := ''' ''';
                END IF;

                IF NEW.organ IS NOT NULL
                THEN
                    v_ORGAN := '''' || NEW.organ || '''';
               ELSE
                    v_ORGAN := 'NULL';
               END IF;

                IF NEW.dateexpedition IS NOT NULL
                THEN
                    v_DATEEXPEDITION := '''' || NEW.dateexpedition || '''';
                ELSE
                    v_DATEEXPEDITION := 'NULL';
                END IF;

                IF NEW.obs IS NOT NULL
                THEN
                    v_OBSERVATION := '''' || NEW.obs || '''';
                ELSE
                    v_OBSERVATION := 'NULL';
                END IF;

                -- Obtém o tipo de documento.
                v_DOCUMENTTYPE := '''' || v_EQUIV[NEW.documenttypeid] || '''';
            END IF;

            IF TG_OP = 'INSERT'
            THEN
                v_COMMAND := 'INSERT INTO basDocument ( personId,
                                                        documenttypeid,
                                                        content,
                                                        organ,
                                                        dateexpedition,
                                                        observation )
                                             VALUES (' || NEW.personId ||','
                                                       || v_DOCUMENTTYPE || ','
                                                       || v_CONTENT || ','
                                                       || V_ORGAN || ','
                                                       || v_DATEEXPEDITION || ','
                                                       || v_OBSERVATION || ');';
            ELSE
                IF TG_OP = 'UPDATE'
                THEN
                    v_COMMAND := 'UPDATE basDocument
                                     SET documenttypeid = ' || v_DOCUMENTTYPE || ',
                                         content = ' || v_CONTENT || ',
                                         organ = ' || v_ORGAN || ',
                                         dateexpedition = ' || v_DATEEXPEDITION || ',
                                         observation = ' || v_OBSERVATION || '
                                   WHERE personId = ' || OLD.personId || '
                                     AND documenttypeid = ' || v_DOCUMENTTYPE || ';';
                ELSE
                    -- Obtém o tipo de documento.
                    v_DOCUMENTTYPE := '''' || v_EQUIV[OLD.documenttypeid] || '''';

                    v_COMMAND := 'DELETE FROM basDocument WHERE personId = ' || OLD.personId || '
                                                            AND documenttypeid = ' || v_DOCUMENTTYPE || ';';
                END IF;
            END IF;

            -- Executa o comando na base do Gnuteca.
            SELECT INTO v_RESULT dblink_exec(v_CONFIG, v_COMMAND );
        END IF;

        RETURN NULL;
    END;
$BODY$;
