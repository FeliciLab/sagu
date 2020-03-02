CREATE OR REPLACE VIEW receita AS
         SELECT DISTINCT b.personid AS cod_pessoa, a.invoiceid AS titulo, c.ournumber AS nosso_numero, b.maturitydate AS vencimento,
                CASE
                    WHEN d.situacao IS NOT NULL AND b.parcelnumber = 1 THEN d.situacao
                    ELSE e.description
                END AS descricao, ( SELECT sum(xx.value) AS sum
                   FROM lancamentos_financeiros xx
                  WHERE xx.invoiceid = a.invoiceid AND xx.operationtypeid = 'D'::bpchar AND NOT (EXISTS ( SELECT findefaultoperations.username, findefaultoperations.datetime, findefaultoperations.ipaddress, findefaultoperations.addcurricularcomponentoperation, findefaultoperations.cancelcurricularcomponentoperation, findefaultoperations.protocoloperation, findefaultoperations.interestoperation, findefaultoperations.discountoperation, findefaultoperations.libraryfineoperation, findefaultoperations.closeincomeforecastoperation, findefaultoperations.enrolloperation, findefaultoperations.paymentoperation, findefaultoperations.agreementoperation, findefaultoperations.banktaxoperation, findefaultoperations.selectiveprocesstaxoperation, findefaultoperations.bankclosingtaxoperation, findefaultoperations.renewaloperation, findefaultoperations.negotiationoperation, findefaultoperations.opencounteroperation, findefaultoperations.counterwithdrawaloperation, findefaultoperations.otherdiscountsoperation, findefaultoperations.otheradditionsoperation, findefaultoperations.monthlyfeeoperation, findefaultoperations.withdrawoperation
                           FROM findefaultoperations
                          WHERE findefaultoperations.interestoperation = xx.operationid OR findefaultoperations.otheradditionsoperation = xx.operationid))) AS valor_nominal, ( SELECT sum(xx.value) AS sum
                   FROM lancamentos_financeiros xx
                  WHERE xx.invoiceid = a.invoiceid AND xx.operationtypeid = 'C'::bpchar AND NOT (EXISTS ( SELECT findefaultoperations.username, findefaultoperations.datetime, findefaultoperations.ipaddress, findefaultoperations.addcurricularcomponentoperation, findefaultoperations.cancelcurricularcomponentoperation, findefaultoperations.protocoloperation, findefaultoperations.interestoperation, findefaultoperations.discountoperation, findefaultoperations.libraryfineoperation, findefaultoperations.closeincomeforecastoperation, findefaultoperations.enrolloperation, findefaultoperations.paymentoperation, findefaultoperations.agreementoperation, findefaultoperations.banktaxoperation, findefaultoperations.selectiveprocesstaxoperation, findefaultoperations.bankclosingtaxoperation, findefaultoperations.renewaloperation, findefaultoperations.negotiationoperation, findefaultoperations.opencounteroperation, findefaultoperations.counterwithdrawaloperation, findefaultoperations.otherdiscountsoperation, findefaultoperations.otheradditionsoperation, findefaultoperations.monthlyfeeoperation, findefaultoperations.withdrawoperation
                           FROM findefaultoperations
                          WHERE findefaultoperations.paymentoperation = xx.operationid))) AS descontos, 'BANCO'::text AS tipo_entrada, (((a.bankid::text || '-'::text) || a.branch::text) || '/'::text) || a.branchnumber::text AS conta_bancaria, a.occurrencedate AS data_pgto, a.creditdate AS data_credito, NULL::text AS forma_pagamento, a.valuepaid AS valor_pago, a.username AS usuario
           FROM fin.bankmovement a
      JOIN finreceivableinvoice b ON b.invoiceid = a.invoiceid
   JOIN finincomesource e ON e.incomesourceid = b.incomesourceid
   JOIN finbankinvoiceinfo c ON c.invoiceid = b.invoiceid
   LEFT JOIN view_situacao_do_contrato_no_periodo d ON d.contrato = (( SELECT finentry.contractid
   FROM finentry
  WHERE finentry.invoiceid = b.invoiceid AND finentry.contractid IS NOT NULL
  ORDER BY finentry.entryid DESC
 LIMIT 1)) AND d.periodo::text = ((( SELECT bb.periodid
   FROM finentry aa
   JOIN acdlearningperiod bb ON aa.learningperiodid = bb.learningperiodid
  WHERE aa.invoiceid = b.invoiceid
  ORDER BY aa.entryid DESC
 LIMIT 1))::text)
  WHERE a.bankmovementstatusid <> ALL (ARRAY[4, 5])
UNION
         SELECT DISTINCT b.personid AS cod_pessoa, a.invoiceid AS titulo, c.ournumber AS nosso_numero, b.maturitydate AS vencimento,
                CASE
                    WHEN d.situacao IS NOT NULL AND b.parcelnumber = 1 THEN d.situacao
                    ELSE e.description
                END AS descricao, ( SELECT sum(xx.value) AS sum
                   FROM lancamentos_financeiros xx
                  WHERE xx.invoiceid = a.invoiceid AND xx.operationtypeid = 'D'::bpchar AND NOT (EXISTS ( SELECT findefaultoperations.username, findefaultoperations.datetime, findefaultoperations.ipaddress, findefaultoperations.addcurricularcomponentoperation, findefaultoperations.cancelcurricularcomponentoperation, findefaultoperations.protocoloperation, findefaultoperations.interestoperation, findefaultoperations.discountoperation, findefaultoperations.libraryfineoperation, findefaultoperations.closeincomeforecastoperation, findefaultoperations.enrolloperation, findefaultoperations.paymentoperation, findefaultoperations.agreementoperation, findefaultoperations.banktaxoperation, findefaultoperations.selectiveprocesstaxoperation, findefaultoperations.bankclosingtaxoperation, findefaultoperations.renewaloperation, findefaultoperations.negotiationoperation, findefaultoperations.opencounteroperation, findefaultoperations.counterwithdrawaloperation, findefaultoperations.otherdiscountsoperation, findefaultoperations.otheradditionsoperation, findefaultoperations.monthlyfeeoperation, findefaultoperations.withdrawoperation
                           FROM findefaultoperations
                          WHERE findefaultoperations.interestoperation = xx.operationid OR findefaultoperations.otheradditionsoperation = xx.operationid))) AS valor_nominal, ( SELECT sum(xx.value) AS sum
                   FROM lancamentos_financeiros xx
                  WHERE xx.invoiceid = a.invoiceid AND xx.operationtypeid = 'C'::bpchar AND NOT (EXISTS ( SELECT findefaultoperations.username, findefaultoperations.datetime, findefaultoperations.ipaddress, findefaultoperations.addcurricularcomponentoperation, findefaultoperations.cancelcurricularcomponentoperation, findefaultoperations.protocoloperation, findefaultoperations.interestoperation, findefaultoperations.discountoperation, findefaultoperations.libraryfineoperation, findefaultoperations.closeincomeforecastoperation, findefaultoperations.enrolloperation, findefaultoperations.paymentoperation, findefaultoperations.agreementoperation, findefaultoperations.banktaxoperation, findefaultoperations.selectiveprocesstaxoperation, findefaultoperations.bankclosingtaxoperation, findefaultoperations.renewaloperation, findefaultoperations.negotiationoperation, findefaultoperations.opencounteroperation, findefaultoperations.counterwithdrawaloperation, findefaultoperations.otherdiscountsoperation, findefaultoperations.otheradditionsoperation, findefaultoperations.monthlyfeeoperation, findefaultoperations.withdrawoperation
                           FROM findefaultoperations
                          WHERE findefaultoperations.paymentoperation = xx.operationid))) AS descontos, 'CAIXA'::text AS tipo_entrada, NULL::text AS conta_bancaria, a.movementdate::date AS data_pgto, a.movementdate::date AS data_credito, ( SELECT finspecies.description
                   FROM finspecies
                  WHERE finspecies.speciesid = a.speciesid) AS forma_pagamento,
                CASE
                    WHEN a.operation = 'C'::bpchar THEN a.value
                    ELSE (-1)::numeric * a.value
                END AS valor_pago, ( SELECT bb.miolousername
                   FROM finopencounter aa
              JOIN basphysicalpersonemployee bb ON bb.personid = aa.operatorid
             WHERE aa.opencounterid = a.opencounterid) AS usuario
           FROM fincountermovement a
      JOIN finreceivableinvoice b ON b.invoiceid = a.invoiceid
   JOIN finincomesource e ON e.incomesourceid = b.incomesourceid
   LEFT JOIN finbankinvoiceinfo c ON c.invoiceid = a.invoiceid
   LEFT JOIN view_situacao_do_contrato_no_periodo d ON d.contrato = (( SELECT finentry.contractid
   FROM finentry
  WHERE finentry.invoiceid = b.invoiceid AND finentry.contractid IS NOT NULL
  ORDER BY finentry.entryid DESC
 LIMIT 1)) AND d.periodo::text = ((( SELECT bb.periodid
   FROM finentry aa
   JOIN acdlearningperiod bb ON aa.learningperiodid = bb.learningperiodid
  WHERE aa.invoiceid = b.invoiceid
  ORDER BY aa.entryid DESC
 LIMIT 1))::text);
