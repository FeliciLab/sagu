CREATE OR REPLACE FUNCTION getallcustomvalues( p_campo varchar)
  RETURNS TABLE (customized_id miolo_custom_value.customized_id%TYPE,
                 value miolo_custom_value.value%TYPE) AS
$BODY$
/*************************************************************************************
  NAME: getallcustomvalues
  PURPOSE: Retorna os chaves e os valores de um campo personalizado. Esta função é útil
           quando necessário relacionar os valores com tabelas (joins)

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- -----------------  ------------------------------------
  1.0       27/03/15   jamiel          1. FUNÇÃO criada.
*************************************************************************************/
    SELECT customized_id, value
      FROM miolo_custom_field campo
 LEFT JOIN miolo_custom_value valor
        ON ( valor.custom_field_id = campo.id )
     WHERE campo.name = $1;
$BODY$
  LANGUAGE 'sql';  
