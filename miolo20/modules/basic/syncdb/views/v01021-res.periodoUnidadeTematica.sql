CREATE OR REPLACE VIEW res.periodoUnidadeTematica AS
SELECT 'P1'::varchar AS periodoId, 'Primeiro ano'::varchar AS descricao
UNION
SELECT 'P2'::varchar, 'Segundo ano'::varchar
UNION
SELECT 'P3'::varchar, 'Terceiro ano'::varchar;
