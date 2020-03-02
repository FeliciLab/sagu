CREATE OR REPLACE FUNCTION synchronizePasswordOfPerson() RETURNS trigger
LANGUAGE plpgsql AS
$BODY$
    DECLARE
        v_RESULT VARCHAR;
        v_CONFIG VARCHAR;
        v_PASSWORD VARCHAR;
        v_PERSON INT;
    BEGIN
        --Trigger para sincronização da senha do usuário do sagu com o do gnuteca
        -- Obtém a configuração da base de dados.
        v_CONFIG := value FROM basConfig WHERE parameter = 'GNUTECA_DATABASE_ADRESS';
        v_PERSON := personId FROM ONLY basPhysicalPerson WHERE miolousername = NEW.login AND miolousername IS NOT NULL LIMIT 1;

        IF (length(v_CONFIG) > 0) AND (v_PERSON IS NOT NULL) AND ( v_CONFIG != 'FALSE' )
        THEN

            v_PASSWORD := NEW.m_password;

            --SE PERSON_PASSWORD_ENCRYPT = 1 entao usa md5 no gnuteca.
            IF (SELECT value FROM dblink (v_CONFIG, 'SELECT value FROM basconfig WHERE PARAMETER = ''PERSON_PASSWORD_ENCRYPT'' ') as foo (value varchar)) = '1'
            THEN
                v_PASSWORD := md5(v_PASSWORD::text);
            END IF;            

            -- Executa o comando na base do Gnuteca.
            SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'UPDATE basPerson SET password = ''' || v_PASSWORD || '''
                                                             WHERE personId = ' || V_PERSON || ';' );

            SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'UPDATE basPerson SET login = ''' || NEW.login || '''
                                                             WHERE personId = ' || V_PERSON || ';' );
        END IF;
	
        RETURN NULL;
    END;
$BODY$;
