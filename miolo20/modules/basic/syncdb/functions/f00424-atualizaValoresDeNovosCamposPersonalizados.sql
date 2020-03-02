CREATE OR REPLACE FUNCTION atualizaValoresDeNovosCamposPersonalizados()
RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: atualizaValoresDeNovosCamposPersonalizados
  DESCRIPTION: Trigger que adiciona um valor para um campo personalzado recém
  adicionado caso este seja relacionado a outros (contenha o mesmo nome)

  REVISIONS:
  Ver       Date       Author                   Description
  --------- ---------- -----------------------  -------------------------------
  1.0       06/07/15   Luís Augusto W. Mercado  1. Trigger criada.
******************************************************************************/
DECLARE
    idCampoPersonalizadoRelacionado varchar;

BEGIN
    idCampoPersonalizadoRelacionado := (
                                        SELECT id
                                          FROM miolo_custom_field
                                         WHERE name = NEW.name
                                         LIMIT 1
                                       );
    
    -- Para não entrar em loop
    ALTER TABLE miolo_custom_value DISABLE TRIGGER trg_atualizaValoresDosCamposPersonalizados;

    INSERT INTO miolo_custom_value (customized_id, custom_field_id, value)
         SELECT customized_id,
                NEW.id,
                value
           FROM miolo_custom_value
          WHERE custom_field_id = idCampoPersonalizadoRelacionado;
    
    ALTER TABLE miolo_custom_value ENABLE TRIGGER trg_atualizaValoresDosCamposPersonalizados;
      
    RETURN NEW;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_atualizaValoresDeNovosCamposPersonalizados ON miolo_custom_field;
CREATE TRIGGER trg_atualizaValoresDeNovosCamposPersonalizados AFTER INSERT ON miolo_custom_field
    FOR EACH ROW EXECUTE PROCEDURE atualizaValoresDeNovosCamposPersonalizados();
