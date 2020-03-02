CREATE OR REPLACE VIEW rptHorarios AS (
        SELECT x.scheduleid || '' || x.timeid || to_char(x.occurrencedate, 'mmdd') AS horarioidunique,
               x.*
          FROM (
            select unnest(occurrencedates) AS occurrencedate,
                   t.timeid,
                   s.scheduleid,
                   s.groupid,
                   s.unitid,
                   s.weekdayid,
                   s.physicalresourceid,
                   s.physicalresourceversion,
                   timetouser(t.beginhour) AS beginhour,
                   timetouser(t.endhour) AS endhour,
                   t.numberminutes,
                   PR.room,
                   PR.building,
                   PR.description
              from acdgroup g
        inner join acdschedule s
                on s.groupid = g.groupid
        inner join acdtime t
                on t.timeid = ANY(s.timeids)
         LEFT JOIN insPhysicalResource PR
                ON (PR.physicalresourceid,
                    PR.physicalresourceversion) = (s.physicalresourceid,
                                                   s.physicalresourceversion)
        ) x
);

COMMENT ON COLUMN rptHorarios.occurrencedate IS 'Data da aula';
COMMENT ON COLUMN rptHorarios.timeid IS 'Hora da aula';
COMMENT ON COLUMN rptHorarios.horarioidunique IS 'Identificador unico, utilizado para lookups (academic - lookupHorarios) e relatorios para simplificar filtros';
