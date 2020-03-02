CREATE OR REPLACE FUNCTION createIndexToForeignKey()
RETURNS BOOLEAN AS $body$
DECLARE
    line RECORD;
    result_change BOOLEAN;
/*************************************************************************************
  NAME: createIndexToForeignKey
  PURPOSE: Cria indices para as foreign key que ainda não possuem
           Ex.: 
           Consulta: SELECT * from createIndexToForeignKey();
           Retorno: true
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       29/04/2015 Jamiel Spezia     1. Função criada.
**************************************************************************************/
BEGIN
    FOR line IN SELECT DISTINCT 'CREATE INDEX idx_'
                                || CASE WHEN tc.constraint_schema = 'public'
                                        THEN ''
                                        ELSE tc.constraint_schema
                                   END
                                || CASE WHEN length(tc.table_name) > 33 THEN md5(tc.table_name) ELSE tc.table_name END
                                || '_'
                                || kcu.column_name
                                || ' ON '
                                || tc.constraint_schema
                                || '.'
                                || tc.table_name
                                || '('
                                || kcu.column_name
                                || ')' as comando
                                --tc.constraint_schema, tc.constraint_name, tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_nam
                           FROM information_schema.table_constraints AS tc
                           JOIN information_schema.key_column_usage AS kcu
                             ON tc.constraint_name = kcu.constraint_name
                           JOIN information_schema.constraint_column_usage AS ccu
                             ON ccu.constraint_name = tc.constraint_name
                          WHERE constraint_type = 'FOREIGN KEY' --Lista todas as foreign key
                            --AND (tc.table_name like 'acdalunoprocessadonaclassificacaodadisciplina' or tc.table_name like 'turma') --Especificamente de uma tabela :-)
                            AND ( SELECT count(*) >0
                                    FROM pg_catalog.pg_class c
                                    JOIN pg_catalog.pg_namespace n on n.oid        = c.relnamespace
                                    JOIN pg_catalog.pg_index i     on i.indexrelid = c.oid
                                    JOIN pg_catalog.pg_class t     on i.indrelid   = t.oid
                                    JOIN pg_catalog.pg_attribute a on a.attrelid = t.oid and a.attnum = ANY(i.indkey)
                                   WHERE c.relkind = 'i'
                                     AND n.nspname not in ('pg_catalog', 'pg_toast')
                                     AND n.nspname = tc.constraint_schema
                                     AND t.relname = tc.table_name
                                     AND a.attname = kcu.column_name) = 'f' --identifica se a foreign key já possui um indice
    LOOP
        RAISE NOTICE '%', line.comando;
        EXECUTE line.comando;
    END LOOP;

    RETURN True;
END;
$body$ language plpgsql;
