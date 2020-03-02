--
CREATE OR REPLACE FUNCTION corrigeMovimentacoesContratuaisDeMatricula()
RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: corrigeMovimentacoesContratuaisDeMatricula
  PURPOSE: Corrige as movimentações de matrícula sem período letivo.
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/12/2013 Augusto A. Silva  1. Função criada.
**************************************************************************************/
DECLARE
	V_MOVIMENTACOES RECORD;
	V_CONTRATO RECORD;
	V_PERIODOS_LETIVOS RECORD;
	V_PERIODO_LETIVO INT;
BEGIN

	FOR V_MOVIMENTACOES IN
		( SELECT DISTINCT contractid,
			 statecontractid,
			 statetime
		    FROM acdmovementcontract
		   WHERE statecontractid = getParameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INT
		     AND learningPeriodId IS NULL )
	LOOP
		SELECT INTO V_CONTRATO 
			    contractId,
			    courseId,
			    courseVersion,
			    turnId,
			    unitId
		       FROM acdContract
		      WHERE contractid = V_MOVIMENTACOES.contractid;

		
		SELECT INTO V_PERIODO_LETIVO learningPeriodId
		       FROM acdLearningPeriod
		      WHERE V_MOVIMENTACOES.statetime::DATE BETWEEN beginDate AND endDate
			AND ( courseId,
			      courseVersion,
			      turnId,
			      unitId ) = ( V_CONTRATO.courseId,
					   V_CONTRATO.courseVersion,
					   V_CONTRATO.turnId,
					   V_CONTRATO.unitId );

		UPDATE acdmovementcontract
		   SET learningPeriodId = V_PERIODO_LETIVO
		 WHERE ( contractid,
			 statecontractid,
			 statetime ) = ( V_MOVIMENTACOES.contractid,
					 V_MOVIMENTACOES.statecontractid,
					 V_MOVIMENTACOES.statetime );

		RAISE NOTICE 'contractId = %, stateContractId = %, statetime = %, learningPeriodId = %', V_MOVIMENTACOES.contractid, V_MOVIMENTACOES.statecontractid, V_MOVIMENTACOES.statetime, V_PERIODO_LETIVO;
		
	END LOOP;
	
	RETURN TRUE;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;

