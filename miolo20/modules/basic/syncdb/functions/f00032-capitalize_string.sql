CREATE OR REPLACE FUNCTION capitalize_string(p_texto VARCHAR)
RETURNS VARCHAR AS
$BODY$
/*********************************************************************************************
  NAME: capitalize_string
  PURPOSE: Aplica estilo "Camel Case" em uma string, nome de cidade por exemplo.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       30/08/2011 Arthur Lehdermann 1. FUNÇÃO criada.
*********************************************************************************************/
DECLARE
    v_palavra VARCHAR;
    v_texto VARCHAR[];
    v_texto_retorno VARCHAR;
BEGIN
    v_texto_retorno := '';
    -- Percorre as palavras para alterar para "Camel Case"
    FOR v_palavra IN SELECT explode_array(string_to_array(p_texto, ' '))
    LOOP
        -- Se for alguma dessas palavras deixa minúscula a palavra
        IF LOWER(v_palavra) IN ( 'da', 'de', 'do', 'das', 'dos', 'os', 'as', 'e', 'a', 'ou' )
        THEN
            v_palavra := LOWER(v_palavra);
        ELSE -- Caso contrério a primeira letra maiúscula
            v_palavra := initcap(v_palavra);
        END IF;

        -- Vai remontando o texto
        v_texto_retorno := v_texto_retorno || ' ' || v_palavra;
    END LOOP;

    -- Retorna o texto recebido por parémetro em "Camel Case"
    RETURN v_texto_retorno;
END
$BODY$
LANGUAGE 'plpgsql';
--
