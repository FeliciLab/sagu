--
CREATE OR REPLACE FUNCTION obterAlunosAtivosDaDisciplina(p_groupid INT)
RETURNS SETOF basphysicalperson AS
$BODY$
/*************************************************************************************
  NAME: obterAlunosPorDisciplina
  PURPOSE: Obter os alunos ativos de uma disciplina.
**************************************************************************************/

BEGIN
    RETURN QUERY ( SELECT D.*
                  FROM acdEnroll A
            INNER JOIN acdContract B
                    ON (A.contractId = B.contractId)
            INNER JOIN acdEnrollStatus C
                    ON (C.statusId = A.statusId)
       INNER JOIN ONLY basPhysicalPerson D
                    ON (B.personId = D.personId)
                 WHERE A.groupId = p_groupid
                   AND (A.statusid <> getparameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int 
                   AND  A.statusid <> getparameter('ACADEMIC', 'ENROLL_STATUS_DESISTING')::int 
                   AND  A.statusid <> getparameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::int )
              ORDER BY 1 );
END;
$BODY$
LANGUAGE 'plpgsql';
