CREATE OR REPLACE FUNCTION getShockingSchedules(p_groupId_1 INT, p_groupId_2 INT)
RETURNS TABLE (datas TEXT,
               horarios TEXT,
               diasSemana TEXT) AS
$BODY$
/******************************************************************************
  NAME: getShockingSchedules
  DESCRIPTION: Retorna os dias, horários e dias da semana do conflito de horário.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       19/05/2015 Luís F. Wermann      1. Função criada.
******************************************************************************/
DECLARE
    v_sql TEXT;
BEGIN
v_sql := '
    SELECT STRING_AGG(DISTINCT dateToUser(GROUP_1.date), ''' || ', ' || ''') AS dias,
       STRING_AGG(DISTINCT GROUP_1.beginHour || ''' || '/'  || ''' || GROUP_1.endHour, ''' || ', ' || ''') AS horarios,
       STRING_AGG(DISTINCT obterDiaExtenso((SELECT date_part(''' || 'dow' || ''', GROUP_1.date))::INT), ''' || ', ' || ''' ) AS diasSemana
       FROM (SELECT datas.scheduleid, 
                   date,  
                   x.beginhour, 
                   x.endhour 
              FROM (SELECT scheduleid,
                           UNNEST(occurrenceDates) AS date
                      FROM acdSchedule
                     WHERE groupId = ' || p_groupId_1  || ') datas
        INNER JOIN (SELECT scheduleid,
                           UNNEST(timeids) AS times
                      FROM acdSchedule a
                     WHERE groupId = ' || p_groupId_1 || ') tempos
                        ON (datas.scheduleid = tempos.scheduleid)
        INNER JOIN acdtime x
                ON x.timeid = tempos.times) AS GROUP_1           
INNER JOIN (SELECT datas.scheduleid, 
                   date,  
                    x.beginhour, 
                    x.endhour 
              FROM (SELECT scheduleid,
                           UNNEST(occurrenceDates) AS date
                      FROM acdSchedule
                     WHERE groupId = ' || p_groupId_2  || ') datas
        INNER JOIN (SELECT scheduleid,
                           UNNEST(timeids) AS times
                      FROM acdSchedule a
                     WHERE groupId = ' || p_groupId_2  || ') tempos
                        ON (datas.scheduleid = tempos.scheduleid)
        INNER JOIN acdtime x
                ON x.timeid = tempos.times ) AS GROUP_2
        ON GROUP_1.date = GROUP_2.date
       AND (SELECT (GROUP_1.beginhour,GROUP_1.endhour) OVERLAPS (GROUP_2.beginhour,GROUP_2.endhour)) ';
   
    RETURN QUERY EXECUTE v_sql;

END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;