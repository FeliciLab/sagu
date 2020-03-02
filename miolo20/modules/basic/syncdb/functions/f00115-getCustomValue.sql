CREATE OR REPLACE FUNCTION getCustomValue(p_fieldname varchar, p_customizedId varchar)
  RETURNS text AS
$BODY$
/*************************************************************************************
  NAME: getcustomvalue
  PURPOSE: Returna o valor customizado, de acordo com id do registro e campo passados.
**************************************************************************************/
BEGIN
     RETURN (   SELECT value
                  FROM miolo_custom_field F
            INNER JOIN miolo_custom_value V ON V.custom_field_id = F.id
                 WHERE F.name = p_fieldname
                   AND customized_id = p_customizedId );
END;
$BODY$
LANGUAGE 'plpgsql';
