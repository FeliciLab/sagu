CREATE OR REPLACE FUNCTION totalDePontosObtidosPeloAluno( p_subscriptionid integer )
RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: totalDePontosObtidosPeloAluno
  PURPOSE: Retorna o total de pontos obtidos pelo aluno até a etapa atual de
           um processo seletivo.
**************************************************************************************/
DECLARE
BEGIN
    RETURN (  SELECT totalpoints
                FROM spr.subscriptionstepinfo AA
          INNER JOIN spr.subscription BB
                  ON ( AA.subscriptionid = BB.subscriptionid
                       AND AA.subscriptionid = p_subscriptionid)
               WHERE AA.stepid = (SELECT B.stepid 
                                   FROM spr.subscriptionstepinfo A
                             INNER JOIN spr.step B
                                     ON A.stepid = B.stepid
                                  WHERE selectiveprocessid = BB.selectiveprocessid
                                    AND totalpoints IS NOT NULL
                               ORDER BY steporder DESC
                                  LIMIT 1)   );
END;
$BODY$
LANGUAGE 'plpgsql';
--
