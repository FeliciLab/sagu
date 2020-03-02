CREATE OR REPLACE FUNCTION res.ultimaOcorrenciaDeContratoId(p_residenteId integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: res.ultimaOcorrenciaDeContratoId
  PURPOSE: Retorna o identificador da ocorrencia de contrato mais recente para o residente informado.
 *************************************************************************************/
DECLARE
    v_retVal res.ocorrenciaDeContrato.ocorrenciaDeContratoId%TYPE;
BEGIN
    SELECT A.ocorrenciaDeContratoId INTO v_retVal
      FROM res.ocorrenciaDeContrato A
     WHERE A.residenteId = p_residenteId
            AND A.dataHora::DATE <= now()
  ORDER BY A.dataHora DESC
     LIMIT 1;

    RETURN v_retVal;
END;
$BODY$
  LANGUAGE plpgsql
IMMUTABLE;
