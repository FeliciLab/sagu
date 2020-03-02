CREATE OR REPLACE FUNCTION getparameterdescription(p_moduleconfig character varying, p_parameter character varying)
  RETURNS character varying AS
$BODY$
/*************************************************************************************
  NAME: getParameterDescription
  PURPOSE: Retorna um varchar contendo a descricao de um parÃ©metro.
**************************************************************************************/
DECLARE
    v_retVal varchar;
    
    v_validationSql varchar;
    v_validationVal boolean;
    v_validationMsg varchar;
BEGIN
    --Caso o parametro não exista, retorna mostrando o erro.
    IF checkValidParameter(p_moduleConfig, p_parameter) = 'f' THEN
        RAISE EXCEPTION 'O parâmetro % não existe no módulo %.', p_parameter, p_moduleConfig;
    END IF;

    -- Se nao tiver passado o modulo
    IF ( p_moduleConfig IS NULL )
    THEN
        RAISE EXCEPTION 'Você deve informar um módulo.';
    -- Se nao tiver passado o parametro
    ELSIF ( p_parameter IS NULL )
    THEN
        RAISE EXCEPTION 'Você deve informar um parâmetro.';
    END IF;

    -- Busca a descricao do parametro
    SELECT C.description INTO v_retVal
      FROM basConfig C
     WHERE (C.moduleConfig, C.parameter) = (UPPER(p_moduleConfig), UPPER(p_parameter));
     
    RETURN v_retVal;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION getparameter(character varying, character varying)
  OWNER TO postgres;
