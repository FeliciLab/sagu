CREATE OR REPLACE FUNCTION totalCounterMovementNoTreasuryCounter(p_counterId finCounter.counterId%TYPE, p_beginDate date, p_endDate date, p_specieId finCounterMovement.speciesId%TYPE, p_operation finCounterMovement.operation%TYPE)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: totalCounterMovementNoTreasuryCounter
  PURPOSE: Calcula o saldo de movimentaçães conforme caixa, período e espécie.
  DESCRIPTION: vide "PURPOSE".
**************************************************************************************/
DECLARE
    v_total numeric;
    v_select text;
BEGIN
 v_select := '';

    IF p_counterId IS NOT NULL
    THEN
        v_select := v_select || 'B.counterId = ''' || p_counterId || ''' AND ';
    END IF;

    IF p_beginDate IS NOT NULL
    THEN
        v_select := v_select || 'A.movementdate::date >= ''' || p_beginDate || ''' AND ';
    END IF;

    IF p_endDate IS NOT NULL
    THEN
        v_select := v_select || 'A.movementdate::date <= ''' || p_endDate || ''' AND ';
    END IF;

    IF p_specieId IS NOT NULL
    THEN
        v_select := v_select || 'A.speciesid = ''' || p_specieId || ''' AND ';
    END IF;

    IF p_operation IS NOT NULL
    THEN
        v_select := v_select || 'A.operation = ''' || p_operation || ''' AND ';
    END IF;
    
  IF v_select <> ''
    THEN
        v_select := ' WHERE ' || substring( v_select from 0 for (char_length(v_select)-4) );
    END IF;

    v_select:= 'SELECT ROUND(SUM(A.value),2)
	        FROM fincountermovement A
          INNER JOIN finOpenCounter B
 	          ON (B.openCounterId = A.openCounterId)
          INNER JOIN finCounter C
                  ON (C.counterId = B.counterId) ' || v_select;

RAISE NOTICE '%', v_select;

	FOR v_total IN EXECUTE v_select
	LOOP
		RETURN v_total;
	END LOOP;
END
$BODY$
language 'plpgsql';
