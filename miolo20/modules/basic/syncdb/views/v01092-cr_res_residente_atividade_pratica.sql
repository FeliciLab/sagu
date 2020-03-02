CREATE OR REPLACE VIEW cr_res_residente_atividade_pratica AS (
    SELECT R.*,
           AP.atividadePraticaId AS codigo_atividade_pratica,
           --AP.descricao AS atividade_pratica, --Coluna foi removida em #38562.
           AP.inicio AS data_inicio_atividade_pratica,
           dateToUser(AP.inicio) AS data_inicio_atividade_pratica_formatada,
           AP.fim AS data_fim_atividade_pratica,
           dateToUser(AP.fim) AS data_fim_atividade_pratica_formatada,
           AP.local AS local_atividade_pratica,
           AP.nota AS nota_atividade_pratica,
           AP.cargaHoraria AS carga_horaria_atividade_pratica
      FROM cr_res_residente R
INNER JOIN res.atividadePratica AP
        ON AP.residenteId = R.codigo_residente
);
