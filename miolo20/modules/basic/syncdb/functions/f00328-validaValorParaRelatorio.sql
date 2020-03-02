CREATE OR REPLACE FUNCTION validaValorParaRelatorio(p_valor TEXT)
RETURNS TEXT AS
/*************************************************************************************
  NAME: validaValorParaRelatorio
  PURPOSE: Resolve problemas de caracteres especiais para gerar relatórios
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Adicionando resolução para tratar &.
**************************************************************************************/
$BODY$
    DECLARE
    
    v_result TEXT;
    
    BEGIN

        --Trata e-comercial
        v_result = REPLACE(p_valor, '&', '&amp;');

    RETURN v_result;

    END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;
