CREATE OR REPLACE FUNCTION getfatherid(bigint) RETURNS bigint
    LANGUAGE sql
    AS $_$
    SELECT X.relativePersonId::bigint
      FROM basPhysicalPersonKinship X
     WHERE X.personId = $1
       AND X.kinshipId = (SELECT value::integer
                            FROM basConfig
                           WHERE parameter = 'FATHER_KINSHIP_ID'
                             AND moduleConfig = 'BASIC');
$_$;