CREATE OR REPLACE FUNCTION obterSemestreDoContratoPelasMovimentacoesDeMatricula(p_contractId INT)
RETURNS TABLE (
    codigo_dos_periodos_letivos INT[],
    quantidade_de_semestres INT
) AS
$BODY$
DECLARE
    v_semestres INT;
    v_periodos INT[];
    v_learningPeriodIds INT;
    v_contrato_ocorrencia RECORD;
    v_max_semestres_curso INT := 0;
BEGIN
    -- Obtém todos os learningperiodids que o contrato possui movimentação de matrícula.
    FOR v_learningPeriodIds IN ( SELECT A.learningPeriodId
			           FROM acdMovementContract A
			          WHERE A.stateContractId = GETPARAMETER('BASIC','STATE_CONTRACT_ID_ENROLLED')::INT
			            AND A.contractId = p_contractId
		               ORDER BY A.statetime ASC )
    LOOP
        v_periodos := array_append(v_periodos, v_learningPeriodIds);
    END LOOP;

    -- Verifica quantidade de movimentações de matrícula que o contrato possui.
    SELECT INTO v_semestres array_length(v_periodos, 1);

    --Obtém objeto do contrato
    SELECT INTO v_contrato_ocorrencia
                courseId,
                courseVersion,
                turnId,
                unitId
           FROM acdContract
          WHERE contractId = p_contractId;

    --Verifica se a modalidade do curso do contrato é por algum dos tipos de seriado.
    IF (SELECT (courseVersionTypeId::TEXT IN (SELECT UNNEST(string_to_array(getParameter('ACADEMIC', 'ACD_COURSE_TYPE_ID_SERIAL'), ','))))
	  FROM acdCourseVersion
	 WHERE (courseId,
		courseVersion) = (v_contrato_ocorrencia.courseId,
                                  v_contrato_ocorrencia.courseVersion))
    THEN
        --Obtém o máximo de semestres definido para a matriz do curso.
        SELECT INTO v_max_semestres_curso 
                    MAX(semester)
               FROM acdCurriculum
              WHERE (courseId,
                     courseVersion,
                     turnId,
                     unitId) = (v_contrato_ocorrencia.courseId,
                                v_contrato_ocorrencia.courseVersion,
                                v_contrato_ocorrencia.turnId,
                                v_contrato_ocorrencia.unitId);

        /**
         * Se a quantidade de semestres obtida pelo contrato for maior que 
         * a quantidade de semestres definida na matriz curricular do curso,
         * deve retornar semestre máximo da matriz.
         */
        IF (v_semestres > v_max_semestres_curso)
        THEN
            v_semestres := v_max_semestres_curso;
        END IF;
    END IF;

    RETURN QUERY (
        SELECT v_periodos AS codigo_dos_periodos_letivos, 
               v_semestres AS quantidade_de_semestres
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
