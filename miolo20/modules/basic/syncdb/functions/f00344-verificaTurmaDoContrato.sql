CREATE OR REPLACE FUNCTION verificaTurmaDoContrato()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaTurmaDoContrato
  DESCRIPTION: Verifica se a turma é da mesma ocorrência do contrato do aluno.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       24/02/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    v_ocorrencia_turma RECORD;
    v_ocorrencia_contrato RECORD;
BEGIN
    -- Devido a registro legados
    IF TG_OP = 'UPDATE'
    THEN
        IF OLD.classId = NEW.classId
        THEN
            RETURN NEW;
        END IF;
    END IF;

    -- Verifica sempre que for cadastrado uma turma nova para o aluno
    SELECT INTO v_ocorrencia_turma B.courseId, B.courseVersion, B.unitId, B.turnId 
      FROM acdClass A
INNER JOIN acdLearningPeriod B
        ON A.initiallearningperiodid = B.learningPeriodId
     WHERE A.classId = NEW.classId;

    SELECT INTO v_ocorrencia_contrato D.courseId, D.courseVersion, D.unitId, D.turnId 
      FROM acdContract D
     WHERE D.contractId = NEW.contractId;

    IF v_ocorrencia_turma <> v_ocorrencia_contrato 
    THEN
       RAISE EXCEPTION 'A turma % não pertence a mesma ocorrência do curso do contrato %. Escolha uma turma da mesma ocorrência para vincular ao aluno.',NEW.classId, NEW.contractId;
    END IF;
	
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_verificaTurmaDoContrato ON acdClassPupil;
CREATE TRIGGER trg_verificaTurmaDoContrato BEFORE INSERT OR UPDATE ON acdClassPupil
  FOR EACH ROW EXECUTE PROCEDURE verificaTurmaDoContrato();
