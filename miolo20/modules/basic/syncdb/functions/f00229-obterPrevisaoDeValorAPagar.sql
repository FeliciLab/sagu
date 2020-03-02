CREATE OR REPLACE FUNCTION obterPrevisaoDeValorAPagar(p_contractid integer, p_learningperiodid integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: obterprevisaodevalorapagar
  PURPOSE: Obtém previsão do valor a pagar para o período letivo, se não tiver garga horária
  contratada função 

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       04/03/2013 ftomasini    1. FUNÇÃO criada.
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
BEGIN
    SELECT INTO v_data_periodo begindate FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId;

    SELECT INTO v_contrato * FROM acdcontract WHERE contractid = p_contractId;
 
    
    -- Obter o preço de curso vigente na data inicial do período letivo
    PRECO := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_data_periodo);

    IF PRECO.valueisfixed = 't'
    THEN
	-- Número de horas (disciplinas) que o aluno tá matriculado
        CH_CONT := getTotalHours(p_contractId, p_learningPeriodId);

        -- NÃºmero de horas das disciplinas oferecidas pra turma do aluno no perÃ­odo
        CH_PREV := getHoursAvailableForEnroll(p_contractId, p_learningPeriodId);

	IF CH_CONT IS NULL
	THEN
	    CH_CONT = CH_PREV;
        END IF;
        
        VPRG := PRECO.value * CH_CONT / CH_PREV;
         
    ELSE
        -- Obter o nÃºmero de crÃ©ditos total da matrÃ­cula do contrato no perÃ­odo letivo
        CREDITOS := obtemCreditoMatriculado(p_contractId, p_learningPeriodId);

	IF CREDITOS IS NULL
	THEN
	    CREDITOS := CASE getParameter('ACADEMIC', 'USAR_CREDITOS_ACADEMICOS') 
                                  WHEN 't' THEN SUM(e.academiccredits)
                                  WHEN 'f' THEN SUM(e.lessoncredits)
                               END
                        FROM acdlearningperiod a
                  INNER JOIN acdgroup b
                          ON A.learningperiodid = B.learningperiodid
                  INNER JOIN acdcontract C
                          ON (a.courseid = c.courseid
			      and a.courseversion = c.courseversion
                              and a.turnid = c.turnid
			      and a.unitid = c.unitid)
                  INNER JOIN acdcurriculum D
                          ON d.curriculumid = b.curriculumid		
                  INNER JOIN acdcurricularcomponent E
                          ON (E.curricularcomponentid = D.curricularcomponentid
                         AND E.curricularcomponentversion = D.curricularcomponentversion)
                       where A.learningperiodid = p_learningPeriodId  
                         and c.contractid = p_contractId;

	END IF;
	    
        -- No sistema de crÃ©ditos, multiplica-se o nÃºmero de crÃ©ditos pelo valor por crÃ©dito    
        VPRG := PRECO.value * CREDITOS;
    END IF;

    RETURN VPRG;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
