CREATE OR REPLACE FUNCTION obterDisciplinasFinalizadas(p_contrato INTEGER)
RETURNS TABLE 
(nomeCursada TEXT,
 nomeOriginal TEXT,
 oferecida INTEGER,
 curriculum INTEGER,
 matricula INTEGER,
 estado TEXT,
 dataMatricula DATE,
 estadoId INTEGER,
 periodo VARCHAR,
 semestre INT,
 curricularComponentTypeId INT) AS 
/*************************************************************************************
  NAME: obterDisciplinasFinalizadas
  PURPOSE: Obtém todas as disciplinas já finalizadas pelo aluno.
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Função criada.
**************************************************************************************/
$BODY$
DECLARE

v_result RECORD;
    
BEGIN
    RETURN QUERY 
    SELECT U.curricularComponentId || '/' || U.curricularComponentVersion || ' - ' || U.name,
	   OU.curricularComponentId || '/' || OU.curricularComponentVersion || ' - ' || OU.name,
           E.groupId,
           E.curriculumId,
           E.enrollId,
           getEnrollStatusDescription(E.statusId),
           E.dateEnroll,
           E.statusId,
           COALESCE((SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = G.learningPeriodId),
                    (SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = E.learningPeriodId)) AS periodId,
           OC.semester,
           OC.curricularComponentTypeId
      FROM acdEnroll E
 LEFT JOIN acdGroup G --Tabelas da oferecida precisam ser LEFT JOIN, aproveitamentos não tem disciplina oferecida
        ON (G.groupId = E.groupId)
 LEFT JOIN acdCurriculum C
        ON (G.curriculumId = C.curriculumId)
 LEFT JOIN acdCurricularComponent U
        ON (C.curricularComponentId = U.curricularComponentId)
       AND (C.curricularComponentVersion = U.curricularComponentVersion)
INNER JOIN acdCurriculum OC
        ON (E.curriculumId = OC.curriculumId)
INNER JOIN acdCurricularComponent OU
        ON (OC.curricularComponentId = OU.curricularComponentId)
       AND (OC.curricularComponentVersion = OU.curricularComponentVersion)
     WHERE E.contractId = p_contrato
       AND E.statusId IN ( getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT,
                           getParameter('ACADEMIC', 'ENROLL_STATUS_EXCUSED')::INT)
       AND OC.semester > 0
  ORDER BY OC.semester;
END;

$BODY$ LANGUAGE plpgsql IMMUTABLE;