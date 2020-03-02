CREATE OR REPLACE VIEW view_acp_pedagogico AS (
    SELECT *
      FROM cr_acp_inscricao_matricula
     WHERE acpmatricula_matriculaid <> 0
);
