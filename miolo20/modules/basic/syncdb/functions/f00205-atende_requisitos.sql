create or replace function atende_requisitos(p_curriculo integer, p_contrato integer)
returns boolean as
$BODY$
declare
  v_disciplinas_requisitos record;
  v_creditos_requisitos integer;
  v_horas_requisitos integer;
  v_cursadas record;
begin
  for v_disciplinas_requisitos in select conditioncurriculumid from acdcondition 
      where conditioncurriculumid is not null and type = 'P' and curriculumid = p_curriculo
  loop
    if not exists (select enrollid
                     from acdenroll a
                    where a.curriculumid = v_disciplinas_requisitos.conditioncurriculumid
                      and contractid = p_contrato
                      and statusid in (getparameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT, 
                                       getparameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT, 
                                       getparameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)) then
      return false;
    end if;
  end loop;

  select into v_creditos_requisitos sum(credits) from acdcondition where curriculumid = p_curriculo;

  select into v_horas_requisitos sum(numberhour) from acdcondition where curriculumid = p_curriculo;

  if v_creditos_requisitos > 0 or v_horas_requisitos > 0 then
    select into v_cursadas sum(academiccredits) as creditos, sum(academicnumberhours) as horas
      from acdenroll a
     inner join acdcurriculum d on (d.curriculumid = a.curriculumid)
     inner join acdcurricularcomponent e on (e.curricularcomponentid = d.curricularcomponentid and e.curricularcomponentversion = d.curricularcomponentversion) 
     where statusid in (getparameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT, 
                        getparameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT, 
                        getparameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
       and a.contractid = p_contrato;

    if v_cursadas.creditos < v_creditos_requisitos or v_cursadas.horas < v_horas_requisitos then
      return false;
    end if;
  end if;

  return true;
end
$BODY$
language plpgsql;
