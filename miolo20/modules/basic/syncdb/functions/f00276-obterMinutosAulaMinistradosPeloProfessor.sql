CREATE OR REPLACE FUNCTION obterMinutosAulaMinistradosPeloProfessor(p_groupId INT)
RETURNS INT AS
$BODY$
/*************************************************************************************
  NAME: obterMinutosAulaMinistradosPeloProfessor
  PURPOSE: Retorna o total de minutos ministrados pelo professor em uma deterinada disciplina oferecida
           Para converter em horas, dividir o resultado da função por 60.
           Para obter a sobra de minutos ministrados em horas, % 60.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_minutosAulaMinistradas INT := 0;
BEGIN
    SELECT INTO v_minutosAulaMinistradas SUM(X.minutosministrados)
      FROM ( SELECT B.scheduleProfessorId,
                    split_part((EXTRACT(EPOCH FROM E.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT AS minutosMinistrados
               FROM acdScheduleProfessorContent A
         INNER JOIN acdScheduleProfessor B
              USING (scheduleProfessorId)
         INNER JOIN acdSchedule C
              USING (scheduleId)
         INNER JOIN acdGroup D
              USING (groupId)
         INNER JOIN acdTime E
              USING (timeId)
              WHERE D.groupId = p_groupId ) X;
	       
    RETURN v_minutosAulaMinistradas;
END;
$BODY$
LANGUAGE plpgsql;
