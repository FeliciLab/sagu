CREATE OR REPLACE FUNCTION get_semester_contract_period(p_contractid int, p_periodid varchar) 
RETURNS INTEGER AS $$
/*************************************************************************************
  NAME: get_v_semester_contract
  PURPOSE: Obtém o semestre em que o aluno estáva no período informado, caso não tenha um período
           letivo para o curso do aluno no período informado a função irá retornar 0
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
      LEFT JOIN acdgroup E
             ON E.groupid = C.groupid
      LEFT JOIN acdlearningperiod F
             ON F.learningperiodid = E.learningperiodid       
          --Filtra somente pelas disciplinas pré-matriculadas, matriculadas, aprovadas e dispensadas.
          WHERE C.statusid IN (v_enroll_status_approved, v_enroll_status_excused, v_enroll_status_enrolled, v_enroll_status_pre_enrolled)
            AND D.contractid = p_contractid
            AND ((C.groupid IS NULL) OR (F.begindate <= (SELECT x.begindate FROM acdlearningperiod x WHERE x.courseid = f.courseid AND x.courseversion = f.courseversion AND x.turnid = F.turnid AND x.unitid = F.unitid AND x.periodid = p_periodid)));

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

        --RAISE NOTICE 'v_ch_begin % < v_ch_contract % AND v_ch_contract % <= v_ch_semester_total %', v_ch_begin, v_ch_contract, v_ch_contract,v_ch_semester_total;
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
