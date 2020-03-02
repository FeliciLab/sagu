CREATE OR REPLACE FUNCTION getGroupEndDate(p_oferecida INT)
RETURNS TEXT AS
/******************************************************************************************
  NAME: getGroupEndDate
  PURPOSE: Obtem data final da disciplina oferecida
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Criada função para obter data final da oferecida.
******************************************************************************************/
$BODY$
DECLARE

--Data final da oferecida    
v_dataFinal TEXT;
    
BEGIN

SELECT INTO v_dataFinal (SELECT TO_CHAR(MAX(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE')) AS endDate
   FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
           FROM acdSchedule Z
          WHERE Z.groupId = p_oferecida) X) AS endDate;

RETURN v_dataFinal;

END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;
