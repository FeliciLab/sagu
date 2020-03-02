CREATE OR REPLACE FUNCTION registraPeriodoDaTurmaNoResidenteMedico()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: registraPeriodoDaTurmaNoResidenteMedico
  DESCRIPTION: Trigger que registra o período da turma para o residente medico

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       02/12/14   Lu\EDs F. Wermann       1. Trigger replicada do outro
                                             residencia.
******************************************************************************/
DECLARE
    v_turma RECORD;   
BEGIN
    IF NEW.turmaid IS NOT NULL
    THEN
        SELECT INTO v_turma *
               FROM med.turma
              WHERE turmaid = NEW.turmaid;

	NEW.inicio = v_turma.datainicio;
	NEW.fimprevisto = v_turma.datafim;
    END IF;
    
   RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_registraPeriodoDaTurmaNoResidente ON med.residente;
DROP TRIGGER IF EXISTS trg_registraPeriodoDaTurmaNoResidenteMedico ON med.residente;
CREATE TRIGGER trg_registraPeriodoDaTurmaNoResidenteMedico
  BEFORE INSERT OR UPDATE
  ON med.residente
  FOR EACH ROW
  EXECUTE PROCEDURE registraPeriodoDaTurmaNoResidenteMedico();
