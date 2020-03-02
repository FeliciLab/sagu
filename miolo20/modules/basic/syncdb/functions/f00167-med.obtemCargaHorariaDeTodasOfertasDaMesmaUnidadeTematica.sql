CREATE OR REPLACE FUNCTION med.obtemcargahorariadetodasofertasdamesmaunidadetematica(p_residenteid integer, p_unidadetematicaid integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: med.obtemCargaHorariaDeTodasOfertasDaMesmaUnidadeTematica
  PURPOSE: Retorna a carga horÃ¡ria de outras ofertas da mesma unidade temÃ¡tica.
**************************************************************************************/
DECLARE
    v_retVal med.encontro.cargaHoraria%TYPE;
    v_cargahoraria med.encontro.cargaHoraria%TYPE;
    v_cargahorariacomplementar med.encontro.cargaHoraria%TYPE;

BEGIN

SELECT COALESCE (SUM(JJ2.cargaHoraria), 0) INTO v_cargahoraria
               FROM (SELECT  B.cargaHoraria,
                             D.UnidadeTematicaid,
                             A.residenteid
                       FROM med.frequencia A
                 INNER JOIN med.encontro B
                         ON B.encontroId = A.encontroId
                 INNER JOIN med.ofertaDeUnidadeTematica C
                         ON C.ofertaDeUnidadeTematicaId = B.ofertaDeUnidadeTematicaId
                 INNER JOIN med.unidadeTematica D
                         ON D.unidadeTematicaId = C.unidadeTematicaId
                 INNER JOIN med.ofertadoresidente xODR
                         ON (xODR.ofertaDeUnidadeTematicaId = b.ofertaDeUnidadeTematicaId
                        AND xODR.residenteid = A.residenteid)
                 INNER JOIN med.ocorrenciaDeoferta XE
                         ON (XE.ofertadoresidenteid = xODR.ofertadoresidenteid 
                        AND xe.ocorrenciadeofertaid = med.ultimaOcorrenciaDeOfertaId(A.residenteId, B.ofertaDeUnidadeTematicaId)
                        AND xe.status in (1,2, 4))
                         -- considerar somente presenca ou falta justificada
                      WHERE A.presenca IN ('P', 'J')
                        AND A.residenteId = p_residenteid
                        AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN D.unidadetematicaid = p_unidadeTematicaId ELSE 1=1 END) JJ2;

            -- carga horaria oriunda de outras fontes (aproveitamentos, por exemplo)
            SELECT COALESCE(SUM(A.cargaHoraria),0) INTO v_cargahorariacomplementar
              FROM med.cargaHorariaComplementar A
             WHERE A.residenteId = p_residenteId
               AND (CASE WHEN p_unidadeTematicaId IS NOT NULL THEN A.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END);

            --garantindo que as variaveis nao fiquem nulas
            IF v_cargahoraria IS NULL
            THEN
                v_cargahoraria := 0;
            END IF;

            IF v_cargahorariacomplementar IS NULL
            THEN
                v_cargahorariacomplementar := 0;
            END IF;

            -- Total da carga horÃ©ria (carga horÃ©ria complementar + carga horÃ©ria total das unidades temÃ¡ticas)
	    v_retVal = ROUND(COALESCE((v_cargahorariacomplementar + v_cargahoraria),0)::numeric,2);

    RETURN v_retVal;
END;
$$;

