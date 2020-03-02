CREATE OR REPLACE FUNCTION hasShockingSchedule(p_groupid_1 int, p_groupid_2 int) 
RETURNS BOOLEAN AS $$
/*************************************************************************************
  NAME: hasShockingSchedule
  PURPOSE: Check if the two groups have shocking schedules
  AUTOR: ftomasini
**************************************************************************************/
DECLARE
    v_has_shocking BOOLEAN;

BEGIN

    SELECT INTO v_has_shocking TRUE 
      FROM (SELECT datas.scheduleid, 
                   date,  
                   x.beginhour, 
                   x.endhour 
              FROM (SELECT scheduleid,
                           UNNEST(occurrenceDates) AS date
                      FROM acdSchedule
                     WHERE groupId = p_groupid_1) datas
        INNER JOIN (SELECT scheduleid,
                           UNNEST(timeids) AS times
                      FROM acdSchedule a
                     WHERE groupId = p_groupid_1) tempos
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
                     WHERE groupId = p_groupid_2) datas
        INNER JOIN (SELECT scheduleid,
                           UNNEST(timeids) AS times
                      FROM acdSchedule a
                     WHERE groupId = p_groupid_2) tempos
                        ON (datas.scheduleid = tempos.scheduleid)
        INNER JOIN acdtime x
                ON x.timeid = tempos.times ) AS GROUP_2
        ON GROUP_1.date = GROUP_2.date
       AND (SELECT (GROUP_1.beginhour,GROUP_1.endhour) OVERLAPS (GROUP_2.beginhour,GROUP_2.endhour));

    RETURN (v_has_shocking IS TRUE);
END;
$$
LANGUAGE plpgsql;
