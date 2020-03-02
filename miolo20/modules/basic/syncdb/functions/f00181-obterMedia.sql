CREATE OR REPLACE FUNCTION obterMedia(p_degree integer, p_group integer)
RETURNS double precision AS $$
DECLARE

    v_note double precision;
    v_total double precision;
    v_numenrolls integer;
    v_media double precision;
    v_enrollid integer;

BEGIN

    v_total := 0;
    v_numenrolls := 0;
    v_media := 0;

    FOR v_enrollid IN SELECT DISTINCT(enrollid) FROM acddegreeenroll WHERE degreeid = p_degree AND enrollid IN (SELECT enrollid FROM acdenroll WHERE groupid = p_group) LOOP
    BEGIN
        v_numenrolls := v_numenrolls + 1;

        SELECT INTO v_note note FROM acddegreeenroll WHERE enrollid = v_enrollid ORDER BY recorddate DESC LIMIT 1;

        v_total := v_total + v_note;
    END;
    END LOOP;

    IF ( v_numenrolls > 0 ) THEN
        RETURN v_total / v_numenrolls;
    ELSE
        RETURN 0;
    END IF;

END;
$$ LANGUAGE plpgsql;
--
