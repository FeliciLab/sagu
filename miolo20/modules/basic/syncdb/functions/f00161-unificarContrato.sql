CREATE OR REPLACE FUNCTION unificarContrato(p_deContrato INT, p_paraContrato INT) 
RETURNS boolean AS $$
/*************************************************************************************
  NAME: unificarContrato
  PURPOSE: Processo de unificação de contratos.
**************************************************************************************/
DECLARE
    v_deContratoDados RECORD;
    v_paraContratoDados RECORD;

    v_deEnrolls RECORD;
    v_paraEnrolls RECORD;

    v_matriculado INT := getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT;
    v_aprovado INT := getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED')::INT;
    v_spc_movement BOOLEAN;
    v_titulo_aberto RECORD;
    v_deSemestreContrato RECORD;
    v_disciplina TEXT;
BEGIN
    SELECT INTO v_deContratoDados
                *
           FROM acdContract
          WHERE contractId = p_deContrato;

    SELECT INTO v_paraContratoDados
                *
           FROM acdContract
          WHERE contractId = p_paraContrato;

    -- Valida se os dois contratos são da mesma ocorrência de curso.
    IF ( v_deContratoDados.courseid = v_paraContratoDados.courseid AND
         v_deContratoDados.courseversion = v_paraContratoDados.courseversion AND
         v_deContratoDados.turnid = v_paraContratoDados.turnid AND
         v_deContratoDados.unitid = v_paraContratoDados.unitid )
    THEN
        -- Valida se existe a mesma disciplina com status de matriculado/aprovado nas matrículas.
        FOR v_deEnrolls IN
                ( SELECT *
                    FROM acdEnroll
                   WHERE contractId = p_deContrato )
        LOOP
            FOR v_paraEnrolls IN 
                    ( SELECT *
                        FROM acdEnroll
                       WHERE contractId = p_paraContrato )
            LOOP
                IF v_deEnrolls.groupid = v_paraEnrolls.groupid
                THEN
                    IF ( v_deEnrolls.statusid = v_matriculado AND
                         v_paraEnrolls.statusid = v_matriculado )
                    OR ( v_deEnrolls.statusid = v_aprovado AND
                         v_paraEnrolls.statusid = v_aprovado )
                    OR ( v_deEnrolls.statusid = v_aprovado AND
                         v_paraEnrolls.statusid = v_matriculado )
                    OR ( v_deEnrolls.statusid = v_matriculado AND
                         v_paraEnrolls.statusid = v_aprovado )
                    THEN
                        SELECT INTO v_disciplina getcurricularcomponentname(curricularcomponentid)
                               FROM acdcurriculum
                              WHERE curriculumid = v_deEnrolls.curriculumid;
                        RAISE EXCEPTION 'Disciplina duplicada:  %', v_disciplina;
                    END IF;
                END IF;
            END LOOP;
        END LOOP;
        
        SELECT INTO v_spc_movement COUNT(*) > 0 FROM finspcmovement  WHERE  personid = v_deContratoDados.personid;
        IF v_spc_movement = TRUE 
        THEN
            SELECT INTO v_titulo_aberto invoiceid FROM finspcmovement  WHERE  personid = v_deContratoDados.personid;
            RAISE EXCEPTION 'O processo de unificação não pode ser concluído, pois a pessoa % do contrato % está com registro no spc referente ao título %.', v_deContratoDados.personid, p_deContrato, v_titulo_aberto.invoiceid;
        END IF;
        
        FOR v_deSemestreContrato IN ( SELECT periodid, COUNT(contractid)  
                                        FROM acdsemestercontractperiod 
                                       WHERE contractid = p_deContrato OR contractid = p_paraContrato
                                    GROUP BY periodid 
                                      HAVING COUNT(contractid) > 1 )

        LOOP
            DELETE FROM acdsemestercontractperiod WHERE contractid = p_deContrato AND periodid = v_deSemestreContrato.periodid;
        END LOOP;

        -- Executa o processo de unificação dos contratos.
        EXECUTE 'SELECT uniao_de_registros(''acdcontract'', ''' || p_deContrato || ''', ''' || p_paraContrato || ''')';		
    ELSE
        RAISE EXCEPTION 'Os contratos precisam pertencer a mesma ocorrência de curso.';
    END IF;

    RETURN TRUE;
END;
$$
LANGUAGE 'plpgsql';
--
