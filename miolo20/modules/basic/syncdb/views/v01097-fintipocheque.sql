CREATE OR REPLACE VIEW fintipocheque AS 
    SELECT 1 AS codigo, 'AO PORTADOR' AS tipo
    UNION ALL
    SELECT 2 AS codigo, 'NOMINAL' AS tipo 
    UNION ALL
    SELECT 3 AS codigo, 'CRUZADO' AS tipo
    UNION ALL
    SELECT 4 AS codigo, 'PRÉ-DATADO' AS tipo
    UNION ALL
    SELECT 5 AS codigo, 'ESPECIAL' AS tipo
    UNION ALL
    SELECT 6 AS codigo, 'ADMINISTRATIVO' AS tipo;