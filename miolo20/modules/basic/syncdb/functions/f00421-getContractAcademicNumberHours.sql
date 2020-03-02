CREATE OR REPLACE FUNCTION public.getcontractacademicnumberhours(p_contractid integer)
  RETURNS numeric
  LANGUAGE plpgsql
 AS $function$
 /*************************************************************************************
   NAME: getcontractacademicnumberhours
   PURPOSE: Obtém o total de horas que o aluno já cursou.
   AUTOR: ftomasini
 **************************************************************************************/
 DECLARE
     v_ch_contract NUMERIC;
     
     v_enroll_status_approved INT := getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT;
     v_enroll_status_excused INT := getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT;
     v_enroll_status_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT;
     v_enroll_status_pre_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT;
 
 BEGIN
     v_ch_contract:= 0;
 
     --carga horária já efetuada pelo aluno no contrato.
     SELECT INTO v_ch_contract COALESCE(sum(academicnumberhours),0)
            FROM acdcurricularcomponent A
      INNER JOIN acdcurriculum B
              ON (A.curricularcomponentid = B.curricularcomponentid
             AND A.curricularcomponentversion = B.curricularcomponentversion)
      INNER JOIN acdenroll C
              ON (B.curriculumid = C.curriculumid)
      INNER JOIN acdcontract D
              ON (D.contractid = C.contractid
             AND B.courseid =  D.courseid
             AND B.courseversion = D.courseversion
             AND B.turnid = D.turnid
             AND B.unitid = D.unitid)
           --Filtra somente pelas disciplinas pré-matriculadas, matriculadas, aprovadas e dispensadas.
           WHERE C.statusid IN (v_enroll_status_approved, v_enroll_status_excused, v_enroll_status_enrolled, v_enroll_status_pre_enrolled)
             AND D.contractid = p_contractid;
RETURN v_ch_contract;
END;
$function$