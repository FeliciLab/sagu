CREATE OR REPLACE FUNCTION obtemCargaHorariaDeTodasOfertasDaMesmaUnidadeTematica(p_residenteid integer, p_unidadetematicaid integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: obtemCargaHorariaDeTodasOfertasDaMesmaUnidadeTematica
  PURPOSE: Retorna a carga horária de outras ofertas da mesma unidade temática.
**************************************************************************************/
DECLARE
    v_retVal res.encontro.cargaHoraria%TYPE;
    v_cargahoraria res.encontro.cargaHoraria%TYPE;
    v_cargahorariacomplementar res.encontro.cargaHoraria%TYPE;

BEGIN
     SELECT SUM(COALESCE(X.cargaHoraria, 0)) INTO v_cargahoraria
               FROM ( SELECT E.encontroid,
                             E.cargaHoraria
                        FROM res.ofertadoresidente ODR
                  INNER JOIN res.ofertaDeUnidadeTematica OT
                          ON ODR.ofertaDeUnidadeTematicaId = OT.ofertaDeUnidadeTematicaId
                  INNER JOIN res.encontro E
                          ON E.ofertaDeUnidadeTematicaId = OT.ofertaDeUnidadeTematicaId
                  INNER JOIN res.frequencia F
                          ON F.encontroid = E.encontroid
                  INNER JOIN res.ocorrenciaDeoferta OO
                          ON (OO.datahora = ( SELECT MAX(datahora)
                                               FROM res.ocorrenciaDeoferta
                                              WHERE ofertadoresidenteid = ODR.ofertaDoResidenteId )
                              AND OO.ofertadoresidenteid = ODR.ofertaDoResidenteId)
                       WHERE F.residenteid = p_residenteid
                         AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN OT.unidadetematicaid = p_unidadeTematicaId ELSE 1=1 END 
                         AND F.presenca IN ('P', 'J')
                         AND OO.status IN (1, 2, 4)
                    GROUP BY 1, 2) X ;



            -- carga horaria oriunda de outras fontes (aproveitamentos, por exemplo)
            SELECT COALESCE(SUM(A.cargaHoraria),0) INTO v_cargahorariacomplementar
              FROM res.cargaHorariaComplementar A
             WHERE A.residenteId = p_residenteId
               AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN A.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END;
               
            --garantindo que as variaveis nao fiquem nulas
            IF v_cargahoraria IS NULL
            THEN
                v_cargahoraria := 0;
            END IF;

            IF v_cargahorariacomplementar IS NULL
            THEN
                v_cargahorariacomplementar := 0;
            END IF;

            -- Total da carga horéria (carga horéria complementar + carga horéria total das unidades temáticas)
	    v_retVal = ROUND(COALESCE((v_cargahorariacomplementar + v_cargahoraria),0)::numeric,2);

    RETURN v_retVal;
END;
$$;
