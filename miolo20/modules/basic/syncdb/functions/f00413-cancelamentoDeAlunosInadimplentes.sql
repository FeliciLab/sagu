CREATE OR REPLACE FUNCTION cancelamentoDeAlunosInadimplentes()
RETURNS BOOLEAN AS
$BODY$
/******************************************************************************
  NAME: cancelamentoDeAlunosInadimplentes
  DESCRIPTION: Cancela títulos, disciplinas e insere movimentação contratual 
  para todos os alunos que estão e inadimplêntes. Lógica migrada do PHP para
  SQL. Ticket #38165.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       03/06/2015 Luís F. Wermann       1. Função criada.
******************************************************************************/
DECLARE
    --Contratos
    v_contratos RECORD;

    --Código da configuração de matrícula
    v_enrollConfigId INT;

    --Config. que diz se pré-matriculados consomem vaga
    v_preEnrollConsumeVacant BOOLEAN;

    --Disciplinas pré-matriculadas do contrato
    v_disciplinas RECORD;

    --Títulos da matrícula
    v_titulos RECORD;
BEGIN

    --Obter contratos inadimplentes
    FOR v_contratos IN ( SELECT DISTINCT 
                                B.contractId,
                                B.courseId,
                                B.courseVersion,
                                B.turnId,
                                B.unitId        
		           FROM acdContract B 
                          WHERE isDefaulter(B.personId) )
    LOOP

        --Obter código da configuração de matrícula da ocorrência de curso do contrato
        v_enrollConfigId := (SELECT enrollConfigId
                               FROM acdEnrollConfig
                              WHERE courseId = v_contratos.courseId
                                AND courseVersion = v_contratos.courseVersion
                                AND turnId = v_contratos.turnId
                                AND unitId = v_contratos.unitId
                                AND ((NOW()::DATE >= begindate AND NOW()::DATE <= (CASE WHEN enddate is not null 
                                                                                          THEN 
                                                                                               enddate 
                                                                                          ELSE NOW()::DATE 
                                                                                     END)) OR (beginDate IS NULL AND endDate IS NULL)));

        --Caso não achar do curso, procura uma geral
        IF ( v_enrollConfigId IS NULL )
        THEN
            v_enrollConfigId := (SELECT enrollConfigId
                                   FROM acdEnrollConfig
                                  WHERE ((NOW()::DATE >= begindate AND NOW()::DATE <= (CASE WHEN enddate is not null 
                                                                                              THEN 
                                                                                                  enddate 
                                                                                              ELSE NOW()::DATE 
                                                                                         END)) OR (beginDate IS NULL AND endDate IS NULL))
                                    AND courseId IS NULL
                                    AND courseVersion IS NULL
                                    AND turnId IS NULL
                                    AND unitIt IS NULL);
        END IF;

        IF ( v_enrollConfigId IS NULL )
        THEN
            RAISE EXCEPTION 'Nenhuma configuração de matrícula vigente foi encontrada. Para realizar essa tarefa, cadastre uma configuração de matrícula em Acadêmico::Configuração::Configuração de matrícula.';
        END IF;

        --Verifica se pré-matriculados consomem vaga
        v_preEnrollConsumeVacant := (SELECT preEnrollConsumeVacant
                                       FROM acdEnrollConfig
                                      WHERE enrollConfigid = v_enrollConfigId);

        --Disciplinas pré-matriculadas do contrato
        FOR v_disciplinas IN (SELECT A.enrollId,
                                     A.groupId,
				     B.learningPeriodId
                                FROM acdenroll A
                          INNER JOIN acdGroup B
                                  ON (A.groupId= B.groupId)
                          INNER JOIN acdLearningPeriod C
                                  ON (B.learningPeriodId = C.learningPeriodId)
                               WHERE A.contractId = v_contratos.contractId
                       AND (CASE WHEN v_preEnrollConsumeVacant IS TRUE 
                                 THEN 
                                     A.statusId IN (getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT, 
                                                    getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT)
                                 ELSE
                                     A.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT
                            END)
                       AND getContractState(A.contractId) = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_PRE_ENROLL')::INT)
        LOOP

            --Cancelar todos os títulos da matrícula
            FOR v_titulos IN (SELECT obterTitulosDaMatricula(v_contratos.contractId, v_disciplinas.learningPeriodId) AS invoiceId)
            LOOP
            
	        UPDATE finInvoice SET iscanceled = TRUE WHERE invoiceId = v_titulos.invoiceId;

            END LOOP;
        
            --Cancelar matrícula
            UPDATE acdEnroll SET statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::INTEGER,
                                 reasonCancellationId = getParameter('ACADEMIC', 'MOTIVO_CANCELAMENTO_MATRICULA_DE_ALUNOS_INADIMPLENTES')::INTEGER
                           WHERE enrollId = v_disciplinas.enrollId;

        END LOOP;
        
    END LOOP;

    RETURN TRUE;
    
END;
$BODY$
LANGUAGE plpgsql;