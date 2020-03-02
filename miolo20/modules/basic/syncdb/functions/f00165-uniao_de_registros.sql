CREATE OR REPLACE FUNCTION uniao_de_registros(tabela_principal varchar, chave_origem int, chave_destino int) 
RETURNS VOID AS $$
/*************************************************************************************
  NAME: uniao_de_registros
  PURPOSE: Unifica registros duplicados na base de dados, 
  recebendo a tabela e os dois códigos a serem unificados da mesma.
**************************************************************************************/
DECLARE
    row record;
    primary_key VARCHAR;
BEGIN	
    FOR row IN 
        ( SELECT tc.table_name AS tabela, 
	         tc.constraint_schema AS esquema, 
	         kcu.column_name AS coluna
	    FROM information_schema.table_constraints AS tc 
	    JOIN information_schema.key_column_usage AS kcu 
	      ON tc.constraint_name = kcu.constraint_name
	    JOIN information_schema.constraint_column_usage AS ccu 
	      ON ccu.constraint_name = tc.constraint_name
	   WHERE constraint_type = 'FOREIGN KEY' 
	     AND ccu.table_name = tabela_principal )
    LOOP
        IF row.tabela = 'gtclibperson' THEN
            DELETE FROM gtclibperson  WHERE personid =  chave_origem;
            END IF;

        IF row.tabela <> tabela_principal AND row.tabela <> 'gtclibperson'
    THEN
        BEGIN
            RAISE NOTICE 'UPDATE %.% SET % = % WHERE % = %', row.esquema, row.tabela, row.coluna, chave_destino, row.coluna, chave_origem;
            EXECUTE 'UPDATE ' || row.esquema || '.' || row.tabela || ' SET ' || row.coluna || ' = ' || chave_destino || ' WHERE ' || row.coluna || ' = ' || chave_origem;
        EXCEPTION WHEN unique_violation
        THEN
            EXECUTE 'DELETE FROM ' ||  row.esquema || '.' || row.tabela || ' WHERE ' || row.coluna || ' = ' || chave_origem;
        END;
    END IF;
    END LOOP;
        
    SELECT INTO primary_key obterColunaChavePrimariaDaTabela(tabela_principal);

    RAISE NOTICE 'DELETE FROM % WHERE % = %', tabela_principal, primary_key, chave_origem;
    EXECUTE 'DELETE FROM ' || tabela_principal || ' WHERE ' || primary_key || ' = ' || chave_origem;

    RETURN;
END;
$$
LANGUAGE plpgsql;
--
