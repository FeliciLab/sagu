CREATE OR REPLACE FUNCTION transitaPreMatriculadosParaMatriculados()
RETURNS BOOLEAN AS
$BODY$
/*************************************************************************************
  NAME: transitaPreMatriculadosParaMatriculados
  PURPOSE: Função criada para corrigir problemas que ocorrem de alunos que pagaram suas primeiras parcelas 
  e não trocaram de pré-matriculados para matriculados.

  REVISIONS:
  Ver       Date          Author               Description
  --------- ------------- -------------------- ------------------------------------
  1.0       09/05/2014    Augusto A. Silva     1. Função criada.
**************************************************************************************/
DECLARE
    V_PRE_MATRICULADOS RECORD;
  V_CONFIGURACAO_MATRICULA RECORD;
  V_ISFRESHMAN BOOLEAN;
BEGIN
    -- Obtém todos alunos pré-matriculados que já pagaram a primeira parcela.
    FOR V_PRE_MATRICULADOS IN
        ( SELECT A.personId,
                         A.contractId,
                         B.learningPeriodId,
                         ( CASE getContractState(A.contractId)
                                WHEN getParameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INT 
                                THEN
                                     TRUE
                                ELSE
                                     FALSE
                           END ) AS stateContractEnrolled,
                         A.courseId,
                         A.courseVersion,
                         A.turnId,
                         A.unitId
                    FROM acdContract A
              INNER JOIN acdlearningperiod B
                      ON ( B.courseid,
                           B.courseVersion,
                           B.turnid,
                           B.unitId ) = ( A.courseId,
                                          A.courseVersion,
                                          A.turnid,
                                          A.unitId )
                   WHERE verificaseprimeiraparcelarealmentefoipaga(A.contractId, B.learningPeriodId)
                     AND B.periodId = getparameter('BASIC', 'CURRENT_PERIOD_ID')
                     AND ( SELECT COUNT(enrollid) > 0
                             FROM acdEnroll
                            WHERE statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::INT
                              AND contractId = A.contractId  ) )
    LOOP

    SELECT INTO V_CONFIGURACAO_MATRICULA * FROM acdenrollconfig 
            WHERE courseId = V_PRE_MATRICULADOS.courseId
              AND courseVersion = V_PRE_MATRICULADOS.courseVersion 
              AND turnId = V_PRE_MATRICULADOS.turnId 
              AND unitId = V_PRE_MATRICULADOS.unitId
              AND NOW()::DATE >= begindate 
              AND NOW()::DATE <= ( CASE WHEN enddate IS NOT NULL 
                                        THEN enddate 
                                        ELSE NOW()::DATE 
                                         END );

    IF V_CONFIGURACAO_MATRICULA.enrollconfigid IS NULL THEN
    BEGIN
        SELECT INTO V_CONFIGURACAO_MATRICULA * FROM acdenrollconfig 
         WHERE NOW()::DATE >= begindate 
           AND NOW()::DATE <= ( CASE WHEN enddate IS NOT NULL 
                                     THEN enddate 
                                     ELSE NOW()::DATE 
                                      END );

         IF V_CONFIGURACAO_MATRICULA.enrollconfigid IS NULL THEN
             CONTINUE;
         END IF;
    END;
    END IF;

    IF ( V_CONFIGURACAO_MATRICULA.enablepreenroll IS FALSE ) THEN
        CONTINUE;
    END IF;

    -- Aceita somente configuração por pagamento da primeira parcela.
    IF ( V_CONFIGURACAO_MATRICULA.preenrollchecksignature != 'N' OR V_CONFIGURACAO_MATRICULA.preenrollwebconfirmation != 'N' OR V_CONFIGURACAO_MATRICULA.preEnrollCheckFirstPayment = 'N' OR V_CONFIGURACAO_MATRICULA.preMatriculaChecaClassificacao = 'A' ) THEN
        CONTINUE;
    END IF;

    IF V_CONFIGURACAO_MATRICULA.preEnrollCheckFirstPayment != 'A' THEN
    BEGIN
        SELECT INTO V_ISFRESHMAN isFreshManByPeriod(V_PRE_MATRICULADOS.contractId, (SELECT periodId
                                                                                      FROM acdLearningPeriod
                                                                                     WHERE learningPeriodId = V_PRE_MATRICULADOS.learningPeriodId)::VARCHAR );

        IF V_ISFRESHMAN THEN
        BEGIN
            IF V_CONFIGURACAO_MATRICULA.preEnrollCheckFirstPayment != 'C' THEN
                CONTINUE;
            END IF;
        END;
        ELSE
            IF V_CONFIGURACAO_MATRICULA.preEnrollCheckFirstPayment != 'V' THEN
                CONTINUE;
            END IF;
        END IF;
    END;
    END IF;

    -- Verifica se o aluno é calouro
    SELECT INTO V_ISFRESHMAN isFreshManByPeriod(V_PRE_MATRICULADOS.contractId, (SELECT periodId
                                                                                  FROM acdLearningPeriod
                                                                                 WHERE learningPeriodId = V_PRE_MATRICULADOS.learningPeriodId)::VARCHAR );

    -- Se a configuração de classificação estiver habilitada pra calouros
    IF V_CONFIGURACAO_MATRICULA.preMatriculaChecaClassificacao = 'C' AND V_ISFRESHMAN IS TRUE THEN
        CONTINUE;
    -- Se a configuração de classificação estiver habilitada para veteranos
    ELSIF V_CONFIGURACAO_MATRICULA.preMatriculaChecaClassificacao = 'V' AND V_ISFRESHMAN IS FALSE THEN
        CONTINUE;
    END IF;
    
        -- Atualiza as matrículas do aluno para MATRICULADO.
        UPDATE acdEnroll
           SET statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_ENROLLED')::INT
         WHERE enrollId IN ( SELECT A.enrollId
                       FROM acdEnroll A
                 INNER JOIN acdGroup B
                     ON B.groupId = A.groupId
                 INNER JOIN acdLearningPeriod C
                     ON C.learningPeriodId = B.learningPeriodId
                      WHERE A.contractId = V_PRE_MATRICULADOS.contractId
                    AND C.learningPeriodId = V_PRE_MATRICULADOS.learningPeriodId );

                IF ( V_PRE_MATRICULADOS.stateContractEnrolled IS FALSE )
                THEN
                    -- Insere movimentação contratual de MATRÍCULA para o aluno.
                    INSERT INTO acdMovementContract
                                ( contractId,
                                  stateContractId,
                                  statetime,
                                  learningPeriodId )
                         VALUES ( V_PRE_MATRICULADOS.contractId,
                                  getParameter('BASIC', 'STATE_CONTRACT_ID_ENROLLED')::INT,
                                  TO_CHAR(NOW(), getparameter('BASIC', 'MASK_TIMESTAMP'))::TIMESTAMP,
                                  V_PRE_MATRICULADOS.learningPeriodId );
                END IF;

        RAISE NOTICE 'Aluno % do contrato %, MATRICULADO.', V_PRE_MATRICULADOS.personId, V_PRE_MATRICULADOS.contractId;
    END LOOP;

    RETURN TRUE;
END; 
$BODY$
LANGUAGE plpgsql;
