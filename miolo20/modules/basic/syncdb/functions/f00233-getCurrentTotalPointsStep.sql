CREATE OR REPLACE FUNCTION getCurrentTotalPointsStep(p_subscriptionid integer)
RETURNS FLOAT AS
$BODY$
DECLARE
    v_totalPoints FLOAT;
BEGIN
    SELECT INTO v_totalPoints 
                SSI.totalPoints
           FROM spr.subscriptionStepInfo SSI
     INNER JOIN spr.step S
             ON S.stepId = SSI.stepId
          WHERE SSI.totalPoints IS NOT NULL
            AND SSI.subscriptionId = p_subscriptionid
       ORDER BY S.stepOrder DESC
          LIMIT 1;

    RETURN v_totalPoints;
END;
$BODY$
LANGUAGE plpgsql;
