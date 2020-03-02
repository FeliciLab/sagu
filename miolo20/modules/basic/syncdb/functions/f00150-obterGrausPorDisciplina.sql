--
CREATE OR REPLACE FUNCTION obterGrausPorDisciplina(p_groupid INT)
RETURNS SETOF acdDegree AS
$BODY$
/*************************************************************************************
  NAME: obterGrausPorDisciplina
  PURPOSE: Obter os graus de uma disciplina.
**************************************************************************************/

BEGIN
    RETURN QUERY ( SELECT D.* 
	       FROM acdGroup G 
          LEFT JOIN acdLearningPeriod L ON (G.learningPeriodId = L.learningPeriodId) 
          LEFT JOIN acdDegree D ON (L.learningPeriodId = D.learningPeriodId) 
              WHERE G.groupId = p_groupid
           ORDER BY D.degreeNumber );
END;
$BODY$
LANGUAGE 'plpgsql';
