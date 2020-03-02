CREATE OR REPLACE FUNCTION getGroupStartDate(p_oferecida INT)
RETURNS TEXT AS
/******************************************************************************************
  NAME: getGroupStartDate
  PURPOSE: Obtem data inicial da disciplina oferecida
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Criada função para obter data inicial da oferecida.
******************************************************************************************/
$BODY$
DECLARE

--Data inicial da oferecida    
v_dataInicial TEXT;
    
BEGIN

SELECT INTO v_dataInicial (SELECT TO_CHAR(MIN(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE')) AS startDate
   FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
           FROM acdSchedule Z
          WHERE Z.groupId = p_oferecida) X) AS startDate;

RETURN v_dataInicial;

END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;
