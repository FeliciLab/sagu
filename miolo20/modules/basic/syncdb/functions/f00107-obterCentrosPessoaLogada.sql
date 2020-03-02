CREATE OR REPLACE FUNCTION obtercentrospessoalogada()
  RETURNS integer[] AS
$BODY$
/******************************************************************************
 * Obtem os centros da pessoa logada
******************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT centerids FROM basSessao WHERE login=current_user ORDER BY data DESC LIMIT 1);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
