CREATE OR REPLACE FUNCTION miolo_audit_cache_primary_key(p_schema text, p_table text) RETURNS VARCHAR[] AS $body$
/*************************************************************************************************************
  NAME: miolo_audit_cache_primary_key
  PURPOSE: Cria cache das chaves primárias das tabelas. Isso torna mais rápida a busca da miolo_audit
  
  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ----------------- ------------------------------------
  1.0       -          Jamiel Spezia             1. Função criada.
  **************************************************************************************************************/
DECLARE
    v_pkeys VARCHAR[];
BEGIN
    SELECT INTO v_pkeys array_agg(column_name::text) AS column_name FROM obtemChavesPrimariasDaTabela(p_table, p_schema);

    IF v_pkeys IS NOT NULL
    THEN
        EXECUTE 'CREATE OR REPLACE FUNCTION miolo_audit_' || p_schema || '_' || p_table || E'_pkeys()
                 RETURNS VARCHAR[] AS
                 \$body\$
                 DECLARE
                     v_pkeys VARCHAR[] := ARRAY[' || replace(replace(replace(v_pkeys::text, '{', ''''), '}', ''''), ',', ''',''') || E'];
                 BEGIN
                     RETURN v_pkeys;
                 END;
                 \$body\$
                 LANGUAGE plpgsql
                 IMMUTABLE ';
    END IF;
             
    RETURN v_pkeys;
END;
$body$ language plpgsql