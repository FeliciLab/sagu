--
CREATE OR REPLACE FUNCTION verificarFormaDeIngressoCursoCenso(p_contractId INT, p_stateContractId INT)
RETURNS INT AS
$BODY$
/*************************************************************************************
  NAME: verificarFormaDeIngressoCursoCenso
  PURPOSE: Obtem os dados da forma de ingresso do aluno no curso.
**************************************************************************************/
BEGIN
    RETURN ( CASE WHEN ( SELECT stateContractId
		           FROM acdMovementContract 
		          WHERE contractId = p_contractId 
		            AND stateTime = ( SELECT MIN(stateTime) 
				           	FROM acdMovementContract 
                                               WHERE contractId = p_contractId ) 
		          LIMIT 1 ) = p_stateContractId
		  THEN 
                       1
                  ELSE
                       0
             END );
END;
$BODY$
LANGUAGE 'plpgsql';
--

CREATE OR REPLACE FUNCTION verificarSemestreDeIngressoCursoCenso(p_contractId INT)
RETURNS text AS
$BODY$
/*************************************************************************************
  NAME: verificarFormaDeIngressoCursoCenso
  PURPOSE: Obtem os dados da forma de ingresso do aluno no curso.
**************************************************************************************/
BEGIN
    RETURN ( CASE WHEN ( SELECT date_part( 'month', MIN(stateTime) )
				           	FROM acdMovementContract 
                                               WHERE contractId = p_contractId )<=6 
		          THEN 
                       '01'||( SELECT date_part( 'year', MIN(stateTime) )
				           	      FROM acdMovementContract 
                                 WHERE contractId = p_contractId )::text
                  ELSE
                       '02'||( SELECT date_part( 'year', MIN(stateTime) )
				              	  FROM acdMovementContract 
                                 WHERE contractId = p_contractId )::text
             END );
END;
$BODY$
LANGUAGE 'plpgsql';
--
