CREATE OR REPLACE FUNCTION verificaseprimeiraparcelafoipaga(p_enrollid integer, p_period varchar)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaseprimeiraparcelafoipaga
  PURPOSE: Retorna TRUE quando primeira parcela foi paga.
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       21/06/2013 Augusto A. Silva  1. Função criada.
**************************************************************************************/
DECLARE
BEGIN

    RETURN EXISTS( SELECT 1
                     FROM acdEnroll A
               INNER JOIN acdContract B
                       ON A.contractId = B.contractId
               INNER JOIN acdGroup C
                       ON A.groupId = C.groupId
               INNER JOIN acdLearningPeriod D
                       ON C.learningPeriodId = D.learningPeriodId
               INNER JOIN finEntry E
                       ON A.contractId = E.contractId
               INNER JOIN finInvoice F
                       ON E.invoiceId = F.invoiceId
                    WHERE A.enrollId = p_enrollId
                      AND F.isCanceled IS FALSE
                      AND F.parcelNumber = 1
                      AND BALANCE(F.invoiceId) = 0
                      AND D.periodId = p_period );
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION verificaseprimeiraparcelafoipaga(integer, varchar)
  OWNER TO postgres;

CREATE OR REPLACE FUNCTION synchronizeperson()
  RETURNS trigger AS
$BODY$
    DECLARE
        v_PASSWORD VARCHAR;
        v_CITY VARCHAR;
        v_COLUMNS VARCHAR;
        v_VALUES VARCHAR;
        v_UPDATE VARCHAR;
        v_COMMAND VARCHAR;
        v_RESULT VARCHAR;
        v_CONFIG VARCHAR;
        v_TMP VARCHAR;

        v_PHONE_CEL VARCHAR;
        v_PHONE_PRO VARCHAR;
        v_PHONE_REC VARCHAR;
        v_PHONE_RES VARCHAR;

        v_REGISTER RECORD;

    BEGIN
        --Trigger para sincronizar as pessoas com o gnuteca

        -- Obtém a configuração da base de dados.
        v_CONFIG := value FROM basConfig WHERE parameter = 'GNUTECA_DATABASE_ADRESS';

        IF ( length(v_CONFIG) > 0 AND v_CONFIG != 'FALSE' )
        THEN
            IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
            THEN
                SELECT INTO v_REGISTER * FROM ONLY basPhysicalPerson WHERE personId = NEW.personId;

                -- Testa se jé existe pessoa na basphysicalperson, caso não tenha, sai da trigger.
                IF v_REGISTER.personId IS NULL
                THEN
                    RETURN NULL;
                END IF;

                v_COLUMNS := '';
                v_VALUES := '';
                v_UPDATE := '';

                -- Ajusta o valor nome.
                IF v_REGISTER.name IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(replace(v_REGISTER.name::text, '''', ''''''), 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'name,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'name = ' || v_TMP;

                -- Ajusta a cidade.
                v_CITY := name FROM basCity WHERE cityid = v_REGISTER.cityid;

                IF v_CITY IS NOT NULL
                THEN
                    v_TMP := '''' || v_CITY || ''',';
                ELSE
                    v_TMP := 'NULL,'; 
                END IF;

                v_COLUMNS := v_COLUMNS || 'city,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'city = '|| v_TMP;

                -- Ajusta o CEP.
                IF v_REGISTER.zipcode IS NOT NULL
                THEN
                    v_TMP := '''' || v_REGISTER.zipcode || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'zipcode,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'zipcode = '|| v_TMP;

                -- Ajusta o local.
                IF v_REGISTER.location IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(replace(v_REGISTER.location::text, '''', ''''''), 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'location,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'location = ' || v_TMP;

                -- Ajusta o número.
                IF v_REGISTER.number IS NOT NULL
                THEN
                    v_TMP := '''' ||v_REGISTER.number|| ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'number,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'number = ' || v_TMP;

                -- Ajusta o complemento.
                IF v_REGISTER.complement IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(replace(v_REGISTER.complement::text, '''', ''''''), 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'complement,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'complement =' || v_TMP;

                -- Ajusta o bairro.
                IF v_REGISTER.neighborhood IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(replace(v_REGISTER.neighborhood::text, '''', ''''''), 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'neighborhood,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'neighborhood = ' || v_TMP;

                -- Ajusta o e-mail.
                IF CHAR_LENGTH(v_REGISTER.email) > 0
                THEN
                    v_TMP := '''' || convert_to(v_REGISTER.email::text, 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'email,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'email = ' || v_TMP;

                -- Ajusta a senha.
                v_PASSWORD:= m_password FROM miolo_user WHERE login =v_REGISTER.miolousername;

                IF v_PASSWORD IS NOT NULL
                THEN
                    --SE PERSON_PASSWORD_ENCRYPT = 1 entao usa md5 no gnuteca.
                    IF (SELECT value FROM dblink (v_CONFIG, 'SELECT value FROM basconfig WHERE PARAMETER = ''PERSON_PASSWORD_ENCRYPT'' ') as foo (value varchar)) = '1'
                    THEN
                        v_TMP := '''' || md5(convert_to(v_PASSWORD::text, 'UTF8')) || ''',';
                    ELSE
                        v_TMP := '''' || convert_to(v_PASSWORD::text, 'UTF8') || ''',';                   
                    END IF;
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'password,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'password = ' || v_TMP;

                -- Ajusta o login.
                IF v_REGISTER.miolousername IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(v_REGISTER.miolousername::text, 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'login,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'login = ' || v_TMP;

                -- Ajusta o sexo.
                IF v_REGISTER.sex IS NOT NULL
                THEN
                    v_TMP := '''' || v_REGISTER.sex || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'sex,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'sex = ' || v_TMP;

                -- Ajusta a data de nascimento.
                IF v_REGISTER.datebirth IS NOT NULL
                THEN
                    v_TMP := '''' || v_REGISTER.datebirth || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'datebirth,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'datebirth = ' || v_TMP;

                -- Ajusta o emprego.
                IF v_REGISTER.workfunction IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(v_REGISTER.workfunction::text, 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'profession,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'profession = ' || v_TMP;

                -- Ajusta o local de trabalho.
                IF v_REGISTER.workemployername IS NOT NULL
                THEN
                    v_TMP := '''' || convert_to(v_REGISTER.workemployername::text, 'UTF8') || ''',';
                ELSE
                    v_TMP := 'NULL,';
                END IF;

                v_COLUMNS := v_COLUMNS || 'workplace,';
                v_VALUES := v_VALUES || v_TMP;
                v_UPDATE := v_UPDATE || 'workplace = '|| v_TMP;

                -- Prepara o comando de inclusão.
                IF TG_OP = 'INSERT'
                THEN
                    IF NEW.personId IS NOT NULL
                    THEN
                        v_COLUMNS := v_COLUMNS || 'personid,';
                        v_VALUES := v_VALUES || '''' || NEW.personId || ''',';
                    END IF;

                    -- Ajusta as colunas e valores retirando a "," no final dos valores.
                    v_COLUMNS := substring(v_COLUMNS FROM 0 FOR length(v_COLUMNS));
                    v_VALUES := substring(v_VALUES FROM 0 FOR length(v_VALUES));

                    v_COMMAND := 'INSERT INTO basperson (' || v_COLUMNS || ') VALUES (' || v_VALUES || ');';
                END IF;

                -- Prepara comando de atualização.
                IF TG_OP = 'UPDATE'
                THEN
                    v_UPDATE := substring(v_UPDATE FROM 0 FOR length(v_UPDATE));
                    v_COMMAND := 'UPDATE basperson SET ' || v_UPDATE || ' WHERE personId = ' || NEW.personId;
                END IF;

                -- Executa o comando na base do Gnuteca.
                SELECT INTO v_RESULT dblink_exec(v_CONFIG, v_COMMAND );

                -- Apaga os telefones.
                SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'DELETE FROM basPhone WHERE personId = ' || NEW.personId );

                -- Insere os telefones.
                v_PHONE_CEL := cellphone FROM ONLY basPhysicalPerson WHERE personId = NEW.personId;
                IF v_PHONE_CEL IS NOT NULL
                THEN
                    SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'INSERT INTO basPhone VALUES (' || NEW.personId || ',''CEL'',''' || v_PHONE_CEL || ''')');
                END IF;

                v_PHONE_PRO := workphone FROM ONLY basPhysicalPerson WHERE personId = NEW.personId;
                IF v_PHONE_PRO IS NOT NULL
                THEN
                    SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'INSERT INTO basPhone VALUES (' || NEW.personId || ',''PRO'',''' || v_PHONE_PRO || ''')');
                END IF;

                v_PHONE_REC:= messagephone FROM ONLY basPhysicalPerson WHERE personId = NEW.personId;
                IF v_PHONE_REC IS NOT NULL
                THEN
                    SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'INSERT INTO basPhone VALUES (' || NEW.personId || ',''REC'',''' || v_PHONE_REC || ''')');
                END IF;

                v_PHONE_RES:= residentialphone FROM ONLY basPhysicalPerson WHERE personId = NEW.personId;
                 IF v_PHONE_RES IS NOT NULL
                THEN
                    SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'INSERT INTO basPhone VALUES (' || NEW.personId || ',''RES'',''' || v_PHONE_RES || ''')');
                END IF;
            ELSE
                -- Apaga os telefones.
                SELECT INTO v_RESULT dblink_exec(v_CONFIG, 'DELETE FROM basPhone WHERE personId = ' || OLD.personId );

                -- Prepara comando para apagar.
                V_COMMAND := 'DELETE FROM basPerson WHERE personId = ' || OLD.personId;

                -- Executa o comando na base do Gnuteca.
                SELECT INTO v_RESULT dblink_exec(v_CONFIG, v_COMMAND );

            END IF;
        END IF;

        RETURN NULL;
    END;
$BODY$ LANGUAGE plpgsql;
