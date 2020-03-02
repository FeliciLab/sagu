CREATE OR REPLACE FUNCTION res.cargahorariatotal(p_residenteid integer, p_unidadetematicaid integer)
  RETURNS real AS
$BODY$
/*********************************************************************************************
  NAME: res.cargaHorariaTotal
  PURPOSE: Obter a carga horéria total cursada por um residente em uma determinada
  unidade temática.
  DESCRIPTION: A FUNÇÃO percorre todos os locais onde o residente possa ter carga
  horéria registrada para a unidade temática informada, somando tudo o que for carga
  horéria vélida.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/07/2011 Alex Smith        1. FUNÇÃO criada.
  1.1       27/07/2011 ftomasini         1. estava comparando o Código da ultima ocorréncia
                                            com os status de ocorréncia de carga horéria vélida.
  1.2       23/08/2011 Moises Heberle    1. Modificado para que suporte que seja passado uma
                                            unidadeTematicaId como NULL, fazendo o somatorio total,
                                            sem filtrar por unidade tematica.
  1.3       26/08/2011 ftomasini         1. Correção no somatorio carga horéria
*********************************************************************************************/
DECLARE
    v_retVal res.encontro.cargaHoraria%TYPE;
    v_cargahoraria res.encontro.cargaHoraria%TYPE;
    v_cargahorariacomplementar res.encontro.cargaHoraria%TYPE;
BEGIN
    SELECT COALESCE (SUM(JJ2.cargaHoraria), 0) INTO v_cargahoraria
               FROM (SELECT B.cargaHoraria,
                            res.ultimaOcorrenciaDeOfertaId(A.residenteId, B.ofertaDeUnidadeTematicaId) as ultimaOcorrenciaDeOfertaId
                       FROM res.frequencia A
                 INNER JOIN res.encontro B
                         ON B.encontroId = A.encontroId
                 INNER JOIN res.ofertaDeUnidadeTematica C
                         ON C.ofertaDeUnidadeTematicaId = B.ofertaDeUnidadeTematicaId
                 INNER JOIN res.unidadeTematica D
                         ON D.unidadeTematicaId = C.unidadeTematicaId
                         -- considerar somente presenca ou falta justificada
                      WHERE A.presenca IN ('P', 'J')
                        AND A.residenteId = p_residenteId
               AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN D.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END ) JJ2
         INNER JOIN res.ocorrenciaDeoferta E
                 ON (E.ocorrenciadeofertaid = JJ2.ultimaOcorrenciaDeOfertaId )
         INNER JOIN res.ofertadoresidente ODR
                 ON ODR.ofertadoresidenteid = E.ofertadoresidenteid       
                   -- considerar somente ofertas cujo status para o residente
                   -- seja de Aprovacao, Interrupcao com aproveitamento de
                   -- carga horaria ou Apto
               Where E.status IN (1, 2, 4);
             
            -- carga horaria oriunda de outras fontes (aproveitamentos, por exemplo)
            SELECT COALESCE(SUM(A.cargaHoraria),0) INTO v_cargahorariacomplementar
              FROM res.cargaHorariaComplementar A
             WHERE A.residenteId = p_residenteId
               AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN A.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END;

            -- Total da carga horéria (carga horéria complementar + carga horéria total das unidades temáticas)
	    v_retVal = ROUND(COALESCE((v_cargahorariacomplementar + v_cargahoraria),0)::numeric,2);

    RETURN v_retVal;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION res.cargahorariatotal(integer, integer) OWNER TO postgres;
