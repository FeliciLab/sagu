CREATE OR REPLACE VIEW cr_acd_transferencia_interna_de_curso AS (
select pe.personid as pessoa_codigo,
       pe.name as pessoa_nome,
       doc.content as pessoa_cpf,
       a.statetime as transderencia_data,
       b.periodid as transferencia_periodo,  
       d.contractid as contrato_origem_codigo, 
       d.courseid as contrato_origem_curso_codigo,
       x1.name as contrato_origem_curso_nome, 
       d.courseversion as contrato_origem_curso_versao, 
       d.turnid as contrato_origem_turno_codigo,
       x2.description as contrato_origem_turno_descricao, 
       d.unitid as contrato_origem_unidade_codigo,
       x3.description as contrato_origem_unidade_descricao, 
       e.contractid as contrato_destino_codigo, 
       e.courseid as contrato_destino_curso_codigo,
       y1.name as contrato_destino_curso_nome, 
       e.courseversion as contrato_destino_curso_versao, 
       e.turnid as contrato_destino_turno_codigo,
       y2.description as contrato_destino_turno_descricao, 
       e.unitid as contrato_destino_unidade_codigo,
       y3.description as contrato_destino_unidade_descricao
from acdmovementcontract a
inner join acdlearningperiod b
on a.learningperiodid = b.learningperiodid
-- contrato origem
inner join acdcontract d
on d.contractid = a.contractid
inner join acdcourse x1
on x1.courseid = d.courseid
inner join basturn x2
on x2.turnid = d.turnid
inner join basunit x3
on x3.unitid = d.unitid
inner join ONLY basphysicalperson pe
on pe.personid = d.personid
inner join basdocument doc
on (doc.personid = pe.personid
and doc.documenttypeid = 2)
--contrato destino
inner join acdcontract e
on ( e.contractid = (select xx.contractid 
                          from acdmovementcontract xx
                          inner join acdcontract xx1
                          on (xx1.personid = pe.personid
                               and xx1.contractid = xx.contractid)
                          inner join acdlearningperiod xx2
                          on xx2.learningperiodid = xx.learningperiodid
                          where xx2.periodid = b.periodid
                          and  xx.statecontractid = getparameter('ACADEMIC', 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_FROM')::integer
                          and  xx.statetime::date = a.statetime::date order by xx.statetime desc limit 1))
inner join acdcourse y1
on y1.courseid = e.courseid
inner join basturn y2
on y2.turnid = e.turnid
inner join basunit y3
on y3.unitid = e.unitid
where statecontractid = getparameter('ACADEMIC', 'STATE_CONTRACT_ID_INTERNAL_TRANSFER_TO')::integer);
