--
-- 13/07/2011 - ftomasini - Acrescentado status desistente quando solicitante desiste da solicitação.
--
CREATE OR REPLACE FUNCTION tra.getRequestStatus(
    p_requestId tra.request.requestId%TYPE)

RETURNS VARCHAR AS
$BODY$
/*************************************************************************************
  NAME: getRequestStatus
  PURPOSE: Retorna o status de uma solicitação de estágio.

  DESCRIPTION:
  Retorna o status de uma solicitação de estágio.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       11/05/2011 Arthur Lehdermann 1. FUNÇÃO criada.
  1.1       13/07/2011 Fabiano Tomasini  1.1 Adicionado status desistente para quando
                                         solicitante desiste da solicitação.
**************************************************************************************/
DECLARE
    v_status RECORD;
    v_request_status VARCHAR;

BEGIN
    v_request_status := '';

    SELECT INTO v_status
                COUNT(C.subscriptionTeamId) AS subscriptionTeamData,
                B.status AS subscriptionStatus,
                A.status AS requestStatus
           FROM tra.request A
      LEFT JOIN tra.subscription B
             ON A.requestId = B.requestId
      LEFT JOIN tra.subscriptionTeam C
             ON B.subscriptionId = C.subscriptionId
          WHERE A.requestId = p_requestId
       GROUP BY 2,3;

    IF v_status.subscriptionTeamData > 0
    THEN
        v_request_status := 'ENTURMADO';
    ELSIF v_status.subscriptionStatus = 'P'
    THEN
        v_request_status := 'EM ANÁLISE';
    ELSIF v_status.subscriptionStatus = 'A'
    THEN
        v_request_status := 'INSCRITO';
    ELSIF v_status.subscriptionStatus = 'N'
    THEN
        v_request_status := 'NÃO ACEITO';
    ELSIF v_status.subscriptionStatus = 'D'
    THEN
        v_request_status := 'DESISTENTE';
    ELSIF v_status.requestStatus = 'D'
    THEN
        v_request_status := 'DESISTENTE';
    ELSIF v_status.requestStatus = 'P'
    THEN
        v_request_status := 'SOLICITADO';
    ELSIF v_status.requestStatus = 'A'
    THEN
        v_request_status := 'EM ANÁLISE';
    ELSIF v_status.requestStatus = 'N'
    THEN
        v_request_status := 'NÃO ACEITO';
    ELSE
        v_request_status := '-';
    END IF;

    RETURN v_request_status;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE;
--
