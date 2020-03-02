CREATE OR REPLACE FUNCTION acd_obterTurmaDoAlunoNoPeriodo(p_contractId INT, p_periodId VARCHAR)
RETURNS SETOF acdClass AS
$BODY$
DECLARE
    v_acdClass acdClass;
BEGIN
    RETURN QUERY (
        SELECT C.*
	  FROM acdClass C
    INNER JOIN acdClassPupil CP
	    ON CP.classId = C.classId
    INNER JOIN acdContract CO
	    ON CO.contractId = CP.contractId
     LEFT JOIN acdLearningPeriod  LP
	    ON (LP.courseId,
		LP.courseVersion,
		LP.turnId,
		LP.unitId) = (CO.courseId,
			      CO.courseVersion,
			      CO.turnId,
			      CO.unitId)
	   AND LP.periodId = p_periodId
	 WHERE CP.contractId = p_contractId
	   AND ((LP.beginDate, LP.endDate) OVERLAPS (CP.beginDate, CP.endDate)
	    OR (CP.endDate IS NULL 
	   AND LP.endDate >= CP.beginDate))
      ORDER BY CP.beginDate DESC
         LIMIT 1
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;