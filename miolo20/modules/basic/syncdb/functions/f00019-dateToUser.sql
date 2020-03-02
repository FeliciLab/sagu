CREATE OR REPLACE FUNCTION dateToUser(p_date date)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: dateToUser
  PURPOSE: Converte uma data vinda da base para o usuario.
**************************************************************************************/
BEGIN
    IF p_date IS NOT NULL
    THEN
        RETURN TO_CHAR(p_date, 'dd/mm/yyyy');
    ELSE
        RETURN NULL;
    END IF;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;

