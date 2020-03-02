CREATE OR REPLACE FUNCTION rpt_receita_diaria(character varying, character varying)
  RETURNS SETOF record AS
$BODY$        
SELECT DISTINCT 
  a.invoiceid as titulo,
  d.ournumber as nosso_numero,
  b.personid as matricula,
  getpersonname(b.personid) as nome,
  doc.content as cpf,
  (select description from finincomesource where incomesourceid = b.incomesourceid) as origem,
  getcoursename(f.courseid) as curso,
  to_char(a.entrydate, 'dd/mm/yyyy') as data_pagamento,
  round(b.nominalvalue, 2) as valor_nominal,
  (select round(sum(value), 2)
     from finentry xa
    where xa.invoiceid = a.invoiceid
      and xa.operationid in (select operationid from finincentivetype)
      and xa.operationid not in (select discountoperation from findefaultoperations)
      and xa.operationid not in (select convenantoperation from finconvenant)) as incentivos,
  (select round(sum(value), 2)
     from finentry xa
    inner join finoperation xb using (operationid)
    where xa.invoiceid = a.invoiceid
      and (xb.operationid in (select discountoperation from findefaultoperations) or
           xb.operationid in (select convenantoperation from finconvenant))) as desconto_e_convenios,
  (select round(sum(value), 2)
     from finentry xa
    inner join finoperation xb using (operationid)
    where xa.invoiceid = a.invoiceid
      and xb.operationtypeid = 'C'
      and xa.operationid not in (select operationid from finincentivetype)
      and xa.operationid not in (select operationid from finconvenant)
      and xa.operationid not in (select discountoperation from findefaultoperations)
      and xa.operationid not in (select paymentoperation from findefaultoperations)
      and xa.operationid not in (select cancelcurricularcomponentoperation from findefaultoperations)) as outros_descontos,
  round(a.value, 2) as valor_pago,
  round(sum(case when c.operation = 'D' then (-1) * c.value else c.value end), 2) as caixa,
  g.description as forma_pgto,
  round(sum(d.value)::numeric, 2) as banco,
  d.branch || '/' || d.branchnumber as conta
FROM finentry a
INNER JOIN ONLY fininvoice b on (a.invoiceid = b.invoiceid)
LEFT JOIN fincountermovement c using (countermovementid)
LEFT JOIN fin.bankmovement d using (bankmovementid)
LEFT JOIN (select distinct contractid, invoiceid from finentry where contractid is not null) e on (e.invoiceid = b.invoiceid)
LEFT JOIN acdcontract f on (f.contractid = e.contractid)
LEFT JOIN finspecies g using (speciesid)
left join basdocument doc on doc.personid = b.personid and doc.documenttypeid = getparameter('BASIC'::varchar, 'DEFAULT_DOCUMENT_TYPE_ID_CPF'::varchar)::integer
WHERE a.entrydate BETWEEN TO_DATE($1, 'dd/mm/yyyy') AND TO_DATE($2, 'dd/mm/yyyy')
  AND a.operationid in (select paymentoperation from findefaultoperations)
  AND (d.bankmovementstatusid is null or d.bankmovementstatusid in (1,2,3))
GROUP BY a.invoiceid, d.ournumber, b.personid, doc.content, f.courseid, a.entrydate, b.nominalvalue, a.value, c.operation, d.value, b.incomesourceid, d.branch, d.branchnumber, g.description
ORDER BY nome, titulo;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION rpt_receita_diaria(character varying, character varying)
  OWNER TO postgres;
