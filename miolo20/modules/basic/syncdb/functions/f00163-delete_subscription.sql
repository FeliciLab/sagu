CREATE OR REPLACE FUNCTION delete_subscription(p_tabela TEXT, p_esquema TEXT, p_chave TEXT)
 RETURNS boolean AS
$BODY$
BEGIN
RETURN true;
END;
$BODY$
LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION delete_subscription(p_tabela TEXT, p_esquema TEXT, p_chave TEXT)
 RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: delete_subscription
  DESCRIPTION: Deleta de forma recursiva todos os registros relacionados a uma 
  inscrição em um processo seletivo.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       28/01/15   Luís F. Wermann    Função criada.
******************************************************************************/
DECLARE

v_row RECORD;
v_select VARCHAR;
v_result TEXT;
v_novaChave TEXT;
v_conteudoChave TEXT;
v_teste TEXT;

BEGIN
    FOR v_row IN (SELECT tc.table_name AS tabela, 
	                 tc.constraint_schema AS esquema, 
	                 kcu.column_name AS coluna
	            FROM information_schema.table_constraints AS tc 
	            JOIN information_schema.key_column_usage AS kcu 
	              ON tc.constraint_name = kcu.constraint_name
	            JOIN information_schema.constraint_column_usage AS ccu 
	              ON ccu.constraint_name = tc.constraint_name
	           WHERE constraint_type = 'FOREIGN KEY' 
	             AND ccu.table_name = p_tabela)
    LOOP
        IF v_row.tabela <> p_tabela
        THEN
            v_select := 'SELECT ' || v_row.coluna || ' FROM ' || v_row.esquema || '.' || v_row.tabela || ' WHERE ' || v_row.coluna || ' = ''' || p_chave || '''';
            RAISE NOTICE '%', v_select;

            EXECUTE v_select INTO v_result;

            IF v_result IS NOT NULL
            THEN
                BEGIN
                v_teste := 'DELETE FROM ' || v_row.esquema || '.' || v_row.tabela || ' WHERE ' || v_row.coluna || ' = ''' || p_chave || '''';
                RAISE NOTICE '%', v_teste; 
                EXECUTE v_teste;

                EXCEPTION WHEN foreign_key_violation
                    THEN 
                        SELECT INTO v_novaChave obterColunaChavePrimariaDaTabela((v_row.esquema || '.' || v_row.tabela));
                        v_select := 'SELECT ' || v_novaChave || ' FROM ' || v_row.esquema || '.' || v_row.tabela || ' WHERE ' || v_row.coluna || ' = ''' || p_chave || '''';
                        EXECUTE v_select INTO v_conteudoChave;
                        EXECUTE 'SELECT delete_cascade( ''' || v_row.tabela || ''', ''' || v_row.esquema || ''', ''' || v_conteudoChave || ''')';
                END;
            END IF;
        END IF;
    END LOOP;

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION delete_subscription(text, text, text)
  OWNER TO postgres;

