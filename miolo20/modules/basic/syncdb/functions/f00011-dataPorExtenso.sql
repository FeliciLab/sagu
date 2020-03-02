CREATE OR REPLACE FUNCTION dataPorExtenso(p_data date)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: dataPorExtenso
  PURPOSE: Gera a representação por extenso de uma data fornecida por parâmetro

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       19/07/2011 Alex Smith        1. FUNÇÃO criada.
**************************************************************************************/
BEGIN
    RETURN EXTRACT(day FROM p_data) || ' de ' || obterMesExtenso(EXTRACT(month FROM p_data)::int) || ' de ' || EXTRACT(year FROM p_data);
END;
$BODY$
LANGUAGE 'plpgsql';
