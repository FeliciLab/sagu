CREATE OR REPLACE FUNCTION obterContratoTransferidoDeOrigem(p_contractId INT)
RETURNS INTEGER AS 
$BODY$
/******************************************************************************
  NAME: obterContratoTransferidoDeOrigem
  PURPOSE: Retorna o contrato de origem do processo de transferencia.
  DESCRIPTION:
  Pelo contrato atual do aluno, é possível obter qual o contrado de origem, caso tenha efetuado
  transferência de contrato.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       22/09/2014 Augusto A. Silva  1. FUNÇÃO criada.
******************************************************************************/
BEGIN
	RETURN ( SELECT contratoDeOrigemId
		   FROM acdTransferencia
		  WHERE contratoDeDestinoId = p_contractId
               ORDER BY datetime 
             DESC LIMIT 1 );
END;
$BODY$
LANGUAGE plpgsql
IMMUTABLE;
