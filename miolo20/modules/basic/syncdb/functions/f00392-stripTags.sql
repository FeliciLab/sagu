CREATE OR REPLACE FUNCTION stripTags(dado text)
    RETURNS TEXT AS
$BODY$
/******************************************************************************
  NAME: stripTags
  DESCRIPTION: Tira qualquer tag HTML de um dado texto

  REVISIONS:
  Ver       Date        Author                       Description
  --------- ----------  --------------------------   --------------------------
  1.0       17/04/2015  Luís Augusto Weber Mercado   1. Função criada.
******************************************************************************/
BEGIN
    RETURN (SELECT regexp_replace(regexp_replace(dado, E'(?x)<[^>]*?(\s alt \s* = \s* ([''"]) ([^>]*?) \2) [^>]*? >', E'\3'), E'(?x)(< [^>]*? >)', '', 'g'));

END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;