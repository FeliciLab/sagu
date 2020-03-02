CREATE OR REPLACE FUNCTION registraLogFechamentoDePeriodoContabil()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: registraLogFechamentoDePeriodoContabil
  DESCRIPTION: Trigger que registra o log de toda a alteração de um registro 
  da tabela accFechamentoDePeriodoContabil

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       23/10/14   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
        INSERT INTO accFechamentoDePeriodoContabilLog 
                    (fechamentoDePeriodoContabilId,estaFechado)
             VALUES (NEW.fechamentoDePeriodoContabilId,NEW.estaFechado);
        RETURN NEW;
    END IF;

    RETURN OLD;
END;
$BODY$
  LANGUAGE plpgsql ;

DROP TRIGGER IF EXISTS trg_registraLogFechamentoDePeriodoContabil ON accFechamentoDePeriodoContabil;
CREATE TRIGGER trg_registraLogFechamentoDePeriodoContabil
  AFTER INSERT OR UPDATE OR DELETE
  ON accFechamentoDePeriodoContabil
  FOR EACH ROW
  EXECUTE PROCEDURE registraLogFechamentoDePeriodoContabil();
