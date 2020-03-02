--
-- 10/10/2011 - Moises Heberle - Alterado FUNÇÃO.
-- 09/01/2013 - Augusto A. Silva - Correçães na FUNÇÃO.
--
CREATE OR REPLACE FUNCTION tra.fn_chk_training_request_start(
    p_unitAreaId tra.request.unitareaid%TYPE,
    p_trainingTypeId tra.request.trainingtypeid%TYPE,
    p_begindate tra.request.begindate%TYPE)

RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: fn_chk_training_request_start
  PURPOSE:FUNÇÃO que verifica se a solicitação esta sendo feita dentro do intervalo mínimo e méximo de solicitação.

  OBS: Parametro trainingTypeId esté DEPRECATED, mantido para compatibilidade.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       30/06/2011 Fabiano Tomasini  1. FUNÇÃO criada.
  1.1       28/07/2011 Arthur Lehdermann 2. Corrigido verificação de dias mínimos e méximo.
  1.2       12/08/2011 Moises Heberle    3. Alterando de areaId para unitAreaId
  1.3       10/10/2011 Moises Heberle    4. Alterando chamada de tabela tra.trainingTypeArea
                                             para tra.unit (campos movidos)
**************************************************************************************/
DECLARE
    v_valid BOOLEAN;
    v_rules RECORD;

BEGIN
    v_valid:= TRUE;

        SELECT (COUNT(*) > 0) AS existingRule,
               U.maximumDaysUntilTrainingStart,
               U.minimumDaysUntilTrainingStart
          INTO v_rules
          FROM tra.unitArea UA
    INNER JOIN tra.unit U
            ON U.unitId = UA.unitId
         WHERE UA.unitAreaId = p_unitAreaId
      GROUP BY 2,3;

    IF ( v_rules.existingRule IS TRUE )
    AND ( v_rules.minimumDaysUntilTrainingStart <> 0 )
    AND ( v_rules.maximumDaysUntilTrainingStart <> 0 )
    THEN
        SELECT (p_begindate::date >= NOW()::date + v_rules.minimumDaysUntilTrainingStart
           AND p_begindate::date <= NOW()::date + v_rules.maximumDaysUntilTrainingStart )
          INTO v_valid;
    END IF;

    RETURN v_valid;

END;
$BODY$
LANGUAGE plpgsql VOLATILE;
