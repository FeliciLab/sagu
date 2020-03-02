CREATE OR REPLACE VIEW resultado_avaliacao_peso AS (
    SELECT *, (CASE WHEN resposta = '5' THEN 1 ELSE (CASE WHEN resposta = '4' THEN 2 ELSE (CASE WHEN resposta = '2' THEN 4 ELSE (CASE WHEN resposta = '1' THEN 5 ELSE 3 END) END) END) END) as resposta_com_peso FROM resultado_avaliacao
);
