CREATE OR REPLACE FUNCTION isEmployeeActive(p_personid bigint)
RETURNS BOOLEAN AS $BODY$
DECLARE
BEGIN
    RETURN EXISTS(
        SELECT 1
          FROM basEmployee E
         WHERE E.personId = p_personid
           AND NOW() BETWEEN COALESCE(beginDate, '01-01-0001'::date) AND COALESCE(endDate, '01-01-3000'::date)
    );
END;
$BODY$ language plpgsql;
--
