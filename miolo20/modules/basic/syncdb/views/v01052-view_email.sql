create or replace view view_email as
        SELECT distinct
                        'A' as modulo,
                        A.personId,
                        A.name AS personName,
                        A.email,
                        A.emailalternative,
                        C.groupId::varchar,
                        C.statusId::varchar
                FROM basPhysicalPersonStudent A
          INNER JOIN acdContract B ON (A.personId = B.personId)
          INNER JOIN acdEnroll C   ON (B.contractId = C.contractId)
	  INNER JOIN acdGroup D    ON (D.groupid=C.groupid)
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
          INNER JOIN acpmatricula BB using (personId)
          INNER JOIN acpofertacomponentecurricular CC using (ofertacomponentecurricularid)
          INNER JOIN acpofertaturma DD using (ofertaturmaid) ;
