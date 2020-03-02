CREATE OR REPLACE VIEW cr_acd_transferencia_externa_de_curso AS (
select pe.personid as pessoa_codigo,
       pe.name as pessoa_nome,
       doc.content as pessoa_cpf,
       a.statetime as transderencia_data,
       b.periodid as transferencia_periodo,
       s.description as transferencia_tipo,  
       d.contractid as contrato_codigo, 
       d.courseid as contrato_curso_codigo,
       x1.name as contrato_curso_nome, 
       d.courseversion as contrato_curso_versao, 
       d.turnid as contrato_turno_codigo,
       x2.description as contrato_turno_descricao, 
       d.unitid as contrato_unidade_codigo,
       x3.description as contrato_unidade_descricao
from acdmovementcontract a
inner join acdlearningperiod b
on a.learningperiodid = b.learningperiodid
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
inner join acdstatecontract s
on s.statecontractid = a.statecontractid
where (a.statecontractid = getparameter('ACADEMIC', 'STATE_CONTRACT_ID_EXTERNAL_TRANSFER_TO')::integer
or a.statecontractid = getparameter('ACADEMIC', 'STATE_CONTRACT_ID_EXTERNAL_TRANSFER_FROM')::integer));





