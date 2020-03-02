CREATE OR REPLACE FUNCTION getUnitParameter( p_moduleConfig varchar, p_parameter varchar, p_unitid int )
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: getUnitParameter
  PURPOSE: Retorna um varchar contendo o valor de um parÃ©metro para a unidade desejada.
**************************************************************************************/
DECLARE
    v_retVal varchar;
BEGIN
    -- Se nÃ£o tiver passado o mÃ©dulo
    IF ( p_moduleConfig IS NULL )
    THEN
        RAISE EXCEPTION 'VocÃ© deve informar um mÃ©dulo.';
    -- Se nÃ£o tiver passado o parÃ©metro
    ELSIF ( p_parameter IS NULL )
    THEN
        RAISE EXCEPTION 'VocÃ© deve informar um parÃ©metro.';
    -- Se nÃ£o tiver passado a unidade
    ELSIF ( p_unitid IS NULL )
    THEN
        RAISE EXCEPTION 'VocÃ© deve informar uma unidade.';
    END IF;
    

    -- Busca o valor do parÃ©metro para a unidade desejada
    SELECT COALESCE(UC.value, C.value) INTO v_retVal
      FROM basConfig C
 LEFT JOIN basUnitConfig UC ON UC.unitid = p_unitid
       AND (UC.moduleConfig, UC.parameter) = (C.moduleConfig, C.parameter)
     WHERE (C.moduleConfig, C.parameter) = (UPPER(p_moduleConfig), UPPER(p_parameter));

    -- Caso nÃ£o consiga obter o valor, erro:
    IF ( v_retVal IS NULL )
    THEN
        RAISE EXCEPTION 'O parÃ©metro % nÃ£o existe no mÃ©dulo % para a unidade selecionada.', p_parameter, p_moduleConfig;
    END IF;

    RETURN v_retVal;
END;
$BODY$
LANGUAGE 'plpgsql';
--
