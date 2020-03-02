---------------------------------------------
-- Estados contratuais dos matriculados
---------------------------------------------
create or replace function bi_obter_estados_contratuais_matriculados(p_periodo varchar, p_curso varchar, p_unidade integer)
returns table (contrato integer,
               periodo varchar,
               estado_contratual text) as
$$
declare
  v_consulta text;
  v_filtros text;
begin
  v_consulta := 'select distinct
                         C.contractid as contrato,
                         F.periodid as periodo,
                         case when exists ( select enrollid
                                               from acdenroll AA
                                              inner join acdgroup BB using (groupid)
                                              inner join acdlearningperiod CC on (CC.learningperiodid = BB.learningperiodid)
                                              where AA.contractid = C.contractid
                                                and CC.periodid <> F.periodid
                                                and CC.begindate < F.begindate
                                                and AA.statusid not in (5, 6, 7) ) then
                                   -- rematrÃ­cula: neste caso, Ã© reingresso ou rematrÃ­cula mesmo
                                   -- Ã© reingresso se encontrar uma mov de reingresso no perÃ­odo
                                   case when exists ( select 1 
                                                        from acdmovementcontract XA
                                                       inner join acdlearningperiod XB using (learningperiodid)
                                                       where XB.periodid = F.periodid
                                                         and XA.statecontractid = 6
                                                         and XA.contractid = C.contractid ) then
                                            ''REINGRESSO''
                                        else
                                            ''REMATRÍCULA''
                                   end
                              else
                                   -- para calouros (primeira matrí­cula) vale o estado de entrada
                                   -- obtem-se, então, a primeira movimentação contratual
                                   coalesce( 
                                   (select case when XA.statecontractid = 1 then ''VESTIBULAR''
                                               when XA.statecontractid = 2 then ''TRANSFERÊNCIA DE OUTRA INSTITUIÇÃO''
                                               when XA.statecontractid = 3 then ''PORTADOR DE DIPLOMA''
                                               when XA.statecontractid = 14 then ''INSCRIÇÃO''
                                               when XA.statecontractid = 19 then ''MATRÍCULA ISOLADA''
                                               when XA.statecontractid = 8 then ''TRANSFERÊNCIA DE OUTRO CURSO''
                                          else
                                              ''OUTRAS FORMAS DE INGRESSO''
                                          end as estado
                                     from acdmovementcontract XA
                                    inner join acdstatecontract XB using (statecontractid)
                                    where XA.contractid = C.contractid
                                      and XA.statecontractid not in (4, 16)
                                    order by XA.statetime limit 1)
                                   ,
                                   ''INDETERMINADO'')
                        end as estado_contratual --estado_contratual_id
                  from acdcontract C
                  inner join acdlearningperiod F 
                     on (F.courseid = C.courseid and 
                         F.courseversion = C.courseversion and
                         F.unitid = C.unitid and
                         F.turnid = C.turnid)
                  where exists (select contractid
                                  from acdenroll AA
                                 inner join acdgroup BB using (groupid)
                                 inner join acdlearningperiod CC on (CC.learningperiodid = BB.learningperiodid)
                                 where AA.contractid = C.contractid
                                   and CC.periodid = F.periodid
                                   and AA.statusid not in (5, 6, 7))';

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
