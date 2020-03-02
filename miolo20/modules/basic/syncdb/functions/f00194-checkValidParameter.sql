CREATE OR REPLACE FUNCTION checkValidParameter(p_module TEXT, p_parameter TEXT)
RETURNS boolean
AS $$
/***************************************************************
  NAME: checkValidParameter
  PURPOSE: Retorna verdadeiro se o parâmetro existe, e falso
           caso contrário.
****************************************************************/
DECLARE
    v_exits boolean;
    
BEGIN

    SELECT INTO v_exits count(*) > 0 FROM basconfig WHERE UPPER(parameter) = UPPER(p_parameter) AND UPPER(moduleconfig) = UPPER(p_module);

    IF v_exits THEN
        RETURN true;
    ELSE    
        RETURN false;
    END IF;

END;
$$ LANGUAGE plpgsql;

