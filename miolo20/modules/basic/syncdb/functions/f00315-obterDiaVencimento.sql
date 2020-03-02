CREATE OR REPLACE FUNCTION obterDiaVencimento(p_contractid integer, p_learningperiodid integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: obterdiavencimento
  PURPOSE: Retorna o dia do vencimento conforme configuração

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       13/02/2013 Fabiano Tomasini    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_preco record;
    v_contrato record;
    v_periodo record;
    v_dia_vencimento integer;

BEGIN
    SELECT INTO v_contrato * FROM acdContract WHERE contractId = p_contractId;
    SELECT INTO v_periodo * FROM acdLearningPeriod WHERE learningperiodid = p_learningPeriodId;

    v_preco := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_periodo.begindate);
     

    IF v_preco.parceltype = 'D' 
    THEN

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Dia de vencimento: Dia da matrícula', '1 - CONFIGURAÇÕES DE PREÇO'); 

        --Obtém o dia de vencimento da primeira movimentação contratual de matricula ou pré-matricula
        SELECT INTO v_dia_vencimento EXTRACT('DAY' FROM statetime) FROM acdmovementcontract WHERE contractid = p_contractid AND learningperiodid = p_learningperiodid  AND statecontractid IN ( getparameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::int, getparameter('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::int ) ORDER BY statetime ASC LIMIT 1;
    ELSE
        --O dia de vencimento é definido pelo contrato
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Dia de vencimento: Dia definido no contrado do aluno', '1 - CONFIGURAÇÕES DE PREÇO');
        v_dia_vencimento := v_contrato.maturityday;

        -- Obtém do preço, caso não encontrado no contrato
        IF v_dia_vencimento IS NULL
        THEN
            SELECT INTO v_dia_vencimento A.maturityday
              FROM finPrice A
        INNER JOIN acdContract B
                ON (A.courseId, A.courseVersion, A.turnId, A.unitId) = (B.courseId, B.courseVersion, B.turnId, B.unitId)
             WHERE B.contractId = p_contractId
               AND NOW()::DATE >= A.startdate
               AND NOW()::DATE <= A.enddate;
        END IF;
    END IF;
    RETURN v_dia_vencimento;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
