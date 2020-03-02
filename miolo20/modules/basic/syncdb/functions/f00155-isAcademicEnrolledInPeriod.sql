--
CREATE OR REPLACE FUNCTION isAcademicEnrolledInPeriod(p_contractid INTEGER, p_periodid CHARACTER VARYING)
RETURNS BOOLEAN AS
$BODY$
DECLARE
    v_enroll_status_cancelled INTEGER := getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INT;
    v_enroll_status_excused INTEGER := getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT;
    v_enroll_status_pre_matriculado INTEGER := getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT;

    v_count INTEGER;
BEGIN    
    SELECT INTO v_count 
                MAX(AA.enrollId)
           FROM acdEnroll AA
     INNER JOIN acdGroup BB
             ON BB.groupId = AA.groupId
     INNER JOIN acdContract CC
             ON CC.contractId = AA.contractId
     INNER JOIN acdLearningPeriod DD
             ON (BB.learningPeriodId = DD.learningPeriodId)
          WHERE DD.periodId = p_periodid
            AND AA.statusId NOT IN (v_enroll_status_cancelled, v_enroll_status_excused, v_enroll_status_pre_matriculado)
            AND AA.contractId = p_contractid;
    
    IF v_count IS NOT NULL
    THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
