CREATE OR REPLACE FUNCTION getTotalTeachedHours(p_groupId acdGroup.groupId%TYPE)
RETURNS FLOAT AS
$BODY$
/*************************************************************************************
  NAME: getTotalTeachedHours
  PURPOSE: Obtém a quantidade de horas já ministradas da oferecida.
  DESCRIPTION:
  Pesquisa a tabela acdScheduleProfessorContent, somando os timeIds de todas as aulas
  jÃ© ministradas. Utilizado para determinar o percentual de frequência do aluno frente
  ao total de aulas ministradas.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       10/06/2011 Alex Smith        1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_totalHours float;
BEGIN
    SELECT COALESCE(SUM((EXTRACT(HOUR   FROM (B.endHour - B.beginHour)) +
                         EXTRACT(MINUTE FROM (B.endHour - B.beginHour)) / 60 +
                         EXTRACT(SECOND FROM (B.endHour - B.beginHour)) / 60 / 60)::float), 0) INTO v_totalHours
      FROM acdScheduleProfessorContent A
INNER JOIN acdTime B
        ON B.timeId = A.timeId
INNER JOIN acdScheduleProfessor C
        ON C.scheduleProfessorId = A.scheduleProfessorId
INNER JOIN acdSchedule D
        ON D.scheduleId = C.scheduleId
     WHERE A.classOccurred
       AND D.groupId = p_groupId;

    RETURN v_totalHours;
END;
$BODY$
LANGUAGE plpgsql;
