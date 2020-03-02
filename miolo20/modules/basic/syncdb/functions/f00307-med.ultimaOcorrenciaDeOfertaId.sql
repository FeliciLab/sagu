CREATE OR REPLACE FUNCTION med.ultimaocorrenciadeofertaid(p_residenteid integer, p_ofertadeunidadetematicaid integer)
   RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: med.ultimaOcorrenciaDeOfertaId
  PURPOSE: Retorna o identificador da ocorrência de oferta mais recente da oferta de
  unidade temática informada, para o residente informado.

  REVISIONS:
**************************************************************************************/
DECLARE
    v_retVal med.ocorrenciaDeOferta.ocorrenciaDeOfertaId%TYPE;
BEGIN
    SELECT A.ocorrenciaDeOfertaId INTO v_retVal
      FROM med.ocorrenciaDeOferta A
INNER JOIN med.ofertaDoResidente B
        ON B.ofertaDoResidenteId = A.ofertaDoResidenteId
     WHERE B.residenteId = p_residenteId
       AND B.ofertaDeUnidadeTematicaId = p_ofertaDeUnidadeTematicaId
  ORDER BY A.dataHora DESC
     LIMIT 1;

    RETURN v_retVal;
END;
$BODY$
  LANGUAGE 'plpgsql';
