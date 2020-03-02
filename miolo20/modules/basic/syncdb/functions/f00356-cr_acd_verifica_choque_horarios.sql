CREATE OR REPLACE FUNCTION cr_acd_verifica_choque_horarios(periodo varchar, cod_curso varchar, cod_professor integer DEFAULT 0)
RETURNS SETOF type_schedule AS $$
DECLARE

    v_record RECORD;
    v_schedule type_schedule;

    v_dadosChoque RECORD;

    v_professorid integer;
    v_groupid integer;

BEGIN

    v_professorid := 0;
    v_groupid     := 0;

    FOR v_record IN SELECT P.professorid, P.scheduleid, S.groupid, S.unitId
                      FROM acdscheduleprofessor P
                 LEFT JOIN acdschedule S ON (P.scheduleid = S.scheduleid)
                 LEFT JOIN acdgroup G ON (S.groupid = G.groupid)
                 LEFT JOIN acdlearningperiod L ON (G.learningperiodid = L.learningperiodid)
                 LEFT JOIN acdcurriculum C ON (G.curriculumid = C.curriculumid)
                     WHERE L.periodid = periodo
                       AND C.courseid = cod_curso
             AND CASE WHEN cod_professor > 0 THEN P.professorid = cod_professor ELSE TRUE END
                  ORDER BY P.professorid
    LOOP

        IF v_record.professorid = v_professorid THEN
        BEGIN
            IF v_record.groupid != v_groupid THEN
                IF hasShockingSchedule(v_record.groupid, v_groupid) THEN
                BEGIN
                                         
                    SELECT INTO v_dadosChoque
                                *
                          FROM getShockingSchedules(v_record.groupid, v_groupid);

                    SELECT INTO v_schedule v_record.professorid, getpersonname(v_record.professorid), v_record.groupid, obterNomeDisciplina(v_record.groupid), v_groupid, obterNomeDisciplina(v_groupid), getUnitDescription(v_record.unitid), v_dadosChoque.datas, v_dadosChoque.horarios, v_dadosChoque.diasSemana;
                    RETURN NEXT v_schedule;
                END;
                END IF;
            END IF;

            v_groupid := v_record.groupid;
        END;
        ELSE
        BEGIN
            v_professorid := v_record.professorid;
            v_groupid     := v_record.groupid;
        END;
        END IF;

    END LOOP;

    RETURN;
END;
$$ LANGUAGE plpgsql;
