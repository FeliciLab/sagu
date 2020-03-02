CREATE OR REPLACE FUNCTION getCurrentEnrollId(p_contractId integer, p_groupId integer)
RETURNS INTEGER AS
$BODY$
/*************************************************************************************
  NAME: getCurrentEnrollId
  DESCRIPTION: Obtem o codigo da ultima matricula (enrollId)
    a partir de uma disciplina e contrato passado. Util para casos onde aluno
    possui varias matriculas em uma disciplina, sendo que anteriores estao como
    CANCELADAS ou outro estado.
**************************************************************************************/
DECLARE
BEGIN
    RETURN    (SELECT enrollId
                 FROM acdEnroll
                WHERE contractId = p_contractId
                  AND groupId = p_groupId
             ORDER BY dateTime DESC
                LIMIT 1);
END;
$BODY$
LANGUAGE plpgsql;
