
CREATE OR REPLACE FUNCTION BIBLIOGRAPHYS(p_data text[])
   RETURNS text AS
/*************************************************************************************
  NAME: BIBLIOGRAPHYS
  DESCRIPTION: bibliografia pelo array
**************************************************************************************/
$BODY$ 
DECLARE
    v_id TEXT;
    v_content text;
    v_result text;
BEGIN

    v_result := '';

    FOR v_id IN SELECT * FROM unnest(p_data)
    LOOP
        FOR v_content IN (SELECT content FROM SEA_BIBLIOGRAPHY_DATA( v_id::int, NULL, NULL, '100.a,700.a,245.a,250.a,260.a,260.b,260.c' ))
        LOOP
            v_result := v_result || ( CASE WHEN CHAR_LENGTH(v_result) > 0 THEN ', ' ELSE '' END ) || v_content;
        END LOOP;

        IF CHAR_LENGTH(v_result) > 0
        THEN
            v_result := v_result || '\n';
        END IF;
    END LOOP;
        
    RETURN RTRIM(v_result);
END; 
$BODY$ 
language plpgsql;

