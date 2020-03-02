CREATE OR REPLACE FUNCTION verificaExistenciaDeCampoPersonalizadoRelacionadoARelatorio()
RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaExistenciaDeCampoPersonalizadoRelacionadoARelatorio
  DESCRIPTION: Verifica se o campo personalizado, antes de ser excluido, está
               relacionado a algum relatório, se sim, lança uma exceção

  REVISIONS:
  Ver       Date       Author                   Description
  --------- ---------- -----------------------  -------------------------------
  1.0       19/06/15   Luís Augusto W. Mercado  1. Trigger criada.
******************************************************************************/
DECLARE
    existeRegistroRelacionado BOOLEAN;
    relatoriosUtilizandoCampo TEXT;
BEGIN
    existeRegistroRelacionado := FALSE;
    relatoriosUtilizandoCampo := '';    

    SELECT INTO existeRegistroRelacionado COUNT(*) > 0
      FROM basreportparameter BRP
     WHERE BRP.nomecampopersonalizado = OLD.name;
    
    SELECT INTO relatoriosUtilizandoCampo string_agg('<strong>' || BR.name || '</strong>', ', ')
      FROM basreportparameter BRP
INNER JOIN basreport BR
        ON BRP.reportid = BR.reportid
     WHERE BRP.nomecampopersonalizado = OLD.name;

    -- Se existe um registro relacionado, não permite exclusão
    IF existeRegistroRelacionado IS TRUE 
    THEN
        RAISE EXCEPTION 'Não é possível excluir o campo personalizado, pois este está sendo utilizado nos seguintes relatórios genéricos: %', relatoriosUtilizandoCampo;
    END IF;
    
    RETURN OLD;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_verificaExistenciaDeCampoPersonalizadoRelacionadoARelatorio ON miolo_custom_field;
CREATE TRIGGER trg_verificaExistenciaDeCampoPersonalizadoRelacionadoARelatorio BEFORE DELETE ON miolo_custom_field
  FOR EACH ROW EXECUTE PROCEDURE verificaExistenciaDeCampoPersonalizadoRelacionadoARelatorio();
