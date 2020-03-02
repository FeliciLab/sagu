CREATE OR REPLACE FUNCTION obterConfiguracaoDeMatricula(p_configuracaoId INTEGER, p_valorInterno TEXT, p_valorExterno TEXT, p_tipoMatricula INTEGER)
RETURNS TEXT AS
/*************************************************************************************
  NAME: obterConfiguracaoDeMatricula
  PURPOSE: Obtém o valor de certa configuração de matrícula.
  DESCRIPTION: vide "PURPOSE".
               Tipo de matrícula: 1 - Sistema (academic).
                                  2 - Web (portal/services).
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Função criada.
**************************************************************************************/
$BODY$
    DECLARE
    
    v_SQL TEXT;
    v_result TEXT;
    
    BEGIN

        IF ( p_tipoMatricula = 1 )
        THEN
            v_SQL := 'SELECT ' || p_valorInterno || ' FROM acdEnrollConfig WHERE enrollConfigId = ' || p_configuracaoId;
            ELSE
	        v_SQL := 'SELECT ' || p_valorExterno || ' FROM acdEnrollConfig WHERE enrollConfigId = ' || p_configuracaoId;
        END IF;

    EXECUTE v_SQL INTO v_result;

    RETURN v_result;

    END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;
