CREATE OR REPLACE VIEW view_lancamentos_categorizados AS
SELECT A.invoiceid AS cod_titulo,
A.personid AS cod_pessoa,
B.name AS pessoa,
N.description AS nv_formacao,
A.parcelnumber AS no_parcela,
A.maturitydate AS dt_vencimento,
CASE WHEN ARRAY[c.operationid] && array_append(d.paymentoperations, j.paymentoperation)
THEN c.entrydate
ELSE NULL::date
END AS dt_pagamento,
c.value as valor,
CASE
WHEN ARRAY[c.operationid] && ARRAY[j.interestoperation]
THEN 'multa/Juros'
WHEN ARRAY[c.operationid] && array_cat(i.discountoperations, ARRAY[j.otherdiscountsoperation, j.discountoperation])
THEN 'Pontualidade'
WHEN ARRAY[c.operationid] && array_append(d.paymentoperations, j.paymentoperation)
THEN 'Liquido Recebido'
WHEN h.operationid = e.operationid
THEN 'Bolsas'
WHEN ARRAY[c.operationid] && ARRAY[j.banktaxoperation, j.bankclosingtaxoperation]
THEN 'Taxas'
WHEN ARRAY[c.operationid] && ARRAY[j.enrolloperation]
THEN 'Valor original'
ELSE 'Outros'
END AS tipo,
CASE
WHEN ARRAY[c.operationid] && ARRAY[j.interestoperation]
THEN 80
WHEN ARRAY[c.operationid] && array_cat(i.discountoperations, ARRAY[j.otherdiscountsoperation, j.discountoperation])
THEN 2
WHEN ARRAY[c.operationid] && array_append(d.paymentoperations, j.paymentoperation)
THEN 100
WHEN h.operationid = e.operationid
THEN 3
WHEN ARRAY[c.operationid] && ARRAY[j.banktaxoperation, j.bankclosingtaxoperation]
THEN 70
WHEN ARRAY[c.operationid] && ARRAY[j.enrolloperation]
THEN 1
ELSE 99
END AS ordem_tipo,
c.operationid AS cod_operacao,
l.unitid AS cod_unidade,
a.bankaccountid AS cod_conta_bancaria
FROM ONLY finreceivableinvoice a
JOIN ONLY basphysicalperson b ON a.personid = b.personid
JOIN ONLY finentry c ON a.invoiceid = c.invoiceid
FULL JOIN ( SELECT string_to_array((getparameter('FINANCE'::character varying, 'PAYMENT_OPERATIONS'::character varying)::text || ','::text) || getparameter('BASIC'::character varying, 'BANK_PAYMENT_OPERATION_ID'::character varying)::text, ','::text)::integer[] AS paymentoperations) d ON true
JOIN ONLY finoperation e ON c.operationid = e.operationid
JOIN ( SELECT a1.invoiceid, b1.contractid
FROM finreceivableinvoice a1
JOIN finentry b1 ON a1.invoiceid = b1.invoiceid AND b1.contractid IS NOT NULL
GROUP BY a1.invoiceid, b1.contractid) f ON a.invoiceid = f.invoiceid
LEFT JOIN ONLY finincentive g ON f.contractid = g.contractid
LEFT JOIN ONLY finincentivetype h ON g.incentivetypeid = h.incentivetypeid
FULL JOIN ( SELECT string_to_array(getparameter('FINANCE'::character varying, 'DISCOUNT'::character varying)::text, ','::text)::integer[] AS discountoperations) i ON true
FULL JOIN ( SELECT findefaultoperations.interestoperation, findefaultoperations.otherdiscountsoperation, findefaultoperations.discountoperation, findefaultoperations.banktaxoperation, findefaultoperations.paymentoperation, findefaultoperations.bankclosingtaxoperation, findefaultoperations.enrolloperation
FROM findefaultoperations) j ON true
FULL JOIN ( SELECT string_to_array(getparameter('FINANCE'::character varying, 'DEFAULT_TAX_BANK_OPERATION_ID'::character varying)::text, ','::text)::integer[] AS discountoperations) k ON true
JOIN acdcontract l ON f.contractid = l.contractid
JOIN acdcourse m ON l.courseid::text = m.courseid::text
JOIN acdformationlevel n ON m.formationlevelid = n.formationlevelid
WHERE a.iscanceled IS FALSE;
