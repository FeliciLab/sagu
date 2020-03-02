CREATE OR REPLACE FUNCTION obterPercentualDeFrequenciaPrevistaDoAluno(p_enrollId INT)
RETURNS NUMERIC AS 
$BODY$
/*************************************************************************************
  NAME: obterPercentualDeFrequenciaPrevistaDoAluno
  PURPOSE: Calcula e retorna o percentual de frequência prevista do aluno.
           O cálculo considera as aulas alocadas ainda não ministradas como presença.
           Caso o aluno receba alguma falta ou meia falta em uma aula, seu percentual
           previsto é diminuido.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_frequenciaPrevistaDoAluno NUMERIC := 0;
BEGIN
    -- Subtrai as horas disperdiçadas pelo aluno nas horas totais alocadas, e divide pelas horas aula cadastradas para obter o percentual.
    SELECT INTO v_frequenciaPrevistaDoAluno ((Z.horasTotaisAlocadasNaOferecida - Z.horasDisperdicadasDoAluno) / Z.lessonNumberHours) * 100

           -- Obtém as horas alocadas na oferecida, horas disperdiçadas pelo aluno (faltas) e as horas aula cadastradas para a disciplina.
	   FROM ( SELECT (obterTotalDeMinutosAlocadosNaDisciplinaOferecida(E.groupId) / 60) AS horasTotaisAlocadasNaOferecida,
		         COALESCE((SUM(X.minutosportimeid) / 60), 0) AS horasDisperdicadasDoAluno,
		         CC.lessonNumberHours
		         
		    FROM acdEnroll E
	      INNER JOIN acdGroup G
		      ON G.groupId = E.groupId
	      INNER JOIN acdCurriculum C
		      ON C.curriculumId = G.curriculumId
	      INNER JOIN acdCurricularComponent CC
		      ON ( CC.curricularComponentId,
			   CC.curricularComponentVersion ) = ( C.curricularComponentId,
							       C.curricularComponentVersion )
	       -- Obtém o total de horas disperdiçadas pelo aluno na disciplina oferecida (faltas).
	       LEFT JOIN ( SELECT A.frequencyDate,
				  A.timeId,
				  A.frequency,
				  ( CASE WHEN A.frequency = 0.5 
				         THEN (split_part((EXTRACT(EPOCH FROM B.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT / 2 )
				         ELSE split_part((EXTRACT(EPOCH FROM B.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT
				     END ) AS minutosportimeid,
				  A.enrollId
			     FROM acdfrequenceenroll A
		       INNER JOIN acdTime B
			    USING (timeId)
			    WHERE A.enrollid = p_enrollId
			      AND A.frequency <> 1
		         ORDER BY 1, 2 ) X
		      ON X.enrollId = E.enrollId

		   WHERE E.enrollId = p_enrollId
	        GROUP BY E.groupId, CC.lessonNumberHours ) Z;

    RETURN v_frequenciaPrevistaDoAluno;
END;
$BODY$
LANGUAGE plpgsql;
