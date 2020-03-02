--
CREATE OR REPLACE FUNCTION obterAlunosDaDisciplina(p_groupid INT)
RETURNS SETOF basphysicalperson AS
$BODY$
/*************************************************************************************
  NAME: obterAlunosPorDisciplina
  PURPOSE: Obter os alunos de uma disciplina.
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
              ORDER BY 1 );
END;
$BODY$
LANGUAGE 'plpgsql';
