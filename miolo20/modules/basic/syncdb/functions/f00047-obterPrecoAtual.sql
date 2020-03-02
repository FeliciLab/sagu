DROP TYPE IF EXISTS obterprecotype;
CREATE TYPE obterprecotype AS ( value float, startdate date, bankaccountid int, parcelsnumber int, fixedvalue float, maturityday integer,  firstparcelatsight boolean, firstParcelatsightfreshman boolean, valueisfixed boolean, valorcreditoferias float, parceltype char(1));
CREATE OR REPLACE FUNCTION obterPrecoAtual(p_courseId VARCHAR, p_courseVersion INT, p_turnid INT, p_unitid INT, p_date DATE)
RETURNS SETOF obterprecotype AS
$BODY$
/*********************************************************************************************
  NAME: obterPrecoAtual
  PURPOSE: Obtem o preco atual do curso e periodo letivo.
*********************************************************************************************/
DECLARE
    V_DATE DATE;
    V_ROW obterprecotype;
BEGIN
    SELECT INTO V_DATE A.startDate
                  FROM finPrice A
                 WHERE A.courseId = p_courseId
                   AND A.courseVersion = p_courseVersion
                   AND A.turnId = p_turnId
                   AND A.unitId = p_unitId
                   AND p_date BETWEEN A.startDate AND A.endDate;

    SELECT INTO V_ROW value::float,
                      startdate,
                      bankaccountid,
                      parcelsnumber,
                      fixedvalue,
                      maturityday,
                      firstparcelatsight,
                      firstparcelatsightfreshman,
                      valueisfixed,
                      valorcreditoferias,
                      parceltype
                 FROM finprice
                WHERE courseid = p_courseid
                  AND courseversion = p_courseversion
                  AND turnid = p_turnid
                  AND unitid = p_unitid
                  AND startdate = V_DATE
                  ;

    RETURN NEXT V_ROW;
END
$BODY$
LANGUAGE 'plpgsql';
