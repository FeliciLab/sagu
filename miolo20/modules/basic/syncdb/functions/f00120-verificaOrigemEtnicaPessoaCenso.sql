--
CREATE OR REPLACE FUNCTION verificaOrigemEtnicaPessoaCenso(p_val INT)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: verificaSexoPessoaCenso
  PURPOSE: Valida o id da entina para ocenso cujos ids s?o diferentes do sagu.
**************************************************************************************/
BEGIN
    RETURN 
    ( CASE WHEN p_val = 2 THEN 3
           WHEN p_val = 3 THEN 2
           WHEN p_val IS NULL THEN 6
           ELSE p_val
      END );
END;
$BODY$
LANGUAGE 'plpgsql';
--

