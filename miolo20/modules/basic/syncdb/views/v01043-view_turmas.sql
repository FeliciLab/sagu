CREATE OR REPLACE view view_turmas AS
SELECT 'P' as modulo,
       acpofertaturma.ofertaturmaid::varchar as classId,
       acpofertaturma.descricao as className,
       'de '||acpofertaturma.datainicialoferta||' até '||acpofertaturma.datafinaloferta as periodId,
       acpcurso.cursoid::varchar as courseId,
       '1' as courseVersion,
       acpcurso.nome as courseName,
       acpofertaturma.dataencerramento::VARCHAR as closed
FROM acpofertaturma
inner join acpofertacurso using (ofertacursoid)
inner join acpocorrenciacurso using (ocorrenciacursoid)
inner join acpcurso using (cursoid)
union all
    SELECT distinct 'A' as modulo,
           A.classId::varchar,
           A.name AS className,
           B.periodId,
           B.courseId,
           B.courseVersion,
           getCourseShortName(B.courseId) AS courseName,
           ((SELECT COUNT(*) FROM acdGroup WHERE classid = A.classid) = (SELECT COUNT(*) FROM acdGroup WHERE classid = A.classid AND isClosed IS TRUE))::VARCHAR AS closed
      FROM acdClass A
INNER JOIN acdLearningPeriod B
        ON (A.initialLearningPeriodId = B.learningPeriodId);
