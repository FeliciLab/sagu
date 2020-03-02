CREATE OR REPLACE FUNCTION obterHistoricoSituacaoAcademicaDosAlunos(p_periodId_inicial VARCHAR, p_periodId_final VARCHAR)
RETURNS SETOF SituacaoDoContratoNoPeriodo AS
$BODY$
DECLARE
    v_current_periodId VARCHAR := p_periodId_final;
    v_current_prev_periodId VARCHAR;
    v_encerra_loop BOOLEAN := FALSE;
    v_query TEXT;
BEGIN
    WHILE (v_encerra_loop IS FALSE)
    LOOP
        v_current_prev_periodId := obterPeriodoAcademicoAnteriorAoPeriodo(v_current_periodId);

        IF v_current_prev_periodId IS NULL
        THEN
            RAISE EXCEPTION 'Não foi encontrado um período anterior registrado para o período %. Acesse a interface de períodos e ajuste, para continuar com o processo.', v_current_periodId;
        END IF;	

	IF (v_current_periodId = p_periodId_final)
	THEN
	    v_query := 'SELECT * FROM obterSituacaoAcademicaDosContratosNoPeriodo(''' || v_current_periodId || ''', FALSE) ';
	ELSE
	    v_query := v_query || ' UNION ALL SELECT * FROM obterSituacaoAcademicaDosContratosNoPeriodo(''' || v_current_periodId || ''', FALSE) ';
	END IF;
    
	IF (v_current_periodId = p_periodId_inicial)
	THEN
	    v_encerra_loop := TRUE;
	END IF;

	v_current_periodId := v_current_prev_periodId;
    END LOOP;

    RAISE NOTICE '%', v_query;
    RETURN QUERY EXECUTE v_query;
END;
$BODY$
LANGUAGE plpgsql;
