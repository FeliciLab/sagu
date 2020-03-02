CREATE OR REPLACE FUNCTION obterCodPessoaPai(p_personId bigint)
   RETURNS BIGINT AS
/*************************************************************************************
  NAME: obterCodPessoaPai
  DESCRIPTION: Obtem codigo da pessoa pai
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN ( SELECT personId FROM basPhysicalPersonKinship WHERE relativePersonId = p_personId AND kinshipid = GETPARAMETER('BASIC', 'FATHER_KINSHIP_ID')::int LIMIT 1 );
END; 
$BODY$ 
language plpgsql;
