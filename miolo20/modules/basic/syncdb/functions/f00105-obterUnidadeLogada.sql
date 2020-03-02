CREATE OR REPLACE FUNCTION obterUnidadeLogada()
  RETURNS integer AS
$BODY$
/******************************************************************************
 * Obtem a unidade do usuario atual logado no postgres
******************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT unitid FROM basSessao WHERE login=current_user ORDER BY data DESC LIMIT 1);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
