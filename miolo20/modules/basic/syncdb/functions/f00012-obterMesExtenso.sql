CREATE OR REPLACE FUNCTION obterMesExtenso(p_mes int)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterMesExtenso
  PURPOSE: Gera a representação por extenso de uma data fornecida por parâmetro
**************************************************************************************/
BEGIN
    RETURN CASE p_mes
        WHEN 01 THEN 'Janeiro'
        WHEN 02 THEN 'Fevereiro'
        WHEN 03 THEN 'Março'
        WHEN 04 THEN 'Abril'
        WHEN 05 THEN 'Maio'
        WHEN 06 THEN 'Junho'
        WHEN 07 THEN 'Julho'
        WHEN 08 THEN 'Agosto'
        WHEN 09 THEN 'Setembro'
        WHEN 10 THEN 'Outubro'
        WHEN 11 THEN 'Novembro'
        WHEN 12 THEN 'Dezembro'
    END;
END;
$BODY$
LANGUAGE 'plpgsql';
