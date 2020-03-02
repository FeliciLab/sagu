CREATE OR REPLACE FUNCTION chk_unique_course_occurrence(p_personid bigint, p_courseid character varying, p_courseversion integer, p_unitid integer, p_turnid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: chk_unique_course_occurrence
  PURPOSE: Verifica se tem um contrato com o mesmo personId, courseId, courseVersion,
  unitId, turnId e se esté fechado.
  DESCRIPTION:

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/11/2010 Alexandre Schmidt 1. FUNÇÃO criada.
  1.1       23/12/2010 Arthur Lehdermann 1. Alterado texto do alerta.
  1.2       22/06/2011 Alexandre Schmidt 1. Corrigido bug que impedia alteraçães em
                                            contratos jé desativados.
**************************************************************************************/
DECLARE
    v_count integer;
BEGIN
    SELECT COUNT(*) INTO v_count
      FROM acdContract
     WHERE courseId = p_courseId
       AND courseVersion = p_courseVersion
       AND turnId = p_turnId
       AND unitId = p_unitId
       AND personId = p_personId
       AND getContractState(contractId) IN (SELECT stateContractId
                                              FROM acdStateContract
                                             WHERE inOutTransition != 'O');

    IF ( v_count > 1 ) THEN
        RAISE NOTICE 'Pessoa % possui mais de um contrato para o curso %/%, unidade %, turno %.', p_personId, p_courseId, p_courseVersion, p_unitId, p_turnId;
    END IF;

    RETURN (v_count < 2);

END;
$$;
