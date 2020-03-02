CREATE OR REPLACE FUNCTION obterValorAPagar(p_contractId integer, p_learningPeriodId integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: obterValorAPagar
  PURPOSE: Obtém o valor a pagar pelo período letivo

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       04/03/2013 Leovan Tavares    1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    -- Número de créditos
    CREDITOS integer;
    -- Preço do curso
    PRECO record;
    -- Valor programado do perí­odo
    VPRG numeric;

    CH_CONT numeric;

    CH_PREV numeric;

    -- Data inicial do período letivo
    v_data_periodo date;
    v_contrato acdcontract;
    v_price_end_date date;
BEGIN
    SELECT INTO v_data_periodo begindate FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId;

    SELECT INTO v_contrato * FROM acdcontract WHERE contractid = p_contractId;
 
    -- Faz log das disciplinas
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Disciplina:'|| b.groupid ||'-'||e.name || '   Horas acadêmicas: ' || E.academicnumberhours || ' h   Número de créditos: '|| e.academiccredits || ' cr', '4 - DISCIPLINAS DA MATRÍCULA')
      FROM unit_acdEnroll A
INNER JOIN unit_acdcurriculum C
        ON C.curriculumId = A.curriculumId
INNER JOIN acdCurricularComponent E
        ON (E.curricularComponentId = C.curricularComponentId AND
            E.curricularComponentVersion = C.curricularComponentVersion)
INNER JOIN acdGroup B
        ON A.groupId = B.groupId 
INNER JOIN unit_acdlearningperiod D
        ON D.learningPeriodId = B.learningPeriodId
     WHERE A.contractId = p_contractId
       AND D.periodId = (SELECT periodId
                           FROM unit_acdlearningperiod
                          WHERE learningPeriodId = p_learningPeriodId)
       AND A.statusId NOT IN (getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT, getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT, getParameter('ACADEMIC', 'ENROLL_STATUS_DESISTING')::INT ) 
       AND B.regimenId <> GETPARAMETER('ACADEMIC', 'REGIME_DE_FERIAS')::integer;
    
    -- Obter o preço de curso vigente na data inicial do período letivo
    PRECO := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_data_periodo);

    SELECT enddate INTO v_price_end_date
      FROM finprice 
     WHERE courseid = v_contrato.courseid 
       AND courseversion = v_contrato.courseversion
       AND turnid = v_contrato.turnid
       AND v_data_periodo BETWEEN startdate AND enddate;

    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Preço cadastrado: R$ ' || ROUND(PRECO.value::numeric,2), '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Data inicial do preço: ' || TO_CHAR(PRECO.startdate, 'dd/mm/yyyy'), '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Data final do preço: ' || TO_CHAR(v_price_end_date, 'dd/mm/yyyy'), '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Conta banária que receberá o pagamento: ' || (SELECT accountnumber ||'-'|| description FROM finbankaccount WHERE bankaccountid = PRECO.bankaccountid), '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Primeira parcela a vista para calouros: ' ||CASE WHEN PRECO.firstparcelatsightfreshman = 't' THEN 'Sim' ELSE 'Não' END, '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Primeira parcela a vista para veteranos: ' ||CASE WHEN PRECO.firstparcelatsight = 't' THEN 'Sim' ELSE 'Não' END, '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Preço créditos férias: R$ ' || ROUND(COALESCE(PRECO.valorcreditoferias, 0.00)::numeric), '1 - CONFIGURAÇÕES DE PREÇO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Preço é fixo (Caso valor = Sim (Carga horária matriculada / Carga horária prevista) * Preço) Caso valor = Não (Número de créditos matriculado * Preço): ' || CASE WHEN PRECO.valueisfixed = 't' THEN 'Sim' ELSE 'Não' END, '1 - CONFIGURAÇÕES DE PREÇO');

    IF PRECO.valueisfixed = 't'
    THEN
	    -- Número de horas (disciplinas) que o aluno tá matriculado
        CH_CONT := getTotalHours(p_contractId, p_learningPeriodId);

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Total carga horária que o aluno está matriculado: ' || CH_CONT || ' h','5 - CÁLCULOS EFETUADOS');
        -- Número de horas das disciplinas oferecidas pra turma do aluno no perÃ­odo
        CH_PREV := getHoursAvailableForEnroll(p_contractId, p_learningPeriodId);

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Total carga horária semestre: ' || CH_PREV ||' h', '5 - CÁLCULOS EFETUADOS');

        VPRG := PRECO.value * CH_CONT / CH_PREV;
         
    ELSE
        -- Obter o número de créditos total da matricula do contrato no periodo letivo
        CREDITOS := obtemCreditoMatriculado(p_contractId, p_learningPeriodId);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Total de créditos que o aluno está matriculado: ' || CREDITOS || ' cr',  '5 - CÁLCULOS EFETUADOS');
        -- No sistema de creditos, multiplica-se o numero de creditos pelo valor por credito    
        VPRG := PRECO.value * CREDITOS;
    END IF;

    RETURN VPRG;
END;
$BODY$
  LANGUAGE plpgsql;
