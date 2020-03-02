CREATE OR REPLACE FUNCTION obterSemestreParaMatriculaEmSeriadoRigido(p_contractId INT, p_learningPeriodId INT)
RETURNS INT AS
$BODY$
/******************************************************************************
  NAME: obterSemestreParaMatriculaEmSeriadoRigido
  PURPOSE: Obtém o semestre do contrato para ser utilizado no semestre rígido.
  Lógica herdada originalmente da classe MatriculaSeriadoRigido.class

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       13/05/2015 Luís F. Wermann   1. Função criada.
******************************************************************************/
DECLARE
    --Semestre (lógica herdada de MatriculaSeriadoRigido.class)
    v_semestre INT;

BEGIN
--Busca semestre do aluno
    v_semestre := (SELECT get_semester_contract(p_contractId));

    --Parâmetro habilitado
    IF ( getParameter('ACADEMIC', 'DEFINE_SEMESTRE_POR_CARGA_HORARIA')::BOOLEAN IS TRUE )
        THEN
        --Possui disciplinas matriculadas no período letivo soma menos um no semestre
        IF ( (SELECT COUNT(E.enrollId)
                FROM acdEnroll E
          INNER JOIN acdGroup G
                  ON (G.groupId = E.groupId)
               WHERE G.learningPeriodId = p_learningPeriodId
                 AND E.contractId = p_contractId) > 0 )
        THEN
            v_semestre := (v_semestre - 1);
        END IF;
    END IF;

    RETURN v_semestre;

END;
$BODY$
LANGUAGE plpgsql
IMMUTABLE;