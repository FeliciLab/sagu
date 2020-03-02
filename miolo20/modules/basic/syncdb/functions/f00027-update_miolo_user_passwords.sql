--atualiza a senha de todos usuarios do postgres para a senha passada (miolo.conf)
CREATE OR REPLACE FUNCTION update_miolo_user_passwords(p_password TEXT)
RETURNS BOOLEAN AS $body$
DECLARE
    v_user TEXT;
BEGIN
    --cria usuarios que faltam
    FOR v_user IN  SELECT distinct login FROM ( SELECT DISTINCT login, ( SELECT usename FROM pg_catalog.pg_user WHERE usename = login ) AS usename, ( SELECT rolname FROM pg_catalog.pg_roles WHERE rolname = login ) AS rolname FROM miolo_user ) AS foo WHERE usename IS NULL and rolname IS NULL
    LOOP
	IF CHAR_LENGTH(v_user) > 0
	THEN
	        EXECUTE 'CREATE USER "'|| v_user ||'" WITH password '''|| p_password ||''' SUPERUSER IN GROUP miolo_users';
	END IF;
    END LOOP;

    --atualiza senha dos que for necessario
    FOR v_user IN SELECT login FROM miolo_user WHERE login IN (SELECT usename FROM pg_catalog.pg_user)
    LOOP
	IF CHAR_LENGTH(v_user) > 0
	THEN
	        EXECUTE 'ALTER ROLE "' || v_user || '" WITH PASSWORD ''' || p_password || '''';
	END IF;
    END LOOP;

    RETURN True;
END;
$body$ language plpgsql;
