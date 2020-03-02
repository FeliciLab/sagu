CREATE OR REPLACE FUNCTION obterObservacaoDeOperacaoFormatada(p_observacao TEXT, p_entryId INT)
RETURNS TEXT AS
$BODY$
/*************************************************************************************
  NAME: obterObservacaoDeOperacaoFormatada
  PURPOSE: Retorna a observação da operação, já formatada.

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ----------------------    ------------------------------------
  1.0       06/03/2015 Luís Felipe Wermann       1. Função criada.
**************************************************************************************/
DECLARE

    v_variaveis VARCHAR[] := ARRAY[
        'X_NUMERO_NOTA_FISCAL',
        'X_NOME_ALUNO'];
    v_variavel VARCHAR;
    v_retorno TEXT;

BEGIN

    v_retorno := p_observacao;
    FOREACH v_variavel IN ARRAY v_variaveis
    LOOP
        IF ( formatarVariavelObservacaoOperacao(v_variavel, p_entryId) IS NOT NULL )
        THEN
            v_retorno := REPLACE(v_retorno, v_variavel, formatarVariavelObservacaoOperacao(v_variavel, p_entryId));
        END IF;
    END LOOP;

    RETURN v_retorno;
        
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
