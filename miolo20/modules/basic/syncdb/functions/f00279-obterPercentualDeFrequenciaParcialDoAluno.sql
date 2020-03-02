CREATE OR REPLACE FUNCTION obterPercentualDeFrequenciaParcialDoAluno(p_enrollId INT)
RETURNS NUMERIC AS
$BODY$
/*************************************************************************************
  NAME: obterPercentualDeFrequenciaParcialDoAluno
  PURPOSE: Calcula e retorna o percentual de frequência parcial do aluno.
           O cálculo não olha para aulas ainda não ministradas, o cálculo somente considera 
           aulas ministradas pelo professor, ou seja, vale como 100%.
           Neste percentual do aluno é cálculado pelo percentual já ministrado como referencia.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_frequenciaParcialDoAluno NUMERIC := 0;
BEGIN
    SELECT INTO v_frequenciaParcialDoAluno (obterMinutosCursadosPeloAlunoNaOferecida(enrollId) * 100) / obterMinutosAulaMinistradosPeloProfessor(groupId)
           FROM acdEnroll
          WHERE enrollId = p_enrollId;
	       
    RETURN v_frequenciaParcialDoAluno;
END;
$BODY$
LANGUAGE plpgsql;
