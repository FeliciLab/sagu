CREATE OR REPLACE FUNCTION isprofessoractive(p_personid bigint)
RETURNS BOOLEAN AS $BODY$
DECLARE
BEGIN
    RETURN EXISTS(
                SELECT 1
                  FROM acdScheduleProfessor SP
            INNER JOIN acdSchedule S
                    ON S.scheduleId = SP.scheduleId
            INNER JOIN acdGroup G
                    ON G.groupId = S.groupId
            INNER JOIN acdLearningPeriod LP
                    ON LP.learningPeriodId = G.learningPeriodId
                 WHERE SP.professorId = p_personid
                   AND NOW() BETWEEN beginDate AND endDate
    );
END;
$BODY$ language plpgsql;
