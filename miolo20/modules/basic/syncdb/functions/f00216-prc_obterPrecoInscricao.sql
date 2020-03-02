CREATE OR REPLACE FUNCTION prc_obterprecoinscricao(p_ofertacursoid int, p_datainicial date, p_datafinal date)
RETURNS int AS
$BODY$
/*************************************************************************************
  NAME: prc_obterprecoinscricao
  PURPOSE: Obtem o código do preço da inscricao a partir da oferta de curso

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       26/11/2013 Jonas Diel        1. Função criada.
**************************************************************************************/
DECLARE
    v_precocursoid prcprecocurso.precocursoid%TYPE;
    v_ofertacursoid  prcprecocurso.ofertacursoid%TYPE;
    v_ocorrenciacursoid prcprecocurso.ocorrenciacursoid%TYPE;

BEGIN
        --Busca preço pela oferta de curso
        SELECT precocursoid INTO v_precocursoid FROM prcprecocurso WHERE ofertacursoid = p_ofertacursoid AND tipo = 'I' AND datainicial <= COALESCE(p_datainicial, now()::date) AND ( datafinal IS NULL OR datafinal  >= COALESCE(p_datafinal, now()::date) );

        IF ( v_precocursoid IS NULL )
        THEN
            --Busca preço pela ocorrencia de curso
            SELECT ocorrenciacursoid INTO v_ocorrenciacursoid FROM acpofertacurso WHERE ofertacursoid = p_ofertacursoid;
            SELECT precocursoid INTO v_precocursoid FROM prcprecocurso WHERE ocorrenciacursoid = v_ocorrenciacursoid AND tipo = 'I' AND datainicial <= COALESCE(p_datainicial, now()::date) AND ( datafinal IS NULL OR datafinal >= COALESCE(p_datafinal, now()::date) );
        END IF;

    RETURN v_precocursoid;
END;
$BODY$
LANGUAGE 'plpgsql';
