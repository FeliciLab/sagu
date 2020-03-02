CREATE OR REPLACE FUNCTION prc_obterprecodaofertadeturma(p_ofertadeturmaid int, p_tipo char(1))
RETURNS int AS
$BODY$
/*************************************************************************************
  NAME: prc_obterprecodaofertadeturma
  PURPOSE: Obtem o código do preço a partir da oferta de turma

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
    --Busca preço pela turma
    SELECT precocursoid INTO v_precocursoid
      FROM prcprecocurso
     WHERE ofertaturmaid = p_ofertadeturmaid
       AND NOW()::date BETWEEN dataInicial AND COALESCE(dataFinal, NOW()::date + 1)
       AND tipo = p_tipo;

    IF ( v_precocursoid IS NULL )
    THEN
        --Busca preço pela oferta de curso
        SELECT ofertacursoid INTO v_ofertacursoid
          FROM acpofertaturma
         WHERE ofertaturmaid = p_ofertadeturmaid;
         
        SELECT precocursoid INTO v_precocursoid 
          FROM prcprecocurso
         WHERE ofertacursoid = v_ofertacursoid
           AND NOW()::date BETWEEN dataInicial AND COALESCE(dataFinal, NOW()::date + 1)
           AND tipo = p_tipo;

        IF ( v_precocursoid IS NULL )
        THEN
            --Busca preço pela ocorrencia de curso
            SELECT ocorrenciacursoid INTO v_ocorrenciacursoid
              FROM acpofertacurso
             WHERE ofertacursoid = v_ofertacursoid;

            SELECT precocursoid INTO v_precocursoid
              FROM prcprecocurso
             WHERE ocorrenciacursoid = v_ocorrenciacursoid
               AND NOW()::date BETWEEN dataInicial AND COALESCE(dataFinal, NOW()::date + 1)
               AND tipo = p_tipo;
        END IF;
    END IF;

    RETURN v_precocursoid;
END;
$BODY$
LANGUAGE 'plpgsql';
