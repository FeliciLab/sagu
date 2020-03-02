-----------------------------------------------------
-- Estados contratuais dos não matriculados (evasões)
----------------------------------------------------- 
create or replace function bi_obter_evasoes(p_periodo varchar, p_curso varchar, p_unidade integer)
returns table (cod_unidade integer,
               cod_curso varchar,
               cod_turno integer,
               cod_aluno bigint,
               periodo varchar,
               data_inicial_periodo date,
               data_estado_contratual date,
               sexo text,
               idade double precision,
               cod_cidade integer,
               estado_contratual text,
               contrato integer,
               etnia text,
               necessidade_especial text) as
$$
declare
  v_consulta text;
  v_filtros text;
begin
  v_consulta := 'select * from (
                 select distinct 
                         C.unitid as cod_unidade,
                         C.courseid as cod_curso,
                         C.turnid as cod_turno,
                         C.personid::bigint as cod_aluno,
                         COALESCE(D.periodid, (select periodid
                                                 from acdlearningperiod
                                                where courseid = C.courseid
                                                  and courseversion = C.courseversion
                                                  and unitid = C.unitid
                                                  and turnid = C.turnid
                                                  and A.statetime between begindate and enddate)) as periodo,
                         COALESCE(D.begindate, A.statetime::date) as data_inicial_periodo,
                         A.statetime::date as data_estado_contratual,
                         CASE WHEN upper(E.sex) = ''M'' THEN ''MASCULINO'' ELSE ''FEMININO'' END as sexo,
                         extract(year from age(coalesce(E.datebirth, date(now())))) as idade,
                         E.cityid as cod_cidade,
                         case when A.statecontractid = 5 then ''TRANCAMENTO''
                              when A.statecontractid = 7 then ''MUDANÇA DE CURSO''
                              when A.statecontractid in (9, 10, 11) then ''CONCLUSÃO''
                              when A.statecontractid in (12, 18) then ''TRANSFERÊNCIA PARA OUTRA INSTITUIÇÃO''
                              when A.statecontractid in (13, 17) then ''CANCELAMENTO''
                              else ''OUTRAS SAÍDAS''
                         end as estado_contratual,
                         C.contractid as contrato,
                         G.description as etnia,
                         F.description as necessidade_especial
                  from acdmovementcontract A
                   inner join acdstatecontract B using (statecontractid)
                   inner join acdcontract C using (contractid)
                   inner join basphysicalpersonstudent E using (personid)
                    left join acdlearningperiod D using (learningperiodid)
                    left join basspecialnecessity F using (specialnecessityid)
                    left join basethnicorigin G using (ethnicoriginid)
                   where B.isclosecontract is true
                     and not exists ( select enrollid
                                        from acdenroll AA
                                       inner join acdgroup BB using (groupid)
                                       inner join acdlearningperiod CC on (CC.learningperiodid = BB.learningperiodid)
                                       where AA.contractid = A.contractid
                                         and ( CC.periodid = D.periodid or 
                                               (D.periodid is null and
                                                A.statetime::date between CC.begindate and CC.enddate ) )
                                         and AA.statusid not in (5, 6, 7) ) ) as sel';

  if (p_periodo is not null) then
    v_filtros := ' and sel.periodo = ''' || p_periodo || '''';
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and sel.cod_curso = ''' || p_curso || '''';
    else
      v_filtros := ' and sel.cod_curso = ''' || p_curso || '''';
    end if;
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and sel.cod_unidade = ' || p_unidade;
    else
      v_filtros := ' and sel.cod_unidade = ' || p_unidade;
    end if;
  end if;

  if (v_filtros is not null) then
    v_filtros := ' where ' || substr(v_filtros, 5);

    v_consulta := v_consulta || v_filtros;
  end if;

  return query execute v_consulta;
end;
$$ language plpgsql;
