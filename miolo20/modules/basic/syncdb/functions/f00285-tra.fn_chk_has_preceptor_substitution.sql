CREATE OR REPLACE FUNCTION tra.fn_chk_has_preceptor_substitution(p_preceptorsubstitutionid bigint, p_teamid integer, p_personid bigint, p_begindate date, p_enddate date)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: fn_chk_has_preceptor_substitution
  PURPOSE: Verifica se a equipe já não tem um preceptor substituto para esse período de tempo.
  DESCRIPTION: Verifica se a equipe já não tem um preceptor substituto para esse período de tempo.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       20/06/2011 Arthur Lehdermann 1. Função criada.
**************************************************************************************/
DECLARE
    v_valid boolean;
BEGIN
    SELECT COUNT(*) > 0 INTO v_valid
      FROM tra.preceptorSubstitution
     WHERE (p_preceptorSubstitutionId IS NULL OR preceptorSubstitutionId != p_preceptorSubstitutionId)
       AND teamId = p_teamId
            -- Caso haja uma substituição sem data de fim, e:
            -- "p_endDate" seja nulo ou se
            -- "p_endDate" seja após a data de início.
       AND ((endDate IS NULL AND (p_endDate IS NULL OR
                                  p_endDate > beginDate)) OR
            -- Caso "p_endDate" seja nulo e "p_beginDate" for menor que a data de fim ou
            -- "p_endDate" esteja entre a data de início ou de de fim ou
            -- "p_beginDate" esteja entre a data de início ou de de fim.
            ((p_endDate IS NULL AND p_beginDate < endDate) OR
             (p_beginDate BETWEEN beginDate AND endDate) OR
             (p_endDate BETWEEN beginDate AND endDate) OR
             (p_beginDate < beginDate AND p_endDate > endDate)));

    RETURN v_valid;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION tra.fn_chk_has_preceptor_substitution(integer, integer, integer, date, date)
  OWNER TO postgres;
