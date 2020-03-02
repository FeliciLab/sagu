CREATE OR REPLACE FUNCTION gerarUsuarioComPermissaoDeLeitura(v_usuario VARCHAR)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: gerarUsuarioComPermissaoDeLeitura
  DESCRIPTION: Função que cria um user no postgres com permissão apenas de leitura.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       15/05/15   Nataniel I. da Silva  1. Função criada.
******************************************************************************/

DECLARE
    p_tabelas RECORD;
    p_tabela VARCHAR;
BEGIN
    BEGIN
        IF ( SELECT COUNT(*) > 0 FROM pg_roles WHERE rolname LIKE v_usuario ) IS FALSE
        THEN 
            EXECUTE 'CREATE ROLE ' || v_usuario;
        END IF;

    EXCEPTION WHEN others THEN
        RAISE EXCEPTION ' % ', SQLERRM;
    END;

    FOR p_tabelas IN ( SELECT relname as tabela, 
                              nspname as _schema 
                         FROM pg_class 
                         JOIN pg_namespace 
                           ON pg_namespace.oid = pg_class.relnamespace  
                        WHERE relkind IN ('r', 'v') -- tabelas e views
                          AND pg_namespace.nspname NOT IN ('pg_catalog', 'pg_toast', 'information_schema')
                     ORDER BY 2,1 )
    LOOP 
        p_tabela := p_tabelas._schema || '.' || p_tabelas.tabela;

        EXECUTE 'REVOKE ALL ON '  || p_tabela || ' FROM ' || v_usuario;
        EXECUTE 'GRANT USAGE ON SCHEMA ' || p_tabelas._schema || ' TO ' || v_usuario;
        EXECUTE 'GRANT SELECT ON ' || p_tabela || ' TO ' || v_usuario;
    END LOOP;
    
    EXECUTE 'ALTER ROLE ' || v_usuario || ' NOSUPERUSER';

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;

