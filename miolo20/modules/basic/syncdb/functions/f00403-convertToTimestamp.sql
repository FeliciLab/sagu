CREATE OR REPLACE FUNCTION convertToTimestamp(p_timestamp VARCHAR)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: convertToTimestamp
  PURPOSE: Converte uma string vinda da aplicação para a base (timestamp).
**************************************************************************************/
DECLARE
    v_timestampFormated VARCHAR;
BEGIN
    BEGIN
        SELECT INTO v_timestampFormated p_timestamp::TIMESTAMP;
        EXCEPTION WHEN OTHERS THEN
            v_timestampFormated := p_timestamp;
    END;

    RETURN v_timestampFormated;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;