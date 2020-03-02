CREATE OR REPLACE FUNCTION obterDiaExtenso(p_value int)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: obterDiaExtenso
  PURPOSE: Retorna o nome do dia por extenso (ex.: 0 = Domingo, 1 = Segunda-feira, etc..)
**************************************************************************************/
BEGIN
    RETURN CASE p_value
        WHEN 0 THEN 'Domingo'
        WHEN 1 THEN 'Segunda-feira'
        WHEN 2 THEN 'Terça-feira'
        WHEN 3 THEN 'Quarta-feira'
        WHEN 4 THEN 'Quinta-feira'
        WHEN 5 THEN 'Sexta-feira'
        WHEN 6 THEN 'Sábado'
    END;
END;
$BODY$
LANGUAGE 'plpgsql';
