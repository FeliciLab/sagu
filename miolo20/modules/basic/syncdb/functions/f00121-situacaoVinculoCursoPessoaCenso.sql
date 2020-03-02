--
CREATE OR REPLACE FUNCTION situacaoVinculoCursoPessoaCenso(p_val VARCHAR)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: situacaoVinculoCursoPessoaCenso
  PURPOSE: Valida o id do estado contratual para o censo cujos ids s?o diferentes do sagu.
**************************************************************************************/
BEGIN
    RETURN 
    ( CASE WHEN ( p_val = 'Matricula' OR p_val = 'Reingresso' OR p_val = 'Renovacao' ) 
           THEN 
                '2' --MATRICULADO / CURSANDO
           WHEN ( p_val = 'Trancamento' OR p_val = 'Cancelamento' ) 
           THEN 
                '3' --TRANCAMENTO
           WHEN ( p_val = 'Mudanca de curso (S)' )  
           THEN 
                '4' --DESVINCULADO DO CURSO
           WHEN ( p_val = 'Transferencia (S)' OR p_val = 'Transferencia (E)' ) 
           THEN 
                '5' --TRANSFER?NCIA
           WHEN ( p_val = 'Concluintes' ) 
           THEN 
                '6' --COLAÇ?O DE GRAU / FORMADO
           WHEN ( p_val = 'Falecimento' ) 
           THEN 
                '7' --FALECIMENTO
           ELSE 
                p_val
      END );
END;
$BODY$
LANGUAGE 'plpgsql';
--
