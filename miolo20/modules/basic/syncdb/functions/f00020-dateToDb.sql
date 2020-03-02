CREATE OR REPLACE FUNCTION dateToDb(p_date varchar)
RETURNS date AS
$BODY$
/*************************************************************************************
  NAME: dateToDb
  PURPOSE: Converte uma data do usuario para o banco de dados
**************************************************************************************/
BEGIN
    RETURN TO_DATE(p_date, 'dd/mm/yyyy');
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
