CREATE OR REPLACE FUNCTION registraPeriodoDaTurmaNoResidente()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: registraPeriodoDaTurmaNoResidente
  DESCRIPTION: Trigger que registra o período da turma para o residente

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       06/11/14   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    v_turma RECORD;   
BEGIN
    IF NEW.turmaid IS NOT NULL
    THEN
        SELECT INTO v_turma *
               FROM res.turma
              WHERE turmaid = NEW.turmaid;

	NEW.inicio = v_turma.datainicio;
	NEW.fimprevisto = v_turma.datafim;
    END IF;
    
   RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_registraPeriodoDaTurmaNoResidente ON res.residente;
CREATE TRIGGER trg_registraPeriodoDaTurmaNoResidente
  BEFORE INSERT OR UPDATE
  ON res.residente
  FOR EACH ROW
  EXECUTE PROCEDURE registraPeriodoDaTurmaNoResidente();
