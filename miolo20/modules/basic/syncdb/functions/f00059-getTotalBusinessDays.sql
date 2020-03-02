CREATE OR REPLACE FUNCTION getTotalBusinessDays(p_begindate date, p_enddate date)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: gettotalbusinessdays
  DESCRIPTION: Obtem o total de dias úteis entre um período de datas (total de
    dias - dias que nao sao considerados dias uteis pela instituicao).
**************************************************************************************/
DECLARE
BEGIN
    RETURN (SELECT COUNT(*) FROM getbusinessdates(p_beginDate, p_endDate));
END
$BODY$
  LANGUAGE plpgsql;
