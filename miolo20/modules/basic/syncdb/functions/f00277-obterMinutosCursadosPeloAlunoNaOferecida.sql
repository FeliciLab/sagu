CREATE OR REPLACE FUNCTION obterMinutosCursadosPeloAlunoNaOferecida(p_enrollId INT)
RETURNS INT AS
$BODY$
/*************************************************************************************
  NAME: obterMinutosCursadosPeloAlunoNaOferecida
  PURPOSE: Retorna o total de minutos cursados pelo aluno em uma determinada disciplina oferecida
           Para converter em horas, dividir o resultado da função por 60.
           Para obter a sobra de minutos ministrados em horas, % 60.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_minutosCursadosPeloAluno INT := 0;
BEGIN
    SELECT INTO v_minutosCursadosPeloAluno SUM(Y.minutosCursadosPeloAluno)
      FROM ( SELECT ( CASE WHEN A.frequency = 0.5 
			   THEN (split_part((EXTRACT(EPOCH FROM B.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT / 2 )
			   ELSE split_part((EXTRACT(EPOCH FROM B.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT
		      END ) AS minutosCursadosPeloAluno,
		    E.groupId
	       FROM acdfrequenceenroll A
	 INNER JOIN acdEnroll E
	      USING (enrollId)
         INNER JOIN acdTime B
	      USING (timeId)
	      WHERE A.enrollid = p_enrollId
	        AND A.frequency <> 0 ) Y;
	       
    RETURN v_minutosCursadosPeloAluno;
END;
$BODY$
LANGUAGE plpgsql;
