CREATE OR REPLACE FUNCTION timestampToDb(p_date varchar)
RETURNS timestamp AS
$BODY$
/*************************************************************************************
  NAME: timestampToDb
  PURPOSE: Converte um timestamp do usuario para a base.
**************************************************************************************/
BEGIN
    RETURN TO_TIMESTAMP(p_date, 'dd/mm/yyyy hh24:mi');
END;
$BODY$
LANGUAGE 'plpgsql';
