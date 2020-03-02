CREATE OR REPLACE FUNCTION createRoleIfNotExists(p_rolename TEXT) 
RETURNS TEXT AS
$$
/******************************************************************************
  NAME: createRoleIfNotExists
  DESCRIPTION: Verifica se já existe o usuário no postgres, se não cria

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/04/2015   Nataniel I. da Silva         Função criada.
******************************************************************************/
BEGIN
    IF NOT EXISTS (SELECT * FROM pg_roles WHERE rolname = p_rolename) THEN
        EXECUTE format('CREATE ROLE %I SUPERUSER LOGIN', p_rolename);
        RETURN 'CREATE ROLE';
    ELSE
        RETURN format('ROLE ''%I'' ALREADY EXISTS', p_rolename);
    END IF;
END;
$$
LANGUAGE plpgsql;
