CREATE OR REPLACE FUNCTION atualizaValoresDosCamposPersonalizados()
RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: atualizaValoresDosCamposPersonalizados
  DESCRIPTION: Atualiza todos os valores de um mesmo campo personalizado que tenham
  a mesma chave primária

  REVISIONS:
  Ver       Date       Author                   Description
  --------- ---------- -----------------------  -------------------------------
  1.0       02/07/15   Luís Augusto W. Mercado  1. Trigger criada.
******************************************************************************/
DECLARE
    idCampoPersonalizado varchar;
    campoPersonalizadoPossuiValorRelacionado boolean;

BEGIN
    IF (TG_OP = 'UPDATE') THEN
        IF OLD.value=NEW.value THEN
            RETURN NEW;

        END IF;

    END IF;
    
    FOR idCampoPersonalizado IN (
                                 SELECT id
                                   FROM miolo_custom_field
                                  WHERE name = (
                                                SELECT name
                                                  FROM miolo_custom_field
                                                 WHERE id=NEW.custom_field_id
                                               )
                                )
    LOOP
        campoPersonalizadoPossuiValorRelacionado := (
                                                     SELECT COUNT(*) > 0
                                                       FROM miolo_custom_value
                                                      WHERE custom_field_id = idCampoPersonalizado
                                                        AND customized_id=NEW.customized_id
                                                    );

        -- Se o registro existe, atualiza-o
        IF campoPersonalizadoPossuiValorRelacionado = TRUE THEN
            UPDATE miolo_custom_value
               SET value=NEW.value
             WHERE customized_id=NEW.customized_id
               AND custom_field_id=idCampoPersonalizado;

        -- Se não insere um novo
        ELSE
            INSERT INTO miolo_custom_value (customized_id, custom_field_id, value)
            VALUES (NEW.customized_id, idCampoPersonalizado, NEW.value);

        END IF;

    END LOOP;

    RETURN NEW;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_atualizaValoresDosCamposPersonalizados ON miolo_custom_value;
CREATE TRIGGER trg_atualizaValoresDosCamposPersonalizados AFTER UPDATE OR INSERT ON miolo_custom_value
    FOR EACH ROW EXECUTE PROCEDURE atualizaValoresDosCamposPersonalizados();
