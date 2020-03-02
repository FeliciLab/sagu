CREATE OR REPLACE VIEW rptmatriculas AS (
/*************************************************************************************
  NOME: rptmatriculas
  DESCRIÇÃO: Visão que obtem alunos matriculados, utilizada em relatorios como Ata
   de vestibular, Matriculados por disciplina, etc..
**************************************************************************************/
         SELECT A.enrollId AS codmatricula,
                C.personId AS codpessoa,
                C.name AS nomepessoa,
                B.courseId AS codcurso,
                A.statusId AS codestadomatricula,
                G.description AS estadomatricula,
                TO_CHAR(A.dateCancellation, 'dd/mm/yyyy') AS datacancelamento,
                E.finalaverage AS mediafinal,
                F.courseVersionTypeId AS codversaocurso,
                B.courseVersion AS versaocurso,
                row_number() OVER (ORDER BY C.name) as linhanumero,
                obternotaouconceitofinal(A.enrollid) AS notafinal,
                (SELECT name FROM bascompanyConf LIMIT 1) AS centro,
                E.description AS periodoletivo,
                timestamptouser(now()::timestamp) AS dataemissao
           FROM acdEnroll A
     INNER JOIN acdContract B
             ON (A.contractId = B.contractId)
     INNER JOIN ONLY basPhysicalPerson C
             ON (B.personId = C.personId)
     INNER JOIN unit_acdGroup D
             ON (D.groupId = A.groupId)
     INNER JOIN unit_acdlearningperiod E
             ON (E.learningPeriodId = D.learningPeriodId)
     INNER JOIN acdCourseVersion F
             ON (F.courseId = B.courseId AND F.courseVersion = B.courseVersion)
     INNER JOIN acdEnrollStatus G
             ON (G.statusId = A.statusId)
       ORDER BY C.name
);
