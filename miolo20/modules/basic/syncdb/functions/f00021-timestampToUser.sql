CREATE OR REPLACE FUNCTION timestampToUser(p_date timestamp)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: timestampToUser
  PURPOSE: Converte um timestamp vindo da base para o usuario.
**************************************************************************************/
BEGIN
    RETURN TO_CHAR(p_date, 'dd/mm/yyyy hh24:mi');
END;
$BODY$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION timestampToUser(p_date TIMESTAMP WITH TIME ZONE)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: timestampToUser
  PURPOSE: Converte um timestamp vindo da base para o usuario.
**************************************************************************************/
BEGIN
    RETURN TO_CHAR(p_date, 'dd/mm/yyyy hh24:mi');
END;
$BODY$
LANGUAGE 'plpgsql';
