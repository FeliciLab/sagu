CREATE OR REPLACE FUNCTION converteTipoDeOperacaoDoValorSeNecessario(p_valor NUMERIC, p_tipo_operacao CHAR)
RETURNS CHAR AS
$BODY$
BEGIN
    RETURN (
        SELECT (CASE WHEN p_valor < 0 
                     THEN
                         (CASE p_tipo_operacao
                               WHEN 'C'
                               THEN
                                    'D'
                               ELSE
                                    'C'
                          END)
                     ELSE
                         p_tipo_operacao
                END)
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
