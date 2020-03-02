----------------------------------------------------------------                       
-- Financeiro
----------------------------------------------------------------
create or replace function bi_obter_titulos(p_periodo varchar, p_curso varchar, p_unidade integer)
returns table (titulo integer,
               cod_unidade integer,
               cod_curso varchar,
               cod_turno integer,
               cod_aluno integer,
               sexo text,
               data_inicial_periodo date,
               data_vencimento date,
               contrato integer,
               serie integer,
               periodo varchar,
               cod_cidade integer,               
               idade double precision,
               valor_nominal numeric,
               valor_incentivos numeric,
               valor_descontos numeric,
               valor_pago numeric,
               valor_juros_multas numeric,
               valor_taxa numeric,
               valor_inadimplencia numeric,
               parcela integer) as
$$
declare
  v_consulta text;
  v_filtros text;
begin
  v_consulta := 'select
                      A.titulo as cod_titulo,-- titulo_id 
                      C.unitid as cod_unidade,-- unidade_id
                      C.courseid as cod_curso,-- curso_id
                      C.turnid as cod_turno,-- turno_id
                      A.matricula as cod_aluno,-- aluno_id
                      CASE WHEN upper(B.sex) = ''M'' THEN ''MASCULINO'' ELSE ''FEMININO'' END as sexo,-- sexo_id 
                      F.begindate as data_inicial_periodo, -- data_periodo_id 
                      A.vencimento as data_vencimento,-- data_vencimento_id
                      A.contrato as contrato,-- contrato_id 
                      G.semester as serie,-- serie_id
                      A.periodo as periodo,-- periodo_id 
                      B.cityid as cod_cidade,-- geografia_id 
                      extract(year from age(coalesce(B.datebirth, date(now())))) as idade,-- faixa_etaria_id 
                      A.valor_nominal,-- valor_nominal 
                      A.valor_incentivos,-- valor_incentivos 
                      A.valor_descontos + getinvoicediscountvalue(A.titulo, now()::date) + getinvoiceconvenantvalue(A.titulo, now()::date) as valor_descontos,-- valor_descontos 
                      A.valor_pago as valor_recebido,-- valor_recebido
                      A.valor_juros_multas,
                      A.valor_taxa, 
                      CASE WHEN A.inadimplencia > 0 THEN A.inadimplencia
                           ELSE CASE WHEN A.vencimento < now()::date and D.balance > 0 THEN D.balance
                           ELSE 0::numeric END
                      END as valor_inadimplencia,-- valor_inadimplencia 
                      D.parcelnumber as parcela-- parcela
                from fininfotitulo A
                inner join basphysicalpersonstudent B on (B.personid = A.matricula)    
                inner join acdcontract C on (C.contractid = A.contrato)
                inner join only finreceivableinvoice D on (D.invoiceid = A.titulo)
                inner join acdlearningperiod F on (F.learningperiodid = A.periodo_letivo)
                left join acdsemestercontractperiod G on (G.contractid = C.contractid and G.periodid = F.periodid)
                where D.iscanceled IS FALSE
                and EXISTS (select entryid from finentry where invoiceid = A.titulo)';

  if (p_periodo is not null) then
    v_filtros := ' and F.periodid = ''' || p_periodo || '''';
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and C.courseid = ''' || p_curso || '''';
    else
      v_filtros := ' and C.courseid = ''' || p_curso || '''';
    end if;
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and C.unitid = ' || p_unidade;
    else
      v_filtros := ' and C.unitid = ' || p_unidade;
    end if;
  end if;

  if (v_filtros is not null) then
    v_consulta := v_consulta || v_filtros;
  end if;

  return query execute v_consulta;
end;
$$ language plpgsql;
