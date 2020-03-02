
--
CREATE OR REPLACE FUNCTION getParameter( p_moduleConfig varchar, p_parameter varchar )
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: getParameter
  PURPOSE: Retorna um varchar contendo o valor de um parÃ©metro.
**************************************************************************************/
DECLARE
    v_retVal varchar;
    
    v_validationSql varchar;
    v_validationVal boolean;
    v_validationMsg varchar;
BEGIN
    --Caso o parâmetro não exista, retorna mostrando o erro.
    IF checkValidParameter(p_moduleConfig, p_parameter) = 'f' THEN
        RAISE EXCEPTION 'O parâmetro % não existe no módulo %.', p_parameter, p_moduleConfig;
    END IF;

    -- Se nÃ£o tiver passado o mÃ©dulo
    IF ( p_moduleConfig IS NULL )
    THEN
        RAISE EXCEPTION 'Você deve informar um módulo.';
    -- Se nÃ£o tiver passado o parÃ©metro
    ELSIF ( p_parameter IS NULL )
    THEN
        RAISE EXCEPTION 'Você deve informar um parâmetro.';
    END IF;

    -- Busca o valor do parÃ©metro, priorizando da unidade logada, caso existir
    SELECT COALESCE(UC.value, C.value) INTO v_retVal
      FROM basConfig C
 LEFT JOIN basUnitConfig UC ON UC.unitid = obterUnidadeLogada()
       AND (UC.moduleConfig, UC.parameter) = (C.moduleConfig, C.parameter)
     WHERE (C.moduleConfig, C.parameter) = (UPPER(p_moduleConfig), UPPER(p_parameter));
     
    -- Validar o valor, caso exista um comando para validação. 
    SELECT COALESCE(UC.validatevalue, C.validatevalue) INTO v_validationSql
      FROM basConfig C
 LEFT JOIN basUnitConfig UC ON UC.unitid = obterUnidadeLogada()
       AND (UC.moduleConfig, UC.parameter) = (C.moduleConfig, C.parameter)
     WHERE (C.moduleConfig, C.parameter) = (UPPER(p_moduleConfig), UPPER(p_parameter));
     
     IF ( char_length(v_validationSql) > 0 AND char_length(v_retVal) > 0 ) THEN
         BEGIN
            v_validationSql := replace(v_validationSql, '?', '''' || v_retVal || '''');
            EXECUTE v_validationSql INTO v_validationVal;
            
            IF ( v_validationVal ) IS NOT TRUE THEN
                BEGIN
                    SELECT COALESCE(UC.validatemsg, C.validatemsg) INTO v_validationMsg
                      FROM basConfig C
                 LEFT JOIN basUnitConfig UC ON UC.unitid = obterUnidadeLogada()
                       AND (UC.moduleConfig, UC.parameter) = (C.moduleConfig, C.parameter)
                     WHERE (C.moduleConfig, C.parameter) = (UPPER(p_moduleConfig), UPPER(p_parameter));
                     
                     RAISE EXCEPTION 'O valor % não é um valor válido para o parâmetro %. %', v_retVal, p_parameter, v_validationMsg;
                END;
            END IF;
         
         END;
     END IF;

    RETURN v_retVal;
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;
