CREATE OR REPLACE VIEW cr_acp_situacao_alunos_por_curso AS (
    SELECT *
      FROM view_situacao_alunos_por_curso_pedagogico
);