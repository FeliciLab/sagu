---------------------------------------------
-- Matrículas
---------------------------------------------
create or replace function bi_obter_matriculas(p_periodo varchar, p_curso varchar, p_unidade integer)
returns table (cod_unidade integer,
               cod_curso varchar,
               cod_turno integer,
               cod_aluno bigint,
               cod_professor bigint,
               sexo text,
               periodo varchar,
               idade double precision,
               cod_cidade integer,
               serie integer,
               contrato integer,
               cod_oferecida integer,
               cod_estado integer,
               data_inicial_periodo date,
               frequencia double precision,
               nota double precision,
               conceito text,
               etnia text,
               necessidade_especial text,
               data_matricula date) as
$$
declare
  v_consulta text;
  v_filtros text;
begin
  v_consulta := 'select B.unitid as cod_unidade,--unidade_id
                       B.courseid as cod_curso,--curso_id
                       B.turnid as cod_turno,--turno_id
                       B.personid::bigint as cod_aluno,--aluno_id
                       case when C.professorresponsible is not null then
                                C.professorresponsible::bigint
                            else
                                (select professorid 
                                   from acdscheduleprofessor AA
                                  inner join acdschedule BB using (scheduleid)
                                  where BB.groupid = C.groupid
                                  order by AA.scheduleprofessorid
                                  limit 1)
                       end as cod_professor, --professor_id
                       CASE WHEN upper(E.sex) = ''M'' THEN ''MASCULINO'' ELSE ''FEMININO'' END as sexo,--sexo_id
                       D.periodid as periodo,--periodo_id
                       extract(year from age(coalesce(E.datebirth, date(now())))) as idade,--faixa_etaria_id
                       E.cityid as cod_cidade,--geografia_id
                       CU.semester as serie,
                       A.contractid as contrato,--contrato_id
                       A.groupid as cod_oferecida,--disciplina_id
                       A.statusid as cod_estado,--situacao_matricula_id
                       D.begindate as data_inicial_periodo,--data_periodo_id
                       A.frequency as frequencia,--frequencia
                       A.finalnote as nota,--nota
                       A.concept as conceito,--conceito
                       G.description as etnia,
                       H.description as necessidade_especial,
                       MAX(A.dateenroll) as data_matricula --data_matricula_id
                from acdenroll A
                inner join acdcontract B using (contractid)
                inner join acdgroup C using (groupid)
                inner join acdcurriculum as CU on (C.curriculumid = CU.curriculumid)
                inner join acdlearningperiod D on (D.learningperiodid = C.learningperiodid)
                inner join basphysicalpersonstudent E using (personid)
                 left join basethnicorigin G using (ethnicoriginid)
                 left join basspecialnecessity H using (specialnecessityid)
                 left join acdsemestercontractperiod F on (F.contractid = B.contractid and F.periodid = D.periodid)';

  if (p_periodo is not null) then
    v_filtros := ' and D.periodid = ''' || p_periodo || '''';
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and B.courseid = ''' || p_curso || '''';
    else
      v_filtros := ' and B.courseid = ''' || p_curso || '''';
    end if;
  end if;

  if (p_curso is not null) then
    if (v_filtros is not null) then
      v_filtros := v_filtros || ' and B.unitid = ' || p_unidade;
    else
      v_filtros := ' and B.unitid = ' || p_unidade ;
    end if;
  end if;

  if (v_filtros is not null) then
    v_filtros := ' where ' || substr(v_filtros, 5);

    v_consulta := v_consulta || v_filtros;
  end if;

  v_consulta := v_consulta || ' group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19';

  return query execute v_consulta;
end;
$$ language plpgsql;
