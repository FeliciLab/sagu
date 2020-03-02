CREATE OR REPLACE FUNCTION obterPeriodoAcademicoAnteriorAoPeriodo(p_periodId VARCHAR)
RETURNS VARCHAR AS
$BODY$
BEGIN
    RETURN (
        SELECT prevperiodid
          FROM acdPeriod
         WHERE periodId = p_periodId
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
