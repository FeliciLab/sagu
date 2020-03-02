
--
-- Augusto A. Silva - 10/05/2013
--
CREATE OR REPLACE FUNCTION obterPeriodoPelaData(p_date DATE)
RETURNS VARCHAR AS
$BODY$
/*************************************************************************************
  NAME: obterPeriodoPelaData
  PURPOSE: Obtém o código do perído em que a data recebida por parâmetro pertence.
**************************************************************************************/

BEGIN
    RETURN ( SELECT periodId 
	       FROM acdlearningperiod 
	      WHERE p_date BETWEEN beginDate AND endDate
	      LIMIT 1 );
END;
$BODY$
LANGUAGE 'plpgsql';
