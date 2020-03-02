/*************************************************************************************
  NAME: dataestanointervalo
  PURPOSE: Retorna TRUE se uma dada data está no intervalo especificado
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       XX/XX/2014 XXXXXXXXXX        1. Função criada.
**************************************************************************************/
CREATE OR REPLACE FUNCTION dataEstaNoIntervalo (a_data DATE, in_data DATE, fin_data DATE) 
RETURNS BOOLEAN AS
$BODY$
    DECLARE

    BEGIN

    RETURN (a_data >= in_data AND a_data <= fin_data); 

    END;

$BODY$ LANGUAGE plpgsql;
