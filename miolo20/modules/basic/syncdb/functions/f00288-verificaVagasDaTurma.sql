CREATE OR REPLACE FUNCTION verificaVagasDaTurma()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaVagasDaTurma
  DESCRIPTION: Trigger que verifica vagas disponíveis da turma do residente

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
        SELECT INTO v_turma *,
                    COALESCE((SELECT COUNT(*) FROM res.residente aa WHERE aa.turmaid = resturma.turmaid AND residenteid != NEW.residenteid),0) as vagasocupada
               FROM res.turma resturma
              WHERE turmaid = NEW.turmaid;

	IF v_turma.vagas <= v_turma.vagasocupada
	THEN
	    RAISE EXCEPTION 'A TURMA ''%'' ESTÁ COM SUAS VAGAS LOTADAS.',v_turma.descricao;
        END IF;
    END IF;
    
   RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_verificaVagasDaTurma ON res.residente;
CREATE TRIGGER trg_verificaVagasDaTurma
  BEFORE INSERT OR UPDATE
  ON res.residente
  FOR EACH ROW
  EXECUTE PROCEDURE verificaVagasDaTurma();
