--
-- 04/07/2011 - Fabiano Tomasini - Validação de peréodo mínimo para novo estégio para determinada modalidade
-- 09/01/2013 - Augusto A. Silva - Adicionada verificação de uitAreaId, caso desejado solicitar mais de uma érea.
--
-- Permissão para o formulério FrmSubscription
CREATE OR REPLACE FUNCTION tra.fn_chk_minimum_interval_to_new_request_to_training_type(
    p_unitAreaId tra.request.requestId%TYPE,
    p_requestId tra.request.requestId%TYPE,
    p_personId tra.request.personId%TYPE,
    p_trainingTypeId tra.request.trainingtypeid%TYPE,
    p_begindate tra.request.begindate%TYPE,
    p_enddate tra.request.enddate%TYPE)

RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: tra.fn_chk_minimum_interval_to_new_request_to_training_type
  PURPOSE:FUNÇÃO que verifica se a pessoa ja cursou um estégio na instituição e esté obedecendo o peréodo de caréncia para solicitar um novo
  estégio na mesma modalidade.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       01/07/2011 Fabiano Tomasini 1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_valid boolean;
    v_request_id integer;
BEGIN

    SELECT requestid FROM tra.request INTO v_request_id WHERE requestid = p_requestid;

    SELECT COUNT(*) = 0
      FROM tra.requestCurrentData A
      INTO v_valid
INNER JOIN tra.trainingType B
        ON (A.trainingTypeId = B.trainingTypeId)
     WHERE A.status != 'D' --desistente
       AND A.status != 'N' --não autorizado
       AND personId = p_personId
       AND A.trainingtypeid = p_trainingtypeid
       AND p_begindate::date < A.endDate::date + B.minimumInterval
       AND B.minimumInterval IS NOT NULL
       AND B.minimumInterval !=0
       AND (v_request_id IS NULL OR A.requestId != v_request_id)
       AND unitAreaId = p_unitAreaId;

    RETURN v_valid;

END;
$BODY$
LANGUAGE plpgsql VOLATILE;
--
