CREATE OR REPLACE FUNCTION getBusinessDates(p_begindate date, p_enddate date)
  RETURNS SETOF date AS
$BODY$
/*************************************************************************************
  NAME: getbusinessdays
  DESCRIPTION: Obtem dias éteis validos (datas) nos periodos passados.
**************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY (SELECT dt::date
              FROM GENERATE_SERIES(p_beginDate, p_endDate, '1 day') dt
             WHERE EXTRACT(DOW FROM dt)::varchar = ANY (
                STRING_TO_ARRAY(GETPARAMETER('BASIC', 'INSTITUTION_BUSINESS_DAYS'), ',')
             )
    );
END
$BODY$
  LANGUAGE plpgsql;
