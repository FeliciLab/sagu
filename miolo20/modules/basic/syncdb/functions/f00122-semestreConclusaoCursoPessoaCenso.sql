--
CREATE OR REPLACE FUNCTION semestreConclusaoCursoPessoaCenso(p_contractId INT)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: semestreConclusaoCursoPessoaCenso
  PURPOSE: Verifica o semestre em que foi concluído o curso, caso tenha sido.
**************************************************************************************/
BEGIN
    RETURN ( CASE WHEN ( 11 IN ( SELECT stateContractId
                                   FROM acdMovementContract
                                  WHERE contractId = p_contractId ) ) -- Verifica se o contrato possui movimentaç?o de colaç?o de grau
                  THEN
                       ( CASE WHEN ( TO_CHAR( ( SELECT endDate
						  FROM acdLearningPeriod
						 WHERE learningPeriodId = ( SELECT learningPeriodId
									      FROM acdMovementContract
									     WHERE contractId = p_contractId
									       AND stateContractId = 11 
                                                                          ORDER BY statetime DESC 
                                                                             LIMIT 1) ), 'mm' ) )::INT > 07
                              THEN
                                   '2'
                              ELSE
                                   '1'
                         END )
                  ELSE 
                       NULL
             END );
END;
$BODY$
LANGUAGE 'plpgsql';
--
