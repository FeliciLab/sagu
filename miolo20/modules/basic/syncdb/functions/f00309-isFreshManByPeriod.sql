CREATE OR REPLACE FUNCTION isFreshManByPeriod(p_contractid int, p_periodid varchar)
RETURNS BOOLEAN AS
$BODY$
DECLARE
    v_contrato_origem INT;
BEGIN
    SELECT INTO v_contrato_origem obterContratoTransferidoDeOrigem(p_contractid);

  /**
   * ATUALIZAÇÃO 01/06/2015 (ticket #38120): Função modificada para que reconheça
   * movimentações de pré-matrícula e/ou caso a última movimentação seja de VESTIBULANDO.
   */

    --Caso o aluno possui contrato de origem, é porque vem de transferência.
    IF (v_contrato_origem IS NOT NULL)
    THEN
        --Se o contrato de origem do aluno possui movimentações de matrícula, utiliza para a consulta de calouro.
        IF (SELECT COUNT(*) > 0
	      FROM acdMovementContract
	     WHERE contractId = v_contrato_origem
	       AND ( stateContractId IN (getParameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INTEGER,
                                         getparameter('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INTEGER) OR
                     getContractState(contractId) = getParameter('BASIC', 'WRITING_STATE_CONTRACT')::INTEGER ) )
	THEN
	    p_contractid := v_contrato_origem;     
	END IF;
    END IF;

    RETURN COALESCE((SELECT (LP.periodid = p_periodid)
                       FROM acdmovementcontract M
                 INNER JOIN acdlearningperiod LP 
                      USING (learningperiodid)
                      WHERE M.contractid = p_contractid
                        AND M.statecontractid IN (getparameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INTEGER,
                                                  getparameter('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INTEGER)
                   ORDER BY statetime ASC
                      LIMIT 1), 
                    (SELECT getContractState(p_contractid) = getParameter('BASIC', 'WRITING_STATE_CONTRACT')::INTEGER),
                      FALSE);
END;
$BODY$
LANGUAGE plpgsql;
