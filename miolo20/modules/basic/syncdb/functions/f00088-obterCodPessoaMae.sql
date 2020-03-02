CREATE OR REPLACE FUNCTION obterCodPessoaMae(p_personId bigint)
   RETURNS BIGINT AS
/*************************************************************************************
  NAME: obterCodPessoaMae
  DESCRIPTION: Obtem codigo da pessoa mae
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN ( SELECT personId FROM basPhysicalPersonKinship WHERE relativePersonId = p_personId AND kinshipid = GETPARAMETER('BASIC', 'MOTHER_KINSHIP_ID')::int LIMIT 1 );
END; 
$BODY$ 
language plpgsql;
