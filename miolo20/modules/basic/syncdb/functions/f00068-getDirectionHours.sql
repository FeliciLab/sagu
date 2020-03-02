CREATE OR REPLACE FUNCTION getDirectionHours(p_professorid bigint, p_begindate date, p_enddate date, p_courseid character varying, p_courseversion integer, p_turnid integer, p_unitid integer) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: getDirectionHours
  PURPOSE: Obtém o total de horas de orientação do professor no peréodo especificado.
  A ocorréncia de curso pode ser nula.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       24/06/2011 Alexandre Schmidt 1. FUNÇÃO criada.
  1.1       10/08/2011 Alex Smith        1. Ajuste para permitir orientação de mais de
                                         uma disciplina.
**************************************************************************************/
DECLARE
    v_retVal float;
BEGIN
    SELECT SUM(A.directionWorkLoad) INTO v_retVal
      FROM acdFinalExaminationDirectors A
INNER JOIN acdEnroll B
        ON B.enrollId = A.enrollId
INNER JOIN acdGroup C
        ON C.groupId = B.groupId
INNER JOIN acdCurriculum D
        ON D.curriculumId = C.curriculumId
INNER JOIN acdLearningPeriod E
        ON E.learningPeriodId = C.learningPeriodId
     WHERE A.personId = p_professorId
       AND E.beginDate <= p_endDate
       AND E.endDate >= p_beginDate
       AND D.courseId = COALESCE(p_courseId, D.courseId)
       AND D.courseVersion = COALESCE(p_courseVersion, D.courseVersion)
       AND D.turnId = COALESCE(p_turnId, D.turnId)
       AND D.unitId = COALESCE(p_unitId, D.unitId);

    RETURN COALESCE(v_retVal, 0);
END;
$$;
