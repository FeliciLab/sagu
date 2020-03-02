CREATE OR REPLACE VIEW cr_acp_boletim_de_desempenho AS(
    SELECT acpinscricao_inscricaoid AS inscricaoid,
           acpcomponentecurricular_descricao as disciplina,
           COALESCE(B.notafinal::varchar, B.conceitofinal) AS notafinal,
           B.frequencia as percentualfrequencia,
           g.ordem as ordemavaliacao,
           G.descricao as nomeavaliacao,
           COALESCE(F.nota::varchar, F.conceito::varchar) as notaavaliacao
      FROM cr_acp_inscricao_matricula A
INNER JOIN acpmatricula B
        ON a.acpmatricula_matriculaid = b.matriculaid
 LEFT JOIN acpcurso C
        ON C.cursoid = A.acpcurso_cursoid
 LEFT JOIN acpperfilcurso D
        ON D.perfilcursoid = C.perfilcursoid
 LEFT JOIN acpmodelodeavaliacao E
        ON D.modelodeavaliacaogeral = E.modelodeavaliacaoid
 LEFT JOIN acpavaliacao F
        ON F.matriculaid = B.matriculaid
 LEFT JOIN acpcomponentedeavaliacao G
        ON G.modelodeavaliacaoid = E.modelodeavaliacaoid
group by 1,2,3,4,5,6,7 -- NÃO USEI O NOME DA COLUNA POIS TERIA QUE COLOCAR O COALESCE AQUI TAMBÉM
);
