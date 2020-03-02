CREATE OR REPLACE FUNCTION verificaSeLancamentoTemVinculoComCheque(p_entryid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaSeLancamentoTemVinculoComCheque
  PURPOSE: Retorna TRUE quando um lançamento tem vínculo com cheque
 REVISIONS:
  Ver       Date        Author                  Description
  --------- ----------  -----------------       ------------------------------------
  1.0       11/06/2015  Nataniel I da Silva     1. FUNÇÂO criada.
**************************************************************************************/
DECLARE
    v_result BOOLEAN := FALSE;
    v_entry RECORD;
BEGIN
    SELECT INTO v_entry * FROM finEntry WHERE entryId = p_entryid;

    IF v_entry.countermovementid IS NOT NULL 
    THEN
        IF (SELECT COUNT(*) > 0 FROM fincountermovementcheque WHERE countermovementid = v_entry.countermovementid)
        THEN
            v_result := TRUE;
        END IF;
    END IF;

    RETURN v_result; 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;