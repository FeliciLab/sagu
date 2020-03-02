CREATE OR REPLACE FUNCTION updatesequences() RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
/*************************************************************************************
  NAME: updateSequences
  PURPOSE: Atualizar todas as sequences do banco para os valores de acordo com a
  tabela que gerenciam, fazendo SELECT MAX(coluna_gerenciada) FROM tabela.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       09/07/2011 Alex Smith        1. Função criada.
  2.0       12/01/2015 Luís F. Wermann   2.0 Função atualizada para não gerar problemas
                                         com tabelas que possuam inherits, ids zerados,
                                         ids que não sejam inteiros.
**************************************************************************************/
DECLARE
    v_row RECORD;
BEGIN

--Para evitar problemas com herança foi adicionada uma validação que coloca exceções de tabelas
--A tabela que vai ter o sequencial ajustado não pode possuir um tabela pai que seja diferente da BasLog

    FOR v_row IN SELECT DISTINCT 'SELECT setval(''' || REGEXP_REPLACE(pg_catalog.pg_get_expr(d.adbin, d.adrelid), '(^.*''([^'']*)[''].*$)',E'\\2') || ''', COALESCE((SELECT CASE WHEN MAX(' || a.attname || ') <= 0 THEN NULL ELSE MAX(' || a.attname || ') END FROM ' || n.nspname || '.' || c.relname || '), 1));' AS sqlToRun
                   FROM pg_catalog.pg_attribute a
             INNER JOIN pg_catalog.pg_attrdef d
                     ON d.adrelid = a.attrelid
                    AND d.adnum = a.attnum
                    AND a.atthasdef
             INNER JOIN pg_class c
                     ON a.attrelid = c.oid
              LEFT JOIN pg_catalog.pg_namespace n
                     ON n.oid = c.relnamespace
              LEFT JOIN information_schema.columns ss
                     ON (table_schema = n.nspname AND table_name = c.relname)
                  WHERE a.attnum > 0
                    AND NOT a.attisdropped
                    AND d.adsrc like '%nextval%'
                    AND (n.nspname || '.' || c.relname) IN (SELECT (A.schemaname || '.' || A.tablename) AS tabela
	                                                      FROM pg_catalog.pg_tables A
	                                                 LEFT JOIN pg_catalog.pg_inherits K
	                                                        ON K.inhrelid::regclass = (A.schemaname || '.' || A.tablename)::regclass
	                                                       AND K.inhparent::regclass <> 'basLog'::regclass
	                                                     WHERE A.schemaname NOT IN ('information_schema', 'pg_catalog')
	                                                       AND K.inhparent::regclass IS NULL
	                                                       AND (A.schemaname || '.' || A.tablename) NOT IN ('public.miolo_custom_field') --problema especial aqui
                                                          ORDER BY tabela)
                    AND ss.data_type IN ('integer', 'bigint', 'int') --restringe para apenas IDs inteiros
               ORDER BY 1
    LOOP
        RAISE NOTICE '%', v_row.sqlToRun;
        EXECUTE v_row.sqlToRun;
    END LOOP;

    RETURN TRUE;
END;
$_$;


ALTER FUNCTION public.updatesequences() OWNER TO postgres;
