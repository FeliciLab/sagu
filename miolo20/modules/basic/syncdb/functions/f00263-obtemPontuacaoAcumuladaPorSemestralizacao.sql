CREATE OR REPLACE FUNCTION obtemPontuacaoAcumuladaPorSemestralizacao(p_contractId int)
RETURNS FLOAT AS
$BODY$

DECLARE
    v_semestres INT;
    v_periodoInicial TEXT;
    v_horasAproveitamento FLOAT;
    v_periodos INT[];
    v_contador INT;
    v_pontuacaoAcumulada INT;
    v_pontuacaoSemestre INT;
    v_semestreInicial INT;

    v_ch_semester RECORD;
    v_semester_contract INT;
    v_ch_begin FLOAT;
    v_ch_semester_total FLOAT;
    
    v_gordura_de_horas NUMERIC := getparameter('ACADEMIC', 'DEFINE_GORDURA_DE_HORAS_PARA_SEMESTRE_POR_CARGA_HORARIA');

    v_enroll_status_approved INT := getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT;
    v_enroll_status_excused INT := getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT;
    v_enroll_status_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT;
    v_enroll_status_pre_enrolled INT := getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT;

BEGIN
    v_semester_contract:= 0;
    v_ch_begin:= 0;
    v_ch_semester_total:= 0;
    v_horasAproveitamento := 0;
    v_contador := 1;
    v_pontuacaoAcumulada := 0;
    v_semestreInicial := 1;

    -- Obtém o periodo inicial, para calcular a quantidade de horas aproveitadas neste período, se existirem
    SELECT INTO v_periodoInicial B.periodid
	   FROM acdMovementContract A
     INNER JOIN acdLearningPeriod B
             ON (A.learningPeriodId = B.learningPeriodId)
          WHERE A.stateContractId = GETPARAMETER('BASIC','STATE_CONTRACT_ID_ENROLLED')::INT
            AND A.contractId = p_contractId
       ORDER BY A.statetime ASC
          LIMIT 1 ; 

    RAISE NOTICE 'PERIODO INICIAL % ',v_periodoInicial; 
    
    -- Obtém a quantidade de horas aproveitadas do primeiro semestre do contrato
    SELECT INTO v_horasAproveitamento COALESCE(COUNT(*), 0)
           FROM acdExploitation A
     INNER JOIN acdEnroll B
             ON (A.enrollId = B.enrollId)
     INNER JOIN acdLearningperiod C
             ON (B.learningPeriodId = C.learningPeriodId)
	  WHERE C.periodId = v_periodoInicial
            AND B.contractId = p_contractId;

    RAISE NOTICE 'HORAS APROVEITADAS %', v_horasAproveitamento;

    IF ( v_horasAproveitamento != 0 )
    THEN
	    -- Verifica pelas horas aproveitadas qual o semestre inicial do aluno
	    -- Percorre os semestres do curso do aluno
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
		   WHERE D.contractid = p_contractId
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
		IF ( ( v_ch_begin < v_horasAproveitamento ) AND ( v_horasAproveitamento <= v_ch_semester_total ) )
		THEN
		    v_semester_contract:= v_ch_semester.semester;
		END IF;
		
		v_ch_begin:= v_ch_semester_total + 1;
	    END LOOP;
    END IF;

    -- É o semestre que o aluno entrou, considerando as horas de aproveitamentos
    RAISE NOTICE 'SEMESTRE DE ENTRADA, CONSIDERANDO AS HORAS APROVEITADAS %', v_semester_contract;
    
    -- Obtém array de códigos de períodos letivos encontrados nas movimentações de matrícula do contrato.
    SELECT INTO v_periodos 
                codigo_dos_periodos_letivos
           FROM obterSemestreDoContratoPelasMovimentacoesDeMatricula(p_contractId);

    -- Obtém a quantidade de semestres calculada pelas movimentações de matrícula do contrato.
    SELECT INTO v_semestres
                quantidade_de_semestres
           FROM obterSemestreDoContratoPelasMovimentacoesDeMatricula(p_contractId);

    RAISE NOTICE 'QUANTIDADE DE MOVIMENTACOES DE MATRICULA %', v_semestres;

    -- Se encontrou o semestre que o aluno deveria estar a partir dos aproveitamentos na primeira matrícula 
    -- adiciona a quantidade no semestre inicial
    IF ( v_semester_contract != 0 )
    THEN
        v_semestreInicial := v_semestreInicial + v_semester_contract;
    END IF;

    RAISE NOTICE 'SEMESTRES DE ENTRADA(1 + SEMESTRE EXTRAIDO DAS H.APROVEITADAS) %',v_semestreInicial;

    WHILE ( v_contador <= v_semestres )
    LOOP
        SELECT INTO v_pontuacaoSemestre obterPontuacaoAcumuladaPorSemestre(p_contractId, v_periodos[v_contador], v_semestreInicial);
        RAISE NOTICE 'PONTUACAO ACUMULADA % NO SEMESTRE % E NO PERIODO %',v_pontuacaoSemestre, v_semestreInicial, v_periodos[v_contador];
	
	v_pontuacaoAcumulada := v_pontuacaoAcumulada + v_pontuacaoSemestre;
	v_contador := v_contador + 1;
        v_semestreInicial := v_semestreInicial +1; 
    END LOOP;

    RAISE NOTICE 'PONTUACAO TOTAL ACUMULADA %',v_pontuacaoAcumulada;

    RETURN v_pontuacaoAcumulada;
END;
$BODY$
LANGUAGE 'plpgsql';
