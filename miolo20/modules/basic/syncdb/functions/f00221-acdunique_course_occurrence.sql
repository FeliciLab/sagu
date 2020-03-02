CREATE OR REPLACE FUNCTION acdunique_course_occurrence()
  RETURNS trigger AS
$BODY$
/*************************************************************************************
  NAME: acdunique_course_occurrence
  PURPOSE: Verifica se tem um contrato com o mesmo personId, courseId, courseVersion,  unitId, turnId e se está fechado.

  REVISIONS:
  Ver       Date       Author               Description
  --------- ---------- -------------------- ------------------------------------
  1.0       23/08/2011 Jonas Gualberto Diel 1. Função criada.
  2.0       08/05/2014 Nataniel I. da Silva 2.Alterada função para permitir alteração/inserção caso o contrato esteja fechado.
**************************************************************************************/
DECLARE
	v_count int;

	--Verifica se o contrato atualizado está fechado
	v_verificaContrato boolean;
BEGIN
	IF TG_OP = 'UPDATE' 
	THEN --Ação de update
		SELECT count(*) INTO v_count
		      FROM acdContract
		     WHERE courseId = NEW.courseId
		       AND courseVersion = NEW.courseVersion
		       AND turnId = NEW.turnId
		       AND unitId = NEW.unitId
		       AND personId = NEW.personId
		       AND contractId != NEW.contractId
		       AND getContractState(contractId) IN (SELECT stateContractId
							      FROM acdStateContract
							     WHERE inOutTransition != 'O');
 
	ELSE
		SELECT count(*) INTO v_count
		      FROM acdContract
		     WHERE courseId = NEW.courseId
		       AND courseVersion = NEW.courseVersion
		       AND turnId = NEW.turnId
		       AND unitId = NEW.unitId
		       AND personId = NEW.personId
		       AND getContractState(contractId) IN (SELECT stateContractId
							      FROM acdStateContract
							     WHERE inOutTransition != 'O');		
	END IF;

	--Verifica se o contrato que está sento atualizado está fechado
	SELECT count(*) > 0 INTO v_verificaContrato 
	  FROM acdcontract
	 WHERE contractid = NEW.contractId
	   AND ( SELECT isclosecontract 
		   FROM acdstatecontract
		  WHERE statecontractid = getContractState(contractId) ) = TRUE;

	IF ( v_count >= 1 ) THEN
	    IF ( v_verificaContrato = FALSE ) THEN
		RAISE EXCEPTION 'A Pessoa % já possui um contrato para o curso %/%, unidade %, turno %.', NEW.personId, NEW.courseId, NEW.courseVersion, NEW.unitId, NEW.turnId;
	    END IF;
	END IF;
 		       
	RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION acdunique_course_occurrence()
  OWNER TO postgres;
