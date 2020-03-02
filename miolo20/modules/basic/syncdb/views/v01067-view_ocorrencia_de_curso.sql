CREATE OR REPLACE VIEW view_ocorrencia_de_curso AS
SELECT                 null as ocorrenciacursoid,
                       A.courseId,
                       A.courseVersion,
                       B.name AS courseName,
                       A.turnId,
                       C.description AS turnDescription,
                       A.unitId,
                       D.description AS unitDescription,
                       B.formationLevelId,
                       E.description AS formationLevelDescription,
                       A.authorizationDate,
                       A.authorizationDocument,
                       A.status,
                       A.minimumConclusionCourse,
                       A.maximumConclusionCourse,
                       'A' as modulo
                  FROM acdcourseoccurrence A
             LEFT JOIN acdcourse B
                    ON (B.courseId = A.courseId)
             LEFT JOIN basTurn C
                    ON (C.turnId = A.turnId)
             LEFT JOIN basUnit D
                    ON (D.unitId = A.unitId)
             LEFT JOIN acdFormationLevel E
                    ON (E.formationLevelId = B.formationLevelId)
UNION
    Select
       ocorrenciacurso.ocorrenciacursoid,
       ocorrenciacurso.cursoid::text as courseid,
       null as courseVersion,
       curso.nome,
       ocorrenciacurso.turnid,
       turno.description as turnDescription,
       ocorrenciacurso.unitid,
       unidade.description as unitDescription,
       null as formationLevelId,
       null as formationLevelDescription,
       null as autorizationDate,
       null as autorizationDocument,
       null as status,
       null as minimumConclusionCourse,
       null as maximumConclusionCourse,
       'P' as modulo
From
       acpocorrenciacurso ocorrenciacurso
         Left Join acpcurso curso on ocorrenciacurso.cursoid=curso.cursoid
         Left Join basturn turno on ocorrenciacurso.turnid=turno.turnid
         Left Join basunit unidade on ocorrenciacurso.unitid=unidade.unitid;
