CREATE OR REPLACE FUNCTION timeToUser(p_value time)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: timeToUser
  PURPOSE: Converte uma hora vinda da base para o usuario.
**************************************************************************************/
BEGIN
    RETURN TO_CHAR(p_value, 'hh24:mi');
END;
$BODY$
LANGUAGE 'plpgsql';

