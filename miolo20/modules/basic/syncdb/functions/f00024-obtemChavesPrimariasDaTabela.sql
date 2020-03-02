CREATE OR REPLACE FUNCTION obtemChavesPrimariasDaTabela(p_table_name TEXT, p_table_squema TEXT) 
RETURNS TABLE(column_name information_schema.sql_identifier) AS
$BODY$
/*************************************************************************************************************
  NAME: obtemChavesPrimariasDaTabela
  PURPOSE: Obtém os nomes das colunas chaves primárias da tabela.
  
  REVISIONS:
  Ver       Date          Author               Description
  --------- ------------- -------------------- ------------------------------------
  1.0       13/05/2014    Augusto A. Silva     1. Função criada.
**************************************************************************************************************/
DECLARE
      v_select TEXT;
BEGIN
     -- Obtém todas as colunas chaves primárias da tabela.
     v_select := 'SELECT column_name AS column_name
                    FROM information_schema.constraint_column_usage U
              INNER JOIN information_schema.table_constraints CONS
                      ON CONS.table_name = U.table_name
                     AND CONS.table_schema = U.table_schema
                     AND CONS.constraint_name = U.constraint_name
                     AND CONS.constraint_type = ''PRIMARY KEY''
                   WHERE U.table_name = ''' || p_table_name || '''
                     AND U.table_schema = ''' || p_table_squema || '''';
        
     RETURN QUERY EXECUTE v_select;
END;
$BODY$
LANGUAGE plpgsql
IMMUTABLE;
