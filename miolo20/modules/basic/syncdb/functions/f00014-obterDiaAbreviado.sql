CREATE OR REPLACE FUNCTION obterDiaAbreviado(p_value int)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterDiaExtenso
  PURPOSE: Retorna o nome do dia por extenso (ex.: 0 = Domingo, 1 = Segunda-feira, etc..)
**************************************************************************************/
BEGIN
    RETURN CASE p_value
        WHEN 0 THEN 'DOM'
        WHEN 1 THEN 'SEG'
        WHEN 2 THEN 'TER'
        WHEN 3 THEN 'QUA'
        WHEN 4 THEN 'QUI'
        WHEN 5 THEN 'SEX'
        WHEN 6 THEN 'SAB'
    END;
END;
$BODY$
LANGUAGE 'plpgsql';
