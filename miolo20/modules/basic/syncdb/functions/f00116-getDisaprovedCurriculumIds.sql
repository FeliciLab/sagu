CREATE OR REPLACE FUNCTION getdisaprovedcurriculumids(p_contractid integer)
  RETURNS SETOF integer AS
$BODY$
/*********************************************************************************************
   NAME: getdisaprovedcurriculumids
   PURPOSE: Busca todas discplinas reprovadas do contrato.

REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       xx/xx/xxxx xxxxxxxxx         1. FUNÇÂO criada.
  1.1       04/02/2013 Samuel Koch       1. Correção na lógica que verifica se o contrato é
                                            de um calouro ou veterano.
  1.2       05/02/2013 Samuel Koch       1. Correção no recálculo das mensalidades.
  1.3       26/02/2013 Samuel Koch       1. Alteração na regra quando o aluno se matricula em
                                            dependência do tipo prática. Caso a disciplina tenha
                                            alguma hora prática considerar 100% prática.
  1.4       02/03/2013 Samuel Koch       1. Alteração para separar as disciplinas que o aluno
                                            irá cursar em outros semestres
*********************************************************************************************/
DECLARE
BEGIN
    RETURN QUERY ( SELECT A.curriculumId
                     FROM acdCurriculum A
               INNER JOIN acdCurricularComponent C
                       ON (C.curricularComponentId = A.curricularComponentId
                      AND C.curricularComponentVersion = A.curricularComponentVersion)
                    WHERE A.curriculumid IN ( SELECT curriculumid
                                                FROM acdEnroll X
                                               WHERE X.contractId = p_contractid
                                                 AND X.curriculumId = A.curriculumId
                                                 AND X.statusId::varchar = ANY(STRING_TO_ARRAY(GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED'), ',')))
                 ORDER BY A.curricularComponentId || '' || A.curricularComponentVersion || '' || C.name );
END
$BODY$
LANGUAGE 'plpgsql'
