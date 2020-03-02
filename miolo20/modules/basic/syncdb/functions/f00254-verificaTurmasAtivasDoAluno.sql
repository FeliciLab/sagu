CREATE OR REPLACE FUNCTION verificaTurmasAtivasDoAluno(p_contractId integer)
  RETURNS BOOLEAN AS
$BODY$
/******************************************************************************
  NAME: verificaTurmasAtivasDoAluno
  DESCRIPTION: Função que verifica se o aluno está em mais de uma turma ativa

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       11/08/2014 Nataniel I. da Silva    1. Criado função.
******************************************************************************/
DECLARE
    v_verifica_turmas BOOLEAN;
    v_info_aluno RECORD;
BEGIN
    -- Criado validação para evitar problemas no momento do cálculo do financeiro
    SELECT INTO v_verifica_turmas 
           COUNT(*) > 1
      FROM acdClassPupil A
     WHERE A.contractId = p_contractId
       AND (A.endDate IS NULL OR A.endDate > now()::date);
       
    IF v_verifica_turmas = TRUE THEN
	 SELECT INTO v_info_aluno 
                C.personid, 
                C.name	
	   FROM acdcontract B
INNER JOIN ONLY basphysicalperson C
      	     ON B.personid = C.personid
	  WHERE B.contractId = p_contractId;
	  
	RAISE EXCEPTION 'O aluno % - % contrato % está com mais de uma turma ativa. Para não gerar inconsistência, é necessário ajustar para que ele tenha apenas uma turma ativa.', v_info_aluno.personid, v_info_aluno.name, p_contractId;
	RETURN FALSE;
    END IF;

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
