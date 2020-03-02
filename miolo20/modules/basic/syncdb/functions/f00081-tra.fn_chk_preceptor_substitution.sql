CREATE OR REPLACE FUNCTION tra.fn_chk_preceptor_substitution(p_preceptorsubstitutionid bigint, p_teamid integer, p_personid bigint, p_begindate date, p_enddate date)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: fn_chk_preceptor_substitution
  PURPOSE: Verifica:
  - Se as datas são vélidas (endDate deve ser maior ou igual a beginDate(ou nula)).
  DESCRIPTION: Verifica se tem as datas são vélidas (endDate deve ser maior ou igual a beginDate(ou nula)).

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       20/06/2011 Arthur Lehdermann 1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_valid boolean;
BEGIN
    -- Verifica a data de fim
    SELECT p_beginDate <= p_endDate INTO v_valid;

    IF ( v_valid IS TRUE )
    THEN
        -- Verifica se jé existe um preceptor substituto para a equipe nessa data
        SELECT tra.fn_chk_has_preceptor_substitution(p_preceptorSubstitutionId,
                                                     p_teamId,
                                                     p_personId,
                                                     p_beginDate,
                                                     p_endDate) IS FALSE INTO v_valid;
    END IF;

    IF ( v_valid IS TRUE )
    THEN
        -- Verifica seo preceptor substituto jé esté em alguma equipe nessa data
        SELECT tra.fn_chk_preceptor_substitution_has_team(p_preceptorSubstitutionId,
                                                          p_teamId,
                                                          p_personId,
                                                          p_beginDate,
                                                          p_endDate) IS FALSE INTO v_valid;
    END IF;

    RETURN v_valid;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
