CREATE OR REPLACE FUNCTION removeLogFrequenciaPortal()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: removeLogFrequenciaPortal
  DESCRIPTION: Trigger que remove registro da tabela de log de frequencia do 
               portal quando uma enroll é excluída

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       13/02/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
BEGIN
    DELETE FROM logfrequenciaportal WHERE enrollid = OLD.enrollid;
    RETURN OLD;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_removeLogFrequenciaPortal ON acdEnroll;
CREATE TRIGGER trg_removeLogFrequenciaPortal BEFORE DELETE ON acdEnroll
  FOR EACH ROW EXECUTE PROCEDURE removeLogFrequenciaPortal();
