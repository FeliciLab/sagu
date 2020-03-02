CREATE OR REPLACE VIEW med.tipoUnidadeTematica AS
SELECT DISTINCT tipoDeUnidadeTematicaId as tipoid,
       descricao
  FROM med.tipoDeUnidadeTematica;
