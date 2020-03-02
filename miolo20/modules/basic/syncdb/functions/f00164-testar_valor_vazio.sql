CREATE OR REPLACE FUNCTION testar_valor_vazio(p_tabela varchar, chave_origem int, chave_destino int) 
RETURNS VOID AS $$
/*************************************************************************************
  NAME: testar_valor_vazio
  PURPOSE: Verifica se o registro de origem possui dados na tabela que o registro de destino não possui e os insere.
**************************************************************************************/
DECLARE
    v_select record;
    v_return record;
    v_primary_key varchar;
BEGIN
    SELECT INTO v_primary_key obterColunaChavePrimariaDaTabela(p_tabela);

    FOR v_select IN
        ( SELECT column_name AS coluna 
            FROM information_schema.columns 
           WHERE table_name = p_tabela )
    LOOP
        EXECUTE 'SELECT length(trim(' || v_select.coluna || '::text)) > 0 AS is_null 
                   FROM ' || p_tabela ||
                ' WHERE ' || v_primary_key || ' = ' || chave_destino || '' INTO v_return;

        IF v_return.is_null IS NULL
        THEN
            EXECUTE 'UPDATE ' || p_tabela || 
                      ' SET ' || v_select.coluna || ' = ( SELECT ' || v_select.coluna ||
                                                     ' FROM ONLY ' || p_tabela || 
                                                         ' WHERE ' || v_primary_key || ' = ' || chave_origem || ')
                      WHERE ' || v_primary_key || ' = ' || chave_destino;
        END IF;
    END LOOP;

    RETURN;
END;
$$
LANGUAGE 'plpgsql';
--
