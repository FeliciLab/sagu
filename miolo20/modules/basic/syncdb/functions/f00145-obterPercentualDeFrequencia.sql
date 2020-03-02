CREATE OR REPLACE FUNCTION obterPercentualDeFrequencia(p_enrollid integer)
RETURNS double precision AS
$BODY$
/*************************************************************************************
  NAME: obterPercentualDeFrequencia
  PURPOSE: Retorna a frequência de um aluno em uma disciplina, em %.
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       09/05/2013 Bruno Fuhr        1. FUNÇÃO criada.
**************************************************************************************/
BEGIN
    RETURN ( SELECT ROUND( ((A.frequency * 100) / D.academicNumberHours)::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::INT ) 
               FROM acdEnroll A
         INNER JOIN acdGroup B
                 ON (A.groupId = B.groupId)
         INNER JOIN acdCurriculum C
                 ON (B.curriculumId = C.curriculumId)
         INNER JOIN acdCurricularComponent D
                 ON (C.curricularComponentId = D.curricularComponentId 
                AND C.curricularComponentVersion = D.curricularComponentVersion)
              WHERE A.enrollid = p_enrollid);
END;
$BODY$
LANGUAGE plpgsql;
