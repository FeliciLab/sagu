CREATE OR REPLACE FUNCTION getDegreeEnrollCurrentGrade(p_degreeid integer, p_enrollid integer, p_ishistory boolean)
RETURNS SETOF typedegreeenrollcurrentgrade
AS $$
DECLARE
v_row RECORD;
v_sql TEXT;
v_orderby TEXT;
v_limitsql TEXT;
BEGIN
v_limitsql := '';

IF p_ishistory IS TRUE
THEN
v_orderby = ' ORDER BY DE.recorddate DESC ';
ELSE
-- Se parametro estiver ativado, ordena pela nota mais alta, senao, pela ultima
IF GETPARAMETER('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') = 't'
THEN
    v_orderby := ' AND recorddate > (SELECT CASE WHEN (
                                         SELECT recorddate FROM acddegreeenroll 
                                         WHERE enrollid = DE.enrollid AND degreeid = DE.degreeid AND note IS NULL 
                                         ORDER BY recorddate DESC LIMIT 1) IS NULL THEN ''2000-01-01''::timestamp ELSE
                                         (SELECT recorddate FROM acddegreeenroll 
                                         WHERE enrollid = DE.enrollid AND degreeid = DE.degreeid AND note IS NULL 
                                         ORDER BY recorddate DESC LIMIT 1)
                                         END) ORDER BY DE.note DESC ';
ELSE
    v_orderby := ' ORDER BY DE.recordDate DESC ';
END IF;

v_limitsql := ' LIMIT 1 ';
END IF;

v_sql := 'SELECT (CASE WHEN (SELECT G.useConcept
                    FROM acdEnroll E
            INNER JOIN acdgroup G
                    ON E.groupId = G.groupId
                    WHERE E.enrollId = DE.enrollId) IS TRUE
            THEN
                DE.concept
            ELSE
                ROUND(DE.note::numeric, GETPARAMETER(''BASIC'', ''GRADE_ROUND_VALUE'')::int)::varchar
            END) AS nota,

            DE.description,                    
            timestamptouser(DE.recordDate) AS recorddate,
            DE.username
    FROM acdDegreeEnroll DE
    WHERE DE.degreeId = ' || p_degreeId || '
        AND DE.enrollId = ' || p_enrollId || v_orderby || v_limitsql;

-- Prevencao de erros
IF p_degreeId IS NULL OR p_enrollId IS NULL
THEN
RETURN;
END IF;

RETURN QUERY EXECUTE v_sql;
END; 
$$ LANGUAGE plpgsql;
--
