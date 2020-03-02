CREATE OR REPLACE FUNCTION obterCidadeEstado(p_cityId int)
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterCidadeEstado
  DESCRIPTION: Obtem nome da Cidade - Estado
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN (
        SELECT name || ' - ' || stateId
          FROM basCity    
         WHERE cityId = p_cityId
    );
END; 
$BODY$ 
language plpgsql;
