CREATE OR REPLACE FUNCTION obterSexoExtenso(p_value char)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterSexoExtenso
  PURPOSE: Retorna o sexo por extenso (ex.: Masculino, Feminino)
**************************************************************************************/
BEGIN
    RETURN CASE lower(p_value)
        WHEN 'm' THEN 'Masculino'
        WHEN 'f' THEN 'Feminino'
    END;
END;
$BODY$
LANGUAGE 'plpgsql';
