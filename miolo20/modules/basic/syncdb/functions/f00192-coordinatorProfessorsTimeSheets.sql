CREATE OR REPLACE FUNCTION coordinatorprofessorstimesheets(p_coordinatorid integer, p_querymonth integer, p_queryyear integer)
RETURNS SETOF t_professortimesheet
AS $$
/*************************************************************************************
  NAME: coordinatorProfessorsTimeSheets
  PURPOSE: Retorna a planilha de horas de todos os professores vinculados ao
  coordenador de curso passado por parâmetro, no mês e ano especificados.
  DESCRIPTION: Faz uma busca pelos professores que têm aula em cursos dos quais a
  pessoa passada por parâmetro (p_coordinatorId) é coordenador. Para cada professor
  encontrado, retorna a sua planilha de horas.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       01/07/2011 Alexandre Schmidt 1. Função criada.
  1.1       09/01/2014 Bruno E. Fuhr     2. Ajuste ao chamar a função professorTimeSheet
**************************************************************************************/
DECLARE
    v_row RECORD;
    v_subrow RECORD;
    v_subsubrow RECORD;
BEGIN
    -- seleciona todos os professors que dão aula em cursos
    -- coordenados pelo v_coordinatorId
    FOR v_row IN SELECT DISTINCT A.professorId
                            FROM acdScheduleProfessor A
                      INNER JOIN acdSchedule B
                              ON B.scheduleId = A.scheduleId
                      INNER JOIN acdGroup C
                              ON C.groupId = B.groupId
                      INNER JOIN acdCurriculum D
                              ON D.curriculumId = C.curriculumId
                      INNER JOIN acdCourseCoordinator E
                              ON E.courseId = D.courseId
                             AND E.courseVersion = D.courseVersion
                             AND E.unitId = D.unitId
                             AND E.turnId = D.turnId
                           WHERE E.coordinatorId = p_coordinatorId
                        ORDER BY 1
    LOOP
        -- para cada professor encontrado, retornar a planilha de horas
        FOR v_subsubrow IN SELECT *
                             FROM professorTimeSheet(v_row.professorId::int,
                                                     p_queryMonth,
                                                     p_queryYear,
                                                     p_queryMonth,
                                                     p_queryYear,
                                                     NULL,
                                                     NULL,
                                                     NULL,
                                                     NULL) A
        LOOP
        BEGIN
            RAISE NOTICE '%', v_row.professorId;
            RETURN NEXT v_subsubrow;
        END;
        END LOOP;
    END LOOP;

    RETURN;
END;
$$ LANGUAGE plpgsql;
