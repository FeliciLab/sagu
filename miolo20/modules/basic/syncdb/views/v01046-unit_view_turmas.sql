CREATE OR REPLACE view unit_view_turmas AS
SELECT 'P' as modulo,
       unit_acpofertaturma.ofertaturmaid::varchar as classId,
       unit_acpofertaturma.descricao as className,
       'de '||unit_acpofertaturma.datainicialoferta||' até '||unit_acpofertaturma.datafinaloferta as periodId,
       unit_acpcurso.cursoid::varchar as courseId,
       '1' as courseVersion,
       unit_acpcurso.nome as courseName,
       unit_acpofertaturma.dataencerramento::VARCHAR as closed
FROM unit_acpofertaturma
inner join unit_acpofertacurso using (ofertacursoid)
inner join unit_acpocorrenciacurso using (ocorrenciacursoid)
inner join unit_acpcurso using (cursoid)
union all
    SELECT 'A' as modulo,
           A.classId::varchar,
           A.name AS className,
           B.periodId,
           B.courseId,
           B.courseVersion,
           getCourseShortName(B.courseId) AS courseName,
           ((SELECT COUNT(*) FROM acdGroup WHERE classid = A.classid) = (SELECT COUNT(*) FROM acdGroup WHERE classid = A.classid AND isClosed IS TRUE))::VARCHAR AS closed
      FROM unit_acdClass A
INNER JOIN unit_acdLearningPeriod B
        ON (A.initialLearningPeriodId = B.learningPeriodId);
