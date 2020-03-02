CREATE OR REPLACE FUNCTION obterPercentualDeFrequenciaRealDoAluno(p_enrollId INT)
RETURNS NUMERIC AS
$BODY$
/*************************************************************************************
  NAME: obterPercentualDeFrequenciaParcialDoAluno
  PURPOSE: Calcula e retorna o percentual de frequência real do aluno.
           O cálculo verifica o total já cursado pelo aluno e referencia,
           pelas horas aulas cadastradas na disciplina. (Cálculo comum)
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_frequenciaRealDoAluno NUMERIC := 0;
BEGIN
    SELECT INTO v_frequenciaRealDoAluno ((obterMinutosCursadosPeloAlunoNaOferecida(A.enrollId) / 60) * 100) / D.lessonNumberHours
           FROM acdEnroll A
     INNER JOIN acdGroup B
	  USING (groupId)
     INNER JOIN acdCurriculum C
	     ON C.curriculumId = B.curriculumId
     INNER JOIN acdCurricularComponent D
	     ON ( D.curricularComponentId,
		  D.curricularComponentVersion ) = ( C.curricularComponentId,
						     C.curricularComponentVersion )
          WHERE A.enrollId = p_enrollId;
	       
    RETURN v_frequenciaRealDoAluno;
END;
$BODY$
LANGUAGE plpgsql;
