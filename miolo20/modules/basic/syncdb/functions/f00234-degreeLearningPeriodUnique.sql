CREATE OR REPLACE FUNCTION degreelearningperiodunique()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: degreeLearningPeriodUnique
  DESCRIPTION: Trigger que não permite cadastrar um grau academico para um período
               letivo com a mesma descrição de um grau que já existe.

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   ftomasini          Bloqueia inconcistencia no cadastro de
                                          graus acadêmicos de um período letivo. 
******************************************************************************/
DECLARE
    v_validate BOOLEAN;
BEGIN

    SELECT INTO v_validate CASE WHEN degreeid IS NOT NULL THEN FALSE ELSE TRUE END 
      FROM acddegree
     WHERE description ilike NEW.description
       AND learningperiodid = NEW.learningperiodid
       AND degreeid != NEW.degreeid;

    IF (TG_OP = 'INSERT' OR  TG_OP = 'UPDATE' ) AND (v_validate IS FALSE)
    THEN
        RAISE EXCEPTION 'Erro ao salvar o período letivo, provavelmente o cadastro foi aberto em mais de uma aba do navegador, feche uma das abas e refaça o processo.';
    END IF;

    RETURN NEW;
    
END;
$BODY$
  LANGUAGE plpgsql ;

DROP TRIGGER IF EXISTS trg_degreelearningperiodunique ON acddegree;
CREATE TRIGGER trg_degreelearningperiodunique
  BEFORE INSERT OR UPDATE
  ON acddegree
  FOR EACH ROW
  EXECUTE PROCEDURE degreelearningperiodunique();
