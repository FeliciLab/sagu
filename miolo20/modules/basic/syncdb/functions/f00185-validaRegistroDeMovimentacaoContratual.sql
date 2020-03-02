--
CREATE OR REPLACE FUNCTION validaRegistroDeMovimentacaoContratual(p_contractid int, p_statecontractid int, p_learningperiodid int)
RETURNS boolean AS
$BODY$
DECLARE	
BEGIN
        PERFORM * FROM basconfig LIMIT 1;
        IF NOT FOUND
        THEN
            RETURN TRUE; --Caso não haja nenhum dado na basconfig retorna como true. Isso é para resolver o bug do postgres que não ignora os check no dump
        END IF;

	-- Se o estado contratual for de matrícula e não está sendo registrado período letivo, retorna false.
	IF ( p_statecontractid = getParameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INT OR
	     p_statecontractid = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INT )
	THEN
		IF p_learningperiodid IS NULL
		THEN
			RAISE EXCEPTION 'Para ser registrada uma movimentação contratual de pré-matrícula ou matrícula, é requerido o período letivo. Contate o administrador do sistema.';
		END IF;
	END IF;

	RETURN TRUE;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

