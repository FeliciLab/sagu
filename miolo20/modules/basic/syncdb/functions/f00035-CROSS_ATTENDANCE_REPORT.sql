CREATE OR REPLACE FUNCTION CROSS_ATTENDANCE_REPORT(
    p_groupId acdGroup.groupId%TYPE, --Código da turma
    p_photoPath varchar, --Diretório das imagens
    p_beginDate date, --Data inicial
    p_endDate date, --Data final
    p_numColumns integer, --Numero de colunas a serem adicionadas
    p_fillData boolean, --Preencher caderno de chamada
    p_showCancelled boolean) --Exibir cancelados
RETURNS TABLE(order_column integer,
              person text,
              ftime text,
              frequency text,
              upload text,
              ordem_aluno bigint)
AS $BODY$
DECLARE
    v_hasTimesAndDates BOOLEAN;
BEGIN
    SELECT COUNT(*) > 0 INTO v_hasTimesAndDates
      FROM acdSchedule
     WHERE groupId = p_groupId
       AND array_length(occurrenceDates,1) > 0
       AND array_length(timeIds,1) > 0
       AND p_fillData;

    RAISE NOTICE '%', CASE WHEN v_hasTimesAndDates THEN 'SIM' ELSE 'NAO' END;

    RETURN QUERY
        SELECT AR.order_column,
               AR.person,
               AR.outData AS ftime,
               AR.frequency,
               AR.upload,
               AR.ordem_aluno
          FROM (
            SELECT A.order_by AS order_column,
                   (CASE
                        WHEN B.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int THEN '* '
                        WHEN B.statusId = getparameter('ACADEMIC', 'ENROLL_STATUS_DESISTING')::int THEN '** '
                        WHEN B.isexploitation IS TRUE THEN '*** '
                    ELSE
                        ''
                    END) || (D.name || ' - ' || D.personId)::text AS person,
                   CASE
                       WHEN A.order_by = 1 THEN (TO_CHAR(A.occurrenceDate, 'mm') || E'\n' || TO_CHAR(A.occurrenceDate, 'dd') || E'\n' || TO_CHAR(A.beginHour, 'HH24:MI') || E'\n' || TO_CHAR(A.endHour, 'HH24:MI'))::text
                       ELSE
                            A.description::text
                   END AS outData,
                   CASE
                       WHEN A.order_by = 1
                       THEN
                            E.frequency::text
                       ELSE
                            CASE WHEN A.order_by = 3
                            THEN
                                    ( CASE WHEN B.frequency > H.academicNumberHours
                                    THEN
                                       '100'::text
                                    ELSE
                                        ROUND( ((B.frequency * 100) / H.academicNumberHours)::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::int)::text
                                    END )
                            ELSE
                                    COALESCE(ROUND(F.note::numeric, getParameter('BASIC', 'GRADE_ROUND_VALUE')::int)::text, ( SELECT COALESCE(subtitle, description)
                                                                                                                                FROM acdConcept
                                                                                                                               WHERE description = F.concept::text ))

                            END
                   END AS frequency,
                   (SELECT filepath || fileid::text FROM basfile WHERE fileid = D.photoId)::text AS upload,
                   (dense_rank() over (order by (CASE
                        WHEN B.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int THEN '* '
                        WHEN B.statusId = getparameter('ACADEMIC', 'ENROLL_STATUS_DESISTING')::int THEN '** '
                        WHEN B.isexploitation IS TRUE THEN '*** '
                    ELSE
                        ''
                    END) || (D.name || ' - ' || D.personId)::text)) AS ordem_aluno
              FROM (
                    SELECT 1 AS order_by,
                             A.groupId,
                             B.occurrenceDate,
                             C.timeId,
                             D.beginHour,
                             D.endHour,
                             NULL AS degreeId,
                             NULL AS description,
                             NULL AS parentDegreeId,
                             NULL AS degreeNumber
                      FROM acdGroup A
                INNER JOIN (SELECT A.groupId, UNNEST(A.occurrenceDates) AS occurrenceDate, A.scheduleID as scheduleID
                              FROM acdSchedule A
                             WHERE A.groupId = p_groupId) B
                        ON B.groupId = A.groupId
                INNER JOIN (SELECT A.groupId, UNNEST(A.timeIds) AS timeId, A.scheduleId
                              FROM acdSchedule A
                             WHERE A.groupId = p_groupId) C
                        ON (C.groupId = A.groupId
                            AND C.scheduleId = B.scheduleId)
                INNER JOIN acdTime D
                        ON D.timeId = C.timeId
                     WHERE A.groupId = p_groupId
                       --Filtra o intervalo de datas
                       AND ( CASE WHEN p_beginDate IS NOT NULL AND p_endDate IS NOT NULL
                            THEN
                                    --Data inicial e final
                                    B.occurrenceDate BETWEEN p_beginDate AND p_endDate
                            ELSE
                                    ( CASE WHEN p_beginDate IS NOT NULL
                                    THEN
                                            --Data inicial
                                            B.occurrenceDate >= p_beginDate
                                    ELSE
                                            ( CASE WHEN p_endDate IS NOT NULL
                                            THEN
                                                    --Data final
                                                    B.occurrenceDate <= p_endDate
                                            ELSE
                                                    TRUE
                                            END )
                                    END )
                            END)
                       AND p_fillData
                 UNION ALL
                    -- se nao ha horarios, instanciar dias vazios
                    SELECT generate_series(-p_numColumns, CASE WHEN v_hasTimesAndDates THEN NULL ELSE -1 END, 1) AS order_by, p_groupId, NULL, NULL, NULL, NULL, NULL::integer AS degreeId, NULL AS description, NULL::integer AS parentDegreeId, NULL::integer AS degreeNumber
                 UNION ALL
                    SELECT (4+row_number() OVER (ORDER BY COALESCE(A.parentDegreeId, 0) DESC, A.degreeNumber))::integer AS order_by, B.groupId, NULL, NULL, NULL, NULL, A.degreeId, A.description, A.parentDegreeId, A.degreeNumber
                      FROM acdDegree A
                INNER JOIN acdGroup B
                        ON B.learningPeriodId = A.learningPeriodId
                 WHERE B.groupId = p_groupId

                 UNION ALL
                 --Adiciona coluna da frequencia
                 SELECT 3, p_groupId, NULL, NULL, NULL, NULL, NULL::integer AS degreeId, 'FREQ (%)' AS description, NULL::integer AS parentDegreeId, NULL::integer AS degreeNumber
                 ) A

             -- obter lista de pessoas
             LEFT JOIN acdEnroll B
                    ON B.groupId = A.groupId

                    -- Pega apenas a ULTIMA matricula, ordenando pela data de matricula
                    AND (
                        B.enrollId = (
                            SELECT EE.enrollId
                              FROM acdEnroll EE
                             WHERE EE.contractId = B.contractId
                               AND EE.groupId = B.groupId

                                -- Exibe ou nao os cancelados
                                AND (CASE WHEN
                                         p_showCancelled IS FALSE
                                     THEN
                                         EE.statusId != getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int
                                     ELSE
                                         TRUE
                                     END)

                          ORDER BY EE.dateEnroll::varchar || EE.hourEnroll::varchar DESC
                             LIMIT 1
                        )
                    )

            INNER JOIN acdContract C
                    ON C.contractId = B.contractId
            INNER JOIN ONLY basPhysicalPerson D
                    ON D.personId = C.personId
            INNER JOIN unit_acdLearningPeriod LP
                    ON LP.learningPeriodId = ( SELECT learningPeriodId
                                                 FROM unit_acdGroup
                                                WHERE groupId = p_groupId )
             -- obter frequencias de cada pessoa
             LEFT JOIN acdFrequenceEnroll E
                    ON E.enrollId = B.enrollId
                   AND E.frequencyDate = A.occurrenceDate
                   AND E.timeId = A.timeId
                   AND p_fillData
             -- obter notas de cada pessoa
             LEFT JOIN acdDegreeEnroll F
                    ON F.enrollId = B.enrollId
                   AND F.degreeId = A.degreeId
                   AND p_fillData
                   AND F.recorddate = (SELECT MAX(G.recorddate) FROM acdDegreeEnroll G WHERE G.enrollId=F.enrollId AND G.degreeId=F.degreeId)--Adicionar somente a ultima ocorrencia de nota da pessoa
             LEFT JOIN acdCurriculum G
                    ON G.curriculumId = ( SELECT curriculumId
                                            FROM acdGroup
                                           WHERE groupid = p_groupId )
                    AND p_fillData
             LEFT JOIN acdCurricularComponent H
                    ON (H.curricularComponentId = G.curricularComponentId AND
                        H.curricularComponentVersion = G.curricularComponentVersion)
                    AND p_fillData
                  WHERE B.statusId <> GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::int
                    AND verificaPrimeiraParcela(B.enrollId, LP.periodId)
          ORDER BY (CASE WHEN B.statusId = getParameter('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int THEN 1 ELSE 0 END), (D.name || ' - ' || D.personId)::TEXT, A.occurrenceDate, A.beginHour, A.endHour, A.order_by) AR;
END;
$BODY$
LANGUAGE 'plpgsql';

