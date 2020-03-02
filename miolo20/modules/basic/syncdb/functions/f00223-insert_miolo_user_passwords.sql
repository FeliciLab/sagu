--Insere usuários do sagu no postgres
CREATE OR REPLACE FUNCTION insert_miolo_user_passwords(p_user TEXT, p_password TEXT)
RETURNS BOOLEAN AS $body$
DECLARE

BEGIN
    BEGIN

        -- Inserir grupo 'miolo_users' se não existir
        IF NOT EXISTS (
          SELECT *
          FROM   pg_catalog.pg_group
          WHERE groname = 'miolo_users') THEN

          CREATE GROUP miolo_users WITH SUPERUSER;
       END IF;

        EXECUTE 'CREATE USER "'|| p_user ||'" WITH password '''|| p_password ||''' SUPERUSER IN GROUP miolo_users';

        EXCEPTION
            WHEN OTHERS THEN
                --RAISE NOTICE 'Não foi possível inserir o usuário % no postgres. Erro: %', p_user, SQLERRM;
                RETURN FALSE;
    END;

    RETURN TRUE;
END;
$body$ language plpgsql;
