CREATE OR REPLACE FUNCTION get_semester_contract(p_contractid int) 
RETURNS INTEGER AS $$
/*************************************************************************************
  NAME: get_v_semester_contract
  PURPOSE: Obtém o semestre em que o aluno está.
  AUTOR: ftomasini
**************************************************************************************/
DECLARE
    v_ch_semester RECORD;
    v_ch_contract FLOAT;
    v_semester_contract INT;
    v_ch_begin FLOAT;
    v_ch_semester_total FLOAT;
    v_total_semesters INT;
    
    v_enroll_status_approved INT := getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT;
    v_enroll_status_excused INT := getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT;
    v_enroll_status_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT;
    v_enroll_status_pre_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT;

    v_gordura_de_horas NUMERIC := getparameter('ACADEMIC', 'DEFINE_GORDURA_DE_HORAS_PARA_SEMESTRE_POR_CARGA_HORARIA');

    v_atividade_complementar NUMERIC := getparameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY');
BEGIN
    v_semester_contract:= 0;
    v_ch_begin:= 0;
    v_ch_semester_total:= 0;
    v_total_semesters:= 0;

    --Obtém o total de semestres do curso do aluno
    SELECT INTO v_total_semesters MAX(a.semester)
      FROM acdcurriculum a
INNER JOIN acdcontract b
        ON (a.courseid = b.courseid
            AND a.courseversion = b.courseversion
            AND a.turnid = b.turnid
            AND a.unitid = b.unitid)
      WHERE contractid = p_contractid;

    --carga horária já efetuada pelo aluno no contrato.
    SELECT INTO v_ch_contract COALESCE(SUM(CASE WHEN B.curriculumtypeid = v_atividade_complementar 
                                                THEN
                                                    (SELECT COALESCE(SUM(totalhours), 0)
                                                       FROM acdcomplementaryactivities
                                                      WHERE C.enrollId = enrollId)
                                                ELSE 
                                                    academicnumberhours
                                           END),0)
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
            AND D.contractid = p_contractid
            AND B.semester <> 0;


    --Percorre os semestres do curso do aluno
    FOR v_ch_semester IN 
  ( SELECT B.semester, 
                 COALESCE(SUM(academicnumberhours),0) AS academicnumberhours
      FROM acdcurricularcomponent A
      INNER JOIN acdcurriculum B
        ON (A.curricularcomponentid = B.curricularcomponentid
       AND A.curricularcomponentversion = B.curricularcomponentversion)
      INNER JOIN acdcontract D
        ON (B.courseid =  D.courseid
       AND B.courseversion = D.courseversion
       AND B.turnid = D.turnid
       AND B.unitid = D.unitid)
     WHERE D.contractid = p_contractid
       -- desconsidera disciplinas que não são da matriz do curso (opcoes de eletiva)
       AND semester != 0
        GROUP BY B.semester 
        ORDER BY B.semester )
    LOOP
        v_ch_semester_total := v_ch_semester_total + v_ch_semester.academicnumberhours;        

  --Gordura de horas para atingir o semestre.
        IF v_gordura_de_horas IS NOT NULL
        THEN
            v_ch_semester_total := v_ch_semester_total + v_gordura_de_horas;
  END IF;
    
        --Verifica se a ch do contrato se encaixa na faixa do semestre
        IF ( (( v_ch_begin <= v_ch_contract ) AND ( v_ch_contract <= v_ch_semester_total )) OR ((v_total_semesters = v_ch_semester.semester) AND (v_ch_contract > v_ch_semester_total)) )
        THEN
            v_semester_contract:= v_ch_semester.semester;
        END IF;
        
        v_ch_begin:= v_ch_semester_total + 1;
    END LOOP;

    RETURN v_semester_contract;
END;
$$
LANGUAGE plpgsql;
