CREATE OR REPLACE FUNCTION obtem_situacao_contrato(p_contractid integer, p_periodid varchar)
  RETURNS integer AS
/******************************************************************************
  NAME: obtem_situacao_contrato
  DESCRIPTION: Obtém a situação do contrato no período informado
  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       25/01/15   ftomasini      1. Função criada.
******************************************************************************/
$BODY$
DECLARE
    v_result_situacao int;
BEGIN

--ok
--Não renovados ( (ESTADO = 1)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 1
           FROM acdContract L --Não renovados
     INNER JOIN acdLearningPeriod G
             ON (G.courseId = L.courseId
            AND G.courseVersion = L.courseVersion
            AND G.turnId = L.turnId
            AND G.unitId = L.unitId)
     INNER JOIN acdPeriod P
             ON P.periodId = G.periodId
          WHERE (G.periodid = p_periodid)
            AND (L.contractId = p_contractid)
            AND (getcontractstatebyperiod(L.contractId, G.periodid) IS NULL)
            AND (isacademicenrolledinperiod(L.contractId, G.periodid) IS FALSE)
            AND (isacademicenrolledinperiod(L.contractId, (SELECT LP.periodId -- Estava academicamente matriculado no último período movimentado
                                                             FROM acdMovementContract MC
                                                       INNER JOIN acdLearningPeriod LP
                                                               ON LP.learningPeriodId = MC.learningPeriodId
                                                            WHERE contractId = L.contractId
                                                              AND LP.periodId <> G.periodid
                                                         ORDER BY stateTime DESC
                                                            LIMIT 1))) IS TRUE
            AND (getcontractstate(L.contractId) IN (SELECT statecontractid FROM acdstatecontract WHERE isclosecontract IS FALSE ));
END IF;

--Cancelamentos ( (ESTADO = 2)
IF (v_result_situacao IS NULL)
THEN

    SELECT INTO v_result_situacao 2
           FROM acdContract M --Cancelamentos
     INNER JOIN acdLearningPeriod G
             ON (G.courseId = m.courseId
            AND G.courseVersion = m.courseVersion
            AND G.turnId = m.turnId
            AND G.unitId = m.unitId)
          WHERE M.contractId = p_contractid
            AND getcontractstatebyperiod(M.contractid, p_periodid) = getparameter('ACADEMIC','STATE_CONTRACT_ID_LOCKED')::int
            AND (SELECT COUNT(SSM.*) > 0
                   FROM ( SELECT SM.learningperiodid
                            FROM acdMovementContract SM
                           WHERE SM.contractid=p_contractid
                             AND SM.stateContractId = getparameter('BASIC','WRITING_STATE_CONTRACT')::int
                             AND (SM.learningperiodid = G.learningPeriodId OR SM.stateTime BETWEEN G.beginDate AND G.endDate)
               GROUP BY SM.learningPeriodId) AS SSM)
       AND G.periodid = p_periodid;
END IF;


--Trancamentos ( (ESTADO = 3)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 3
           FROM acdContract K --Trancamentos
          WHERE K.contractId = p_contractid
            AND getcontractstate(K.contractid) = getparameter('ACADEMIC','STATE_CONTRACT_ID_LOCKED')::int;
END IF;

--Transferidos (S) (ESTADO = 4)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 4
           FROM acdContract J 
          WHERE J.contractId = p_contractid
            AND getcontractstate(J.contractid) = getparameter('ACADEMIC','STATE_CONTRACT_ID_EXTERNAL_TRANSFER_TO')::int;
END IF;

--Transferidos (E) (ESTADO = 5)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 5
      FROM acdContract I --Transferidos (E)
INNER JOIN acdLearningPeriod G
        ON (G.courseId = i.courseId
       AND G.courseVersion = i.courseVersion
       AND G.turnId = i.turnId
       AND G.unitId = i.unitId)
     WHERE i.contractid = p_contractid
       AND ( SELECT COUNT(SSE.*) >= 1
               FROM (SELECT SE.learningperiodid
                       FROM acdMovementContract SE
                      WHERE SE.contractid=i.contractId
                        AND SE.stateContractId = getparameter('ACADEMIC','STATE_CONTRACT_ID_EXTERNAL_TRANSFER_FROM')::int --Transferencia externa de
                        AND ( SE.statetime BETWEEN G.beginDate AND G.endDate OR SE.learningperiodid = G.learningperiodid )
                   GROUP BY SE.learningPeriodId) AS SSE )
       AND G.periodid = p_periodid
       AND isacademicenrolledinperiod(i.contractid, G.periodid);
END IF;

--Reingresso (ESTADO = 6)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 6
      FROM acdContract H --Reingresso
     INNER JOIN acdLearningPeriod G
             ON (G.courseId = h.courseId
            AND G.courseVersion = h.courseVersion
            AND G.turnId = h.turnId
            AND G.unitId = h.unitId)
     WHERE  H.contractid = p_contractid
       AND ( SELECT COUNT(SSI.*) >= 1
               FROM (SELECT SI.learningperiodid
                       FROM acdMovementContract SI
                      WHERE SI.contractid=h.contractId
                        AND SI.stateContractId = getparameter('ACADEMIC','STATE_CONTRACT_ID_UNLOCKED')::int
                        AND ( SI.statetime BETWEEN G.beginDate AND G.endDate OR SI.learningperiodid = G.learningperiodid )
                   GROUP BY SI.learningPeriodId) AS SSI )
       AND G.periodid = p_periodid
       AND isacademicenrolledinperiod(h.contractid, G.periodid);
END IF;

--Portador de diploma (ESTADO = 12)
IF (v_result_situacao IS NULL)
THEN
SELECT INTO v_result_situacao 12
       FROM acdContract B 
 INNER JOIN acdLearningPeriod G
         ON (G.courseId = b.courseId
            AND G.courseVersion = b.courseVersion
            AND G.turnId = b.turnId
            AND G.unitId = b.unitId)
     WHERE b.contractid = p_contractid
       AND ( SELECT COUNT(SSM.*) >= 1
               FROM ( SELECT SM.learningperiodid
                        FROM acdMovementContract SM
                        --Tem movimentacao de vestibulando 
                       WHERE SM.contractid IN (SELECT contractId 
                                                 FROM acdContract 
                                                WHERE personId = b.personId 
                                                  AND courseId = b.courseId)
                         AND SM.stateContractId = getparameter('BASIC','STATE_CONTRACT_ID_DIPLOMA')::int
                        --Não tem nehuma outra movimentacao de ajuste e matricula
                         AND (CASE 
                                  WHEN (SELECT count(*) < 1 
                                          FROM acdContract 
                                         WHERE personId = b.personId 
                                           AND courseId = b.courseId) THEN (SM.statecontractid = getparameter('BASIC','STATE_CONTRACT_ID_ENROLLED')::int OR SM.statecontractid = getparameter('ACADEMIC','STATE_CONTRACT_ID_ADJUSTMENT')::int)  --Matricula
                              ELSE
                                  TRUE
                              END)
                    GROUP BY SM.learningPeriodId) AS SSM)
       AND isacademicenrolledinperiod(b.contractid, G.periodid)
       AND G.periodid = p_periodid;
END IF;

--Renovacoes (ESTADO = 7)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 7
      FROM acdContract C 
     WHERE c.contractid = p_contractid
       AND ( SELECT COUNT(SSR.*) > 1
               FROM ( SELECT SR.learningperiodid
                        FROM acdMovementContract SR
                       WHERE SR.contractid=C.contractId
                         AND SR.stateContractId != getparameter('BASIC','WRITING_STATE_CONTRACT')::int
                    GROUP BY SR.learningPeriodId) AS SSR )
       AND isacademicenrolledinperiod(C.contractId, p_periodid);
END IF;

--Pré-Matriculados (ESTADO = 8)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 8
      FROM acdContract N --Pré-Matriculados
     WHERE n.contractId = p_contractid
       AND getcontractstate(n.contractId) = GETPARAMETER('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INTEGER;
END IF;

--Vestibulandos (ESTADO = 9)
IF (v_result_situacao IS NULL)
THEN
SELECT INTO v_result_situacao 9  
       FROM acdContract B 
 INNER JOIN acdLearningPeriod G
         ON (G.courseId = b.courseId
            AND G.courseVersion = b.courseVersion
            AND G.turnId = b.turnId
            AND G.unitId = b.unitId)
     WHERE b.contractid = p_contractid
       AND ( SELECT COUNT(SSM.*) >= 1
               FROM ( SELECT SM.learningperiodid
                        FROM acdMovementContract SM
                        --Tem movimentacao de vestibulando 
                       WHERE SM.contractid IN (SELECT contractId 
                                                 FROM acdContract 
                                                WHERE personId = b.personId 
                                                  AND courseId = b.courseId)
                         AND SM.stateContractId = getparameter('BASIC','WRITING_STATE_CONTRACT')::int
                        --Não tem nehuma outra movimentacao de ajuste e matricula
                         AND (CASE 
                                  WHEN (SELECT count(*) < 1 
                                          FROM acdContract 
                                         WHERE personId = b.personId 
                                           AND courseId = b.courseId) THEN (SM.statecontractid = getparameter('BASIC','STATE_CONTRACT_ID_ENROLLED')::int OR SM.statecontractid = getparameter('ACADEMIC','STATE_CONTRACT_ID_ADJUSTMENT')::int)  --Matricula
                              ELSE
                                  TRUE
                              END)
                    GROUP BY SM.learningPeriodId) AS SSM)
       AND isacademicenrolledinperiod(b.contractid, G.periodid)
       AND G.periodid = p_periodid;
END IF;

--Concluínte (ESTADO = 10)
IF (v_result_situacao IS NULL)
THEN
    SELECT INTO v_result_situacao 10
           FROM acdContract K
          WHERE K.contractId = p_contractid
            AND getcontractstate(K.contractid) = getparameter('ACADEMIC', 'STATE_CONTRACT_ID_ALL_FINISHED')::int;
END IF;

--NDA Matriculado (ESTADO = 11)
IF (v_result_situacao IS NULL AND isacademicenrolledinperiod(p_contractid, p_periodid))
THEN
    v_result_situacao := 11;
END IF;

IF (v_result_situacao IS NULL)
THEN
    --SEM situacao
    v_result_situacao:=0;
END IF;

    RETURN v_result_situacao;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;