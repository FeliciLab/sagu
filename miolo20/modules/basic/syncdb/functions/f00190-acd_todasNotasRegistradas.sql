/*************************************************************************************
  NAME: acd_todasNotasRegistradas
  PURPOSE: Verifica se todas as notas de uma disciplina foram digitadas.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       23/12/2013 Bruno E. Fuhr        1. Função criada.
**************************************************************************************/
CREATE OR REPLACE FUNCTION acd_todasnotasregistradas(p_groupid integer)
RETURNS boolean
AS $$
DECLARE

    v_todasregistradas boolean;
    v_notaobrigatoria integer;
    v_valornotaobrigatoria double precision;
    v_learningperiodid integer;

BEGIN

    v_todasregistradas := true;

    SELECT INTO v_learningperiodid learningperiodid FROM acdgroup WHERE groupid = p_groupid;
    FOR v_notaobrigatoria IN SELECT degreeid FROM acddegree WHERE learningperiodid = v_learningperiodId AND maybenull = false AND degreeid NOT IN (SELECT parentdegreeid FROM acddegree WHERE learningperiodid = v_learningperiodId AND parentdegreeid IS NOT NULL) LOOP
    
        SELECT INTO v_valornotaobrigatoria note FROM acddegreeenroll WHERE enrollid IN (SELECT enrollid FROM acdenroll WHERE groupid = p_groupid) AND degreeid = v_notaobrigatoria ORDER BY recorddate DESC LIMIT 1;
        IF ( v_valornotaobrigatoria IS NULL ) THEN
        BEGIN
            v_todasregistradas = false;
        END;
        END IF;
        
    END LOOP;
    
    RETURN v_todasregistradas;

END;
$$ LANGUAGE plpgsql;
