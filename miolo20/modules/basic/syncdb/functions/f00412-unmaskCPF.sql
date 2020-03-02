CREATE OR REPLACE FUNCTION unmaskcpf(p_cpf text)
RETURNS text AS
$BODY$
DECLARE
BEGIN
RETURN lpad( regexp_replace( p_cpf, '[^0-9]', '', 'gi'), 11, '0');
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION unmaskcpf(text)
OWNER TO postgres;