CREATE OR REPLACE FUNCTION obterColunaChavePrimariaDaTabela( p_tabela varchar )
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterColunaChavePrimariaDaTabela
  PURPOSE: Retorna o nome da coluna chave primária da tabela.
**************************************************************************************/
DECLARE
BEGIN
    RETURN ( SELECT pg_attribute.attname
               FROM pg_index, pg_class, pg_attribute 
              WHERE pg_class.oid = p_tabela::regclass 
                AND indrelid = pg_class.oid 
                AND pg_attribute.attrelid = pg_class.oid 
                AND pg_attribute.attnum = ANY(pg_index.indkey)
                AND indisprimary );
END;
$BODY$
LANGUAGE 'plpgsql';
--
