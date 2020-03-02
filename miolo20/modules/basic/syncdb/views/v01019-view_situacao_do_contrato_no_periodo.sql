CREATE OR REPLACE VIEW view_situacao_do_contrato_no_periodo AS (
SELECT b.begindate AS data, b.periodid AS periodo, a.contractid AS contrato, a.personid AS pessoa, a.courseid AS curso, a.courseversion AS versao, a.turnid AS turno, a.unitid AS unidade,
        CASE
            WHEN ( SELECT count(DISTINCT view_movimentacoes.periodo) > 1
               FROM view_movimentacoes
              WHERE view_movimentacoes.datahora <= b.enddate AND view_movimentacoes.codigo = getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.contrato = a.contractid
              GROUP BY view_movimentacoes.contrato) THEN
            CASE
                WHEN (EXISTS ( SELECT aa.enrollid
                   FROM acdenroll aa
              JOIN acdgroup bb USING (groupid)
         JOIN acdlearningperiod cc ON cc.learningperiodid = bb.learningperiodid
        WHERE cc.periodid::text = b.periodid::text AND aa.contractid = a.contractid AND (aa.statusid = ANY (ARRAY[getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_ENROLLED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_APPROVED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_DISAPPROVED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS'::character varying)::integer])))) THEN
                CASE
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_UNLOCKED'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.contrato = a.contractid AND view_movimentacoes.datahora <= b.enddate
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Reingresso'::text
                    ELSE 'Renovação'::text
                END
                ELSE
                CASE
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_LOCKED'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Trancamento'::text
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_EXTERNAL_TRANSFER_TO'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Transferência (S)'::text
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN
                    CASE
                        WHEN ( SELECT getcoursename(ab.courseid)::text = getcoursename(ba.courseid)::text
                           FROM view_movimentacoes aa
                      JOIN acdcontract ab ON ab.contractid = aa.contrato
                 JOIN acdcontract ba ON ba.personid = ab.personid
            JOIN view_movimentacoes bb ON bb.contrato = ba.contractid AND bb.contrato <> aa.contrato
           WHERE bb.datahora >= aa.datahora AND aa.contrato = a.contractid AND aa.periodo::text = b.periodid::text AND aa.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO'::character varying)::integer AND bb.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_FROM'::character varying)::integer
           ORDER BY bb.datahora
          LIMIT 1) THEN 'Mudança de turno/versão'::text
                        ELSE 'Mudança de curso (S)'::text
                    END
                    WHEN ( SELECT view_movimentacoes.codigo = ANY (ARRAY[getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_ALL_FINISHED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_GRADUATION'::character varying)::integer])
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Concluintes'::text
                    ELSE 'Outros (S)'::text
                END
            END
            ELSE
            CASE
                WHEN (EXISTS ( SELECT aa.enrollid
                   FROM acdenroll aa
              JOIN acdgroup bb USING (groupid)
         JOIN acdlearningperiod cc ON cc.learningperiodid = bb.learningperiodid
        WHERE cc.periodid::text = b.periodid::text AND aa.contractid = a.contractid AND (aa.statusid = ANY (ARRAY[getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_ENROLLED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_APPROVED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_DISAPPROVED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'ENROLL_STATUS_DISAPPROVED_FOR_LACKS'::character varying)::integer])))) THEN
                CASE
                    WHEN (EXISTS ( SELECT view_movimentacoes.codigo
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.contrato = a.contractid AND view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_DIPLOMA_HOLDER'::character varying)::integer)) THEN 'Portador de diploma'::text
                    WHEN (EXISTS ( SELECT view_movimentacoes.codigo
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.contrato = a.contractid AND view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_EXTERNAL_TRANSFER_FROM'::character varying)::integer)) THEN 'Transferência (E)'::text
                    WHEN (EXISTS ( SELECT view_movimentacoes.codigo
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.contrato = a.contractid AND view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_FROM'::character varying)::integer)) THEN
                    CASE
                        WHEN ( SELECT getcoursename(ab.courseid)::text = getcoursename(ba.courseid)::text AND ( SELECT count(DISTINCT view_movimentacoes.periodo) > 1
                                   FROM view_movimentacoes
                                  WHERE view_movimentacoes.codigo = getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND (view_movimentacoes.contrato = ANY (ARRAY[ab.contractid, ba.contractid])))
                           FROM view_movimentacoes aa
                      JOIN acdcontract ab ON ab.contractid = aa.contrato
                 JOIN acdcontract ba ON ba.personid = ab.personid
            JOIN view_movimentacoes bb ON bb.contrato = ba.contractid AND bb.contrato <> aa.contrato
           WHERE bb.datahora <= aa.datahora AND aa.contrato = a.contractid AND aa.periodo::text = b.periodid::text AND aa.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_FROM'::character varying)::integer AND bb.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO'::character varying)::integer
           ORDER BY bb.datahora DESC
          LIMIT 1) THEN 'Renovação'::text
                        ELSE 'Matrícula'::text
                    END
                    WHEN (EXISTS ( SELECT view_movimentacoes.codigo
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.contrato = a.contractid AND (view_movimentacoes.codigo = ANY (ARRAY[getparameter('BASIC'::character varying, 'WRITING_STATE_CONTRACT'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_SUBSCRIPTION'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_ENEM'::character varying)::integer])))) THEN 'Matr�cula'::text
                    ELSE 'Outros (E)'::text
                END
                ELSE
                CASE
                    WHEN ( SELECT bb.isclosecontract = true
                       FROM view_movimentacoes aa
                  JOIN acdstatecontract bb ON bb.statecontractid = aa.codigo
                 WHERE aa.contrato = a.contractid AND aa.periodo::text = b.periodid::text
                 ORDER BY aa.datahora DESC
                LIMIT 1) AND (EXISTS ( SELECT view_movimentacoes.periodo
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.contrato = a.contractid AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.codigo = getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer)) THEN 'Cancelamento'::text
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_LOCKED'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Trancamento'::text
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_EXTERNAL_TRANSFER_TO'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Transferência (S)'::text
                    WHEN ( SELECT view_movimentacoes.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO'::character varying)::integer
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.codigo <> getparameter('BASIC'::character varying, 'STATE_CONTRACT_ID_ENROLLED'::character varying)::integer AND view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN
                    CASE
                        WHEN ( SELECT getcoursename(ab.courseid)::text = getcoursename(ba.courseid)::text
                           FROM view_movimentacoes aa
                      JOIN acdcontract ab ON ab.contractid = aa.contrato
                 JOIN acdcontract ba ON ba.personid = ab.personid
            JOIN view_movimentacoes bb ON bb.contrato = ba.contractid AND bb.contrato <> aa.contrato
           WHERE bb.datahora >= aa.datahora AND aa.contrato = a.contractid AND aa.periodo::text = b.periodid::text AND aa.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO'::character varying)::integer AND bb.codigo = getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_FROM'::character varying)::integer
           ORDER BY bb.datahora
          LIMIT 1) THEN 'Mudança de turno/versão'::text
                        ELSE 'Mudança de curso (S)'::text
                    END
                    WHEN ( SELECT view_movimentacoes.codigo = ANY (ARRAY[getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_ALL_FINISHED'::character varying)::integer, getparameter('ACADEMIC'::character varying, 'STATE_CONTRACT_ID_GRADUATION'::character varying)::integer])
                       FROM view_movimentacoes
                      WHERE view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid
                      ORDER BY view_movimentacoes.datahora DESC
                     LIMIT 1) THEN 'Concluintes'::text
                    ELSE 'Não matriculado'::text
                END
            END
        END AS situacao
   FROM acdcontract a
   JOIN acdlearningperiod b ON b.courseid::text = a.courseid::text AND b.courseversion = a.courseversion AND b.turnid = a.turnid AND b.unitid = a.unitid
  WHERE (EXISTS ( SELECT view_movimentacoes.contrato
      FROM view_movimentacoes
     WHERE view_movimentacoes.periodo::text = b.periodid::text AND view_movimentacoes.contrato = a.contractid))
  ORDER BY a.contractid, b.periodid);
