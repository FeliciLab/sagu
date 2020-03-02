DROP TYPE IF EXISTS getContractCurrentClassType CASCADE;
CREATE TYPE getContractCurrentClassType AS ( classid varchar , name text, contractid int , begindate date );
CREATE OR REPLACE FUNCTION getContractCurrentClass(p_contractid int)
RETURNS SETOF getContractCurrentClasstype AS
$BODY$
/*********************************************************************************************
  NAME: getContractCurrentClass
  PURPOSE: Obtem a turma atual do contrato..
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY (SELECT DISTINCT A.classId,
                                B.name,
                                A.contractId,
                                A.beginDate
                  FROM acdClassPupil A
            INNER JOIN acdClass B
                    ON B.classId = A.classId
                 WHERE A.contractId = p_contractid
                   AND (A.endDate IS NULL OR A.endDate > now()::date )
                   AND A.classId = getContractClassId(p_contractId)
              ORDER BY A.begindate DESC
                 LIMIT 1);
END
$BODY$
LANGUAGE 'plpgsql';
