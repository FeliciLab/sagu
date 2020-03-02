CREATE OR REPLACE FUNCTION formatarValorMonetario(valor numeric)
RETURNS varchar AS
$BODY$
DECLARE
    v_valor numeric;
BEGIN
    SELECT INTO v_valor round(valor, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::int);
    RETURN trim(to_char(v_valor, '999999999990D00'));
END;
$BODY$
LANGUAGE plpgsql;
