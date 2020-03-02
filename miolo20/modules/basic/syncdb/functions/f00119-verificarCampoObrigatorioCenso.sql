--
-- 27/03/2013 - Augusto A. Silva
-- FUNÇÕES A SEGUIR CRIADAS PARA A GERAÇÃO DO ARQUIVO DE CENSO PARA FAMETRO
--

--
CREATE OR REPLACE FUNCTION verificarCampoObrigatorioCenso(p_val VARCHAR)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: verificarCampoObrigatorioCenso
  PURPOSE: Verifica se o valor trazido por parâmetro é nulo setando 'Campo obrigatório' caso sim.
**************************************************************************************/
BEGIN
    RETURN 
    ( CASE WHEN p_val IS NULL
           THEN
                'Campo obrigatório'
           ELSE
                p_val
      END );
END;
$BODY$
LANGUAGE 'plpgsql';
--
