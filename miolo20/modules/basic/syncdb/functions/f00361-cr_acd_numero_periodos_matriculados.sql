CREATE OR REPLACE FUNCTION cr_acd_numero_periodos_matriculados(p_contractid integer)
RETURNS INTEGER AS $BODY$
DECLARE
BEGIN
    RETURN COUNT(periodid)
      FROM (SELECT DISTINCT periodid
              FROM acdmovementcontract
        INNER JOIN acdlearningperiod
             USING (learningperiodid)
             WHERE statecontractid = getparameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::integer
               AND learningperiodid IS NOT NULL
               AND contractid = p_contractid) as a;
END;
$BODY$ language plpgsql;
