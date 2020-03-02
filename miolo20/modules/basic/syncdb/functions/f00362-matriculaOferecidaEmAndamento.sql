CREATE OR REPLACE FUNCTION matriculaOferecidaEmAndamento()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: matriculaOferecidaEmAndamento
  DESCRIPTION: Faz ajustes necessários nos dados quando a disciplina oferecida
  matriculada está em andamento.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       20/03/15   Luís F. Wermann       1. Trigger criada.
******************************************************************************/
DECLARE
    
    v_dias_aula RECORD;
    v_primeiro_dia DATE;
    v_horario RECORD;
    v_horarios_aula RECORD;

BEGIN

    --Busca a menor data (primeira aula)
    SELECT MIN(X.datas) INTO v_primeiro_dia
      FROM (SELECT UNNEST(A.occurrencedates) AS datas 
              FROM acdschedule A
        INNER JOIN acdScheduleProfessor B
                ON (B.scheduleId = A.scheduleId)
        INNER JOIN acdScheduleProfessorContent C
                ON (C.scheduleProfessorId = B.scheduleProfessorId)
             WHERE A.groupid = NEW.groupid
               AND C.description IS NOT NULL) X;

    --Se a data da matricula é menor que a data da primeira aula
    IF v_primeiro_dia < NEW.dateenroll 
    THEN

        FOR v_dias_aula IN (SELECT DISTINCT UNNEST(A.occurrencedates) AS dia,
                                  A.scheduleid 
                             FROM acdschedule A
                       INNER JOIN acdScheduleProfessor B
                               ON (B.scheduleId = A.scheduleId)
                       INNER JOIN acdScheduleProfessorContent C
                               ON (C.scheduleProfessorId = B.scheduleProfessorId)
                            WHERE A.groupid = NEW.groupid
                              AND C.description IS NOT NULL )
        LOOP

            FOR  v_horarios_aula IN (SELECT UNNEST(A.timeIds) as horarioId
                                      FROM acdSchedule A
                                     WHERE A.scheduleId = v_dias_aula.scheduleId)
            LOOP
            
                IF ( v_dias_aula.dia < NEW.dateenroll )
                THEN
		    BEGIN
                        INSERT INTO acdfrequenceenroll(enrollid, scheduleid, frequencydate, frequency, justification, timeid) VALUES(new.enrollid, v_dias_aula.scheduleid, v_dias_aula.dia, 0, 'Aluno matrículado após a data dessa aula. (Data da matrícula '|| NEW.dateenroll ||')', v_horarios_aula.horarioid);
		    END;
                END IF;

            END LOOP;

        END LOOP;
    
    END IF;

    RETURN NEW;

END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_matriculaOferecidaEmAndamento ON acdEnroll;
CREATE TRIGGER trg_matriculaOferecidaEmAndamento
    AFTER INSERT ON acdEnroll
    FOR EACH ROW
    EXECUTE PROCEDURE matriculaOferecidaEmAndamento();
