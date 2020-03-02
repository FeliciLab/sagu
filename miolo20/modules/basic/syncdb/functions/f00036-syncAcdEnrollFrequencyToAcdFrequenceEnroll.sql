--
-- 12/06/2012 - Jonas - Alteracao na FUNÇÃO (trigger) syncAcdEnrollFrequencyToAcdFrequenceEnroll() por erro no calculo de horas/aula
--
CREATE OR REPLACE FUNCTION syncAcdEnrollFrequencyToAcdFrequenceEnroll()
RETURNS TRIGGER AS
$BODY$
/*************************************************************************************
  NAME: syncAcdEnrollFrequencyToAcdFrequenceEnroll
  PURPOSE: Manter o campo acdEnroll.frequency atualizado.
  DESCRIPTION:
  Trigger executada sempre que a acdFrequenceEnroll sofre alguma alteração, para que o
  valor de total de frequéncia na acdEnroll seja definido como sendo a soma de todas
  as horas em que o aluno tem frequéncia registrada.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       10/06/2011 Alex Smith        1. FUNÇÃO criada.
  1.1       30/11/2011 Moises Heberle    1. Corrigindo arrendondamento para padrao
                                           do SAGU.
  1.2       12/06/2012 Jonas e Samuel    1. Corrigindo calculo de horas/aula.
  1.3       05/06/2014 Nataniel          1. Corrigindo calculo de horas/aula.
**************************************************************************************/
BEGIN
    IF TG_OP <> 'DELETE'
    THEN
    UPDATE acdEnroll A
       SET frequency = COALESCE(ROUND((SELECT SUM(
                                              EXTRACT(HOURS   FROM (C.numberminutes) * B.frequency) +       
                                              EXTRACT(MINUTES FROM (C.numberminutes) * B.frequency) / 60 +  
                                              EXTRACT(SECONDS FROM (C.numberminutes) * B.frequency) / 60 / 6)::float
                                         FROM acdFrequenceEnroll B
                                   INNER JOIN acdTime C
                                           ON C.timeId = B.timeId
                                   INNER JOIN acdSchedule D
			                   ON ( B.scheduleId = D.scheduleId
                                          AND   B.frequencyDate = ANY (D.occurrencedates) )
                                        WHERE B.enrollId = A.enrollId)::numeric,  GETPARAMETER('BASIC','FREQUENCY_ROUND_VALUE')::int), frequency)
    WHERE A.enrollId = NEW.enrollId;

        RETURN NEW;
    ELSE

    UPDATE acdEnroll A
       SET frequency = COALESCE(ROUND((SELECT SUM(
                                              EXTRACT(HOURS   FROM (C.numberminutes) * B.frequency) +       
                                              EXTRACT(MINUTES FROM (C.numberminutes) * B.frequency) / 60 +  
                                              EXTRACT(SECONDS FROM (C.numberminutes) * B.frequency) / 60 / 6)::float
                                         FROM acdFrequenceEnroll B
                                   INNER JOIN acdTime C
                                           ON C.timeId = B.timeId
                                   INNER JOIN acdSchedule D
			                   ON ( B.scheduleId = D.scheduleId
                                          AND   B.frequencyDate = ANY (D.occurrencedates) )
                                        WHERE B.enrollId = A.enrollId)::numeric,  GETPARAMETER('BASIC','FREQUENCY_ROUND_VALUE')::int), frequency)
     WHERE A.enrollId = OLD.enrollId;

        RETURN OLD;
    END IF;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS acdFrequenceEnrollSyncToAcdEnroll ON acdFrequenceEnroll;
CREATE TRIGGER acdFrequenceEnrollSyncToAcdEnroll
    AFTER INSERT OR UPDATE OR DELETE ON acdFrequenceEnroll
    FOR EACH ROW
    EXECUTE PROCEDURE syncAcdEnrollFrequencyToAcdFrequenceEnroll();
--
