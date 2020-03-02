CREATE OR REPLACE FUNCTION inscritoEstaClassificadoNaEtapaAnterior(p_subscriptionId int, p_selectiveProcessId int, p_stepIdAtual int)
RETURNS BOOLEAN AS
$BODY$
BEGIN
    RETURN (COALESCE((SELECT (B.subscriptionstatusid = 2) AS statusnaetapaanterior
               FROM spr.subscription A
         INNER JOIN spr.subscriptionstepinfo B
              USING (subscriptionId)
         INNER JOIN spr.step C
             ON C.stepId = B.stepId
              WHERE A.subscriptionId = p_subscriptionId
            AND B.stepId = (SELECT stepid
                      FROM spr.step
                     WHERE selectiveprocessid = p_selectiveProcessId
                       AND steporder < (SELECT steporder
                                  FROM spr.step
                                 WHERE stepId = p_stepIdAtual)
                      ORDER BY steporder DESC
                     LIMIT 1)), TRUE));
END;
$BODY$
LANGUAGE plpgsql;
