CREATE OR REPLACE FUNCTION curriculumEstaPendente(p_contractId INT, p_curriculumId INT, p_learningPeriodId INT)
RETURNS BOOLEAN AS
$BODY$
/******************************************************************************
  NAME: curriculumEstaPendente
  PURPOSE: Verifica se curriculumId está pendente para o aluno (utilizado para a
  montagem da tela de matrícula quando o curso é de seriado rígido).

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       08/05/2015 Luís F. Wermann   1. Função criada.
******************************************************************************/
DECLARE
    --Código do curriculum encontrado
    v_curriculumId INT;

    --Semestre (lógica herdada de MatriculaSeriadoRigido.class)
    v_semestre INT;

BEGIN
    
    v_semestre := (SELECT * FROM obterSemestreParaMatriculaEmSeriadoRigido(p_contractId, p_learningPeriodId));

    SELECT INTO v_curriculumId 
           A.curriculumId
      FROM acdcurriculum A
INNER JOIN acdContract B
        ON (B.courseId = A.courseId
       AND B.courseVersion = A.courseVersion
       AND B.turnId = A.turnId
       AND B.unitId = A.unitId)
INNER JOIN acdCurricularComponent C
        ON (C.curricularComponentId = A.curricularComponentId
       AND C.curricularComponentVersion = A.curricularComponentVersion)
     WHERE A.semester < v_semestre
       AND B.contractId = p_contractId
       AND A.curriculumId = p_curriculumId
       AND c.name NOT IN (SELECT CCE.name
                            FROM acdEnroll E
                      INNER JOIN acdcurriculum CE 
                              ON E.curriculumid = CE.curriculumid
                      INNER JOIN acdCurricularComponent CCE
                              ON CCE.curricularcomponentid = CE.curricularcomponentid
                           WHERE E.contractId = B.contractId
                             AND E.statusId::varchar = ANY(STRING_TO_ARRAY(GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_APPR_OR_EXC'), ','))
                        GROUP BY CCE.name);

    RETURN char_length(v_curriculumId::VARCHAR) > 0;

END;
$BODY$
LANGUAGE plpgsql
IMMUTABLE;
