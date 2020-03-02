CREATE OR REPLACE VIEW res.tipoUnidadeTematica AS
SELECT DISTINCT tipoDeUnidadeTematicaId as tipoid,
       descricao
  FROM res.tipoDeUnidadeTematica;
