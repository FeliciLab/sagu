CREATE OR REPLACE FUNCTION prc_obterprecodaofertadecurso(p_ofertadecursoid int, p_tipo char(1))
RETURNS int AS
$BODY$
/*************************************************************************************
  NAME: prc_obterprecodaofertadecurso
  PURPOSE: Obtem o código do preço a partir da oferta de curso

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       26/11/2013 Jonas Diel        1. Função criada.
**************************************************************************************/
DECLARE
    v_precocursoid prcprecocurso.precocursoid%TYPE;
    v_ocorrenciacursoid prcprecocurso.ocorrenciacursoid%TYPE;
BEGIN
    --Busca preço pela oferta de curso
    SELECT precocursoid INTO v_precocursoid FROM prcprecocurso WHERE ofertacursoid = p_ofertadecursoid AND tipo = p_tipo AND datainicial <= now()::date AND CASE WHEN datafinal is not null THEN datafinal > now()::date ELSE TRUE END;

    --Busca pela ocorrência de curso
    IF ( v_precocursoid IS NULL )
    THEN
        SELECT ocorrenciacursoid INTO v_ocorrenciacursoid FROM acpofertacurso WHERE ofertacursoid = p_ofertadecursoid;
        SELECT precocursoid INTO v_precocursoid FROM prcprecocurso WHERE ocorrenciacursoid = v_ocorrenciacursoid AND tipo = p_tipo AND datainicial <= now()::date AND CASE WHEN datafinal is not null THEN datafinal > now()::date ELSE TRUE END;

    END IF;

    RETURN v_precocursoid;
END;
$BODY$
LANGUAGE 'plpgsql';
