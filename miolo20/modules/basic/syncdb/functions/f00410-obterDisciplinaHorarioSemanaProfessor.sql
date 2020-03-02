CREATE OR REPLACE FUNCTION obterDisciplinaHorarioSemanaProfessor(p_professorId BIGINT, p_learningPeriodId INT, p_timeId INT, p_diaSemanaId INT)
RETURNS VARCHAR AS
$BODY$
BEGIN
    RETURN (
        SELECT array_to_string(
            ARRAY(SELECT DISTINCT cc.curricularcomponentid || '/' || cc.curricularcomponentversion || ' - ' || cc.name || ' - '|| gg.groupId || COALESCE(' - ' || PR.room, '')
                             FROM acdgroup gg 
                       INNER JOIN acdlearningperiod lp1 
                               ON lp1.learningperiodid = gg.learningperiodid 
                       INNER JOIN acdcurriculum cu 
                               ON cu.curriculumid = gg.curriculumid 
                       INNER JOIN acdcurricularcomponent cc 
                               ON (cu.curricularcomponentid, 
                                  cu.curricularcomponentversion) = (cc.curricularcomponentid, 
                                                                    cc.curricularcomponentversion) 
                       INNER JOIN acdschedule s 
                               ON s.groupid = gg.groupid 
                       INNER JOIN acdtime t 
                               ON t.timeid = ANY(s.timeids) 
                        LEFT JOIN insPhysicalResource PR 
                               ON (PR.physicalresourceid, 
                                   PR.physicalresourceversion) = (s.physicalresourceid, 
                                                                  s.physicalresourceversion) 
                        LEFT JOIN acdscheduleprofessor sp 
                               ON sp.scheduleid = s.scheduleid 
                            WHERE lp1.learningperiodid = p_learningPeriodId
                              AND t.timeid = p_timeId
                              AND p_diaSemanaId IN ( SELECT extract(DOW FROM dts.date) 
                                                       FROM (SELECT UNNEST(s.occurrencedates) AS DATE) dts ) 
                              AND sp.professorid = p_professorId::INT
                         ORDER BY 1 ), E'\n\n')
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE
