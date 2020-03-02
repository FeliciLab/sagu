
CREATE OR REPLACE FUNCTION getTotalLearningPeriodDays(p_learningperiodid int)
  RETURNS int AS
$BODY$
/*************************************************************************************
  NAME: gettotallearningperioddays
  DESCRIPTION: Obtem o total de dias úteis de um periodo letivo.
**************************************************************************************/
DECLARE
    v_learningPeriod RECORD;
BEGIN
    SELECT INTO v_learningPeriod * FROM acdLearningPeriod WHERE learningPeriodId = p_learningperiodid;

    RETURN (SELECT COUNT(*)
              FROM getbusinessdates(v_learningPeriod.beginDate, v_learningPeriod.endDate) dt
             WHERE

                -- Desconta dias em que nao havera aula
                NOT EXISTS(
                    SELECT 1
                      FROM acdacademiccalendarevent
                     WHERE courseId = v_learningPeriod.courseId
                       AND courseversion = v_learningPeriod.courseVersion
                       AND turnId = v_learningPeriod.turnId
                       AND unitId = v_learningPeriod.unitId
                       AND haveClass IS FALSE
                       AND eventDate = dt
                )
    );
END
$BODY$
  LANGUAGE plpgsql;
