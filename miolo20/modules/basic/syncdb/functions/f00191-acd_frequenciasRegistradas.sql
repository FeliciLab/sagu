/*************************************************************************************
  NAME: acd_frequenciasRegistradas
  PURPOSE: Verifica se todas as frequências de uma disciplina foram digitadas.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       23/12/2013 Bruno E. Fuhr        1. Função criada.
**************************************************************************************/
CREATE OR REPLACE FUNCTION acd_frequenciasregistradas(p_groupid integer)
RETURNS boolean
AS $$
DECLARE

    v_todasregistradas boolean;
    v_frequencia double precision;

BEGIN

    v_todasregistradas := true;

    FOR v_frequencia IN SELECT frequency FROM acdenroll WHERE groupid = p_groupid LOOP
        IF ( v_frequencia IS NULL ) THEN
            v_todasregistradas := false;
        END IF;
    END LOOP;

    RETURN v_todasregistradas;

END;
$$ LANGUAGE plpgsql;
