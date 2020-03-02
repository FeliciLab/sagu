CREATE OR REPLACE FUNCTION verificaSeAlunoEReingressanteNoPeriodo(p_contractid INT, p_learningperiodid INT)
RETURNS BOOLEAN AS
$BODY$
/*************************************************************************************
  NAME: verificaSeAlunoEReingressanteNoPeriodo
  PURPOSE: Verifica se o aluno é reingressante no curso no período letivo.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       05/03/2013 Augusto A. Silva  1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
BEGIN
      RETURN ( SELECT COUNT(*) > 0
                 FROM acdMovementContract
                WHERE contractId = p_contractid
                  AND learningPeriodId = p_learningperiodid
                  AND stateContractId = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_UNLOCKED')::INT );
END;
$BODY$
LANGUAGE plpgsql VOLATILE;
--
