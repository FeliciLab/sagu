CREATE OR REPLACE FUNCTION obterNotaOuConceitoFinal(p_enrollId int)
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterNotaOuConceitoFinal
  PURPOSE: Obtem a nota ou conceito final de uma matricula.
  DESCRIPTION: Obtem a nota ou conceito final de uma matricula. Esta FUNÇÃO
    é utilizada frequentemente em relatérios.
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN (
        COALESCE(
	    (SELECT nota                               
	       FROM getDegreeEnrollCurrentGrade(
	           (SELECT DE.degreeId                   
		      FROM acdDegreeEnroll DE           
		INNER JOIN acdDegree DEG 
		        ON DEG.degreeId = DE.degreeId   
		INNER JOIN acdenroll e 
		        ON (e.enrollid = DE.enrollid)
		 LEFT JOIN acdgroup g 
		        ON (g.groupid = e.groupid 
		       AND DEG.learningperiodid = g.learningperiodid)
		     WHERE DE.enrollId = p_enrollId     
		       AND DEG.parentDegreeId IS NULL
		  ORDER BY g.groupid asc
		     LIMIT 1), p_enrollId, false)),

	    --Caso a matrícula seja de transferência, já não possui mais os registros de degrees, então pega direto da enroll.
	    (SELECT COALESCE(ROUND(finalNote::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::INT)::VARCHAR, concept::VARCHAR)
	       FROM acdEnroll
	      WHERE enrollId = p_enrollId))); 
END; 
$BODY$ 
language plpgsql IMMUTABLE;

