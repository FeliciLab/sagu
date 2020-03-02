create or replace view unit_view_email as
        SELECT distinct
                        'A' as modulo,
                        A.personId,
                        A.name AS personName,
                        A.email,
                        A.emailalternative,
                        C.groupId::varchar,
                        C.statusId::varchar
                FROM basPhysicalPersonStudent A
          INNER JOIN unit_acdContract B ON (A.personId = B.personId)
          INNER JOIN unit_acdEnroll C   ON (B.contractId = C.contractId)
	  INNER JOIN unit_acdGroup D    ON (D.groupid=C.groupid)
UNION ALL
        SELECT distinct
                        'P' as modulo,
                        AA.personId,
                        AA.name AS personName,
                        AA.email,
                        AA.emailalternative,
                        DD.ofertaturmaid::varchar AS groupId,
                        BB.situacao::varchar AS statusId
                FROM basPhysicalPersonStudent AA
          INNER JOIN unit_acpmatricula BB using (personId)
          INNER JOIN unit_acpofertacomponentecurricular CC using (ofertacomponentecurricularid)
          INNER JOIN unit_acpofertaturma DD using (ofertaturmaid);
