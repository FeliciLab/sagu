CREATE OR REPLACE FUNCTION miolo_audit_it() RETURNS TRIGGER AS $body$
/*************************************************************************************************************
  NAME: miolo_audit_it
  PURPOSE: Função que permite auditoria de tabelas.
  
  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ----------------- ------------------------------------
  1.0       -          Daniel Hartmann           1. Função criada.
  2.0     27/11/2013   Nataniel Ingor da Silva   2. Função adaptada para utilizar uma outra base
                                                    para salvar os dados de auditoria, caso esteja
                                                    configurado o parâmetro MIOLO_AUDIT_DATABASE(dblink).
  3.0     13/05/2014   Augusto A. Silva          3. Feitas automatizações no processo de auditoria.
**************************************************************************************************************/
DECLARE
    v_original_value TEXT;
    v_new_value TEXT;
    v_col TEXT;
    v_audit_id INT;
    v_do_insert BOOLEAN;
    v_ispk BOOLEAN;
    v_path_dblink TEXT;
    v_dblink TEXT;
    v_is_dblink BOOLEAN = FALSE;
    v_current_query TEXT;
    v_next_val INTEGER;
    v_dblink_extension BOOLEAN = FALSE;
    v_database TEXT;
    v_pkeys VARCHAR[];
    v_pkey TEXT;
    v_versao TEXT;
BEGIN
    v_original_value := NULL;
    v_new_value := '-';
    v_next_val := nextval('miolo_audit_audit_id_seq');

    v_versao := versaoSagu FROM basHistoricoAtualizacao ORDER BY datetime DESC LIMIT 1; 
  
    IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
        INSERT INTO miolo_audit 
                    (audit_id, schema_name, table_name, user_name, action, query, versaosistema)
            VALUES (v_next_val, TG_TABLE_SCHEMA::TEXT, TG_TABLE_NAME::TEXT, session_user::TEXT, TG_OP, current_query(), v_versao);
    END IF;
           
    -- Obtém array de chaves primárias da tabela.
    BEGIN
        EXECUTE 'SELECT miolo_audit_' || TG_TABLE_SCHEMA || '_' || TG_TABLE_NAME || '_pkeys()' 
           INTO v_pkeys;
    EXCEPTION WHEN undefined_function
    THEN
        SELECT INTO v_pkeys miolo_audit_cache_primary_key(TG_TABLE_SCHEMA, TG_TABLE_NAME);
    END;
   
    -- Percorre todas as colunas da tabela.
    FOR v_col IN SELECT column_name
                   FROM information_schema.columns 
                  WHERE table_name = TG_TABLE_NAME
                    AND table_schema = TG_TABLE_SCHEMA
               ORDER BY ordinal_position
    LOOP
        v_ispk := FALSE;
        v_do_insert := TRUE;

        -- Identifica se o valor da coluna foi alterado, caso sim, deverá registrar nos detalhes da auditoria.
        IF TG_OP = 'DELETE' OR TG_OP = 'UPDATE' THEN
            EXECUTE 'SELECT "' || v_col || '" FROM (SELECT $1.*) AS table_old' INTO v_original_value USING OLD;
        END IF;

        IF TG_OP = 'UPDATE' THEN
            EXECUTE 'SELECT "' || v_col || '" FROM (SELECT $1.*) AS table_new' INTO v_new_value USING NEW;

            IF TG_OP = 'UPDATE' AND (v_original_value = v_new_value OR (v_original_value IS NULL AND v_new_value IS NULL)) 
            THEN
                v_do_insert := FALSE;
            END IF;

        END IF;

	-- Aqui está sendo forçada a inserção da chave primária, independente se foi alterada ou não.
        FOR v_pkey IN ( SELECT vt FROM unnest(v_pkeys) x(vt) )
        LOOP
            IF v_pkey = v_col
            THEN
                v_ispk := TRUE;
                v_do_insert := TRUE;
            END IF;
        END LOOP;

        IF v_do_insert THEN
            INSERT INTO miolo_audit_detail
                        (audit_id, column_name, original_value, new_value, is_pkey)
                VALUES (v_next_val, v_col, v_original_value, v_new_value, v_ispk);
        END IF;
        
    END LOOP;

    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
    THEN
        RETURN NEW;
    ELSE
        RETURN OLD;
    END IF;
END;
$body$ language plpgsql security definer;

