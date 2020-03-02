CREATE OR REPLACE FUNCTION verificaVagasDaTurmaMedica()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaVagasDaTurmaMedica
  DESCRIPTION: Trigger que verifica vagas disponíveis da turma do residente
  medico.

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
        SELECT INTO v_turma *,
                    COALESCE((SELECT COUNT(*) FROM med.residente aa WHERE aa.turmaid = medturma.turmaid AND residenteid != NEW.residenteid),0) as vagasocupada
               FROM med.turma medturma
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

DROP TRIGGER IF EXISTS trg_verificaVagasDaTurma ON med.residente;
DROP TRIGGER IF EXISTS trg_verificaVagasDaTurmaMedica ON med.residente;
CREATE TRIGGER trg_verificaVagasDaTurmaMedica
  BEFORE INSERT OR UPDATE
  ON med.residente
  FOR EACH ROW
  EXECUTE PROCEDURE verificaVagasDaTurmaMedica();
