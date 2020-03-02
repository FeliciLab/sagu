CREATE OR REPLACE VIEW med.periodoUnidadeTematica AS
    SELECT DISTINCT periodo as periodoid,
           descricao
      FROM med.periodo;
