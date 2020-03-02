CREATE OR REPLACE FUNCTION generateAcademicCalendar(p_begindate date, p_enddate date, p_opts character varying[], p_values character varying[]) RETURNS TABLE(referencedate date, dayevents text)
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: generateAcademicCalendar
  PURPOSE: Gera a lista de atividades em cada dia do peréodo especificado, conforme os
           parémetros.
  DESCRIPTION: Lista de parémetros posséveis e suas funçães:
    - separator Indicando o separador de eventos. Padréo: <br />;

    - courseId Filtra por curso
    - courseVersion Filtra por versão de curso
    - turnId Filtra por turno
    - unitId Filtra por unidade

    - showCurricularComponents Indica se deve retornar as oferecidas do dia.
    - showEvents Indica se deve retornar eventos que ocorrem no dia.
    - showEnrollPeriods Indica se deve exibir períodos de matrícula.
    - showAcademicCalendar Indica se deve exibir eventos do calendário acadêmico.
    - showResidencyPreceptor Indica se deve exibir unidades temáticas da pessoa.
    - showResidencyOfUser Indica se deve exibir unidades tematicas em que pessoa é residente.
    - showScheduledActivity Indica se deve exibir as atividades programadas.

    - personId Filtra pela pessoa, trazendo apenas atividades nas quais a pessoa esté envolvida.

  USAGE:

    SELECT *
      FROM generateAcademicCalendar(
              '2010-01-01', -- beginDate
              '2010-12-31', -- endDate
              ARRAY['separator', 'showCurricularComponents', 'showEvents', 'showAcademicCalendar', 'showEnrollPeriods', 'courseId', 'personId'],
              ARRAY['<br />',    'f',                        't',          't',                    'f',                 'ENF01N',   '4130']
           );

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       20/05/2011 Alex Smith        1. FUNÇÃO criada.
  1.1       26/05/2011 Alex Smith        1. FUNÇÃO alterada para trazer também o(s)
                                            professor(es) de cada disciplina.
                                         2. Alterada checagem de parémetros, para
                                            permitir que não seja informada nenhuma
                                            opção.
  1.2       25/06/2011 Alex Smith        1. Possibilidade de filtrar por pessoa.
  1.3       20/07/2011 Moises Heberle    1. Adicionado showResidencyPreceptor e showResidencyOfUser.
  1.4       01/09/2011 Arthur Lehderman  1. Adicionado showScheduledActivity.
  1.5       29/11/2011 Moises Heberle    1. Estava obtendo sempre uma data a mais pois
                                             somava endDate+1 no GENERATE_SERIES() da tabela
                                             acdEvent. Removido o "+1" para pegar corretamente.
**************************************************************************************/
DECLARE
    v_sql text;
    v_where text;

    v_index integer;

    -- Opcao 'separator', indicando o separador de eventos
    v_separator text;

    -- Opcao 'courseId', especificando filtro por curso
    v_courseId varchar;
    -- Opcao 'courseVersion', especificando filtro por versao de curso
    v_courseVersion varchar;
    -- Opcao 'turnId', especificando filtro por turno
    v_turnId varchar;
    -- Opcao 'unitId', especificando filtro por unidade
    v_unitId varchar;

    -- Opao para exibir ou nao disciplinas que ocorrem na data
    v_showCurricularComponents boolean;
    -- Opcao para exibir ou nao registros de eventos (acdEvent)
    v_showEvents boolean;
    -- Opcao para exibir ou nao periodos de matricula (acdPeriodEnrollDate)
    v_showEnrollPeriods boolean;
    -- Opcao para exibir ou nao calendario academico (acdAcademicCalendar)
    v_showAcademicCalendar boolean;
    -- Opcao para exibir ou nao unidades temáticas da pessoa.
    v_showResidencyPreceptor boolean;
    -- Opcao para exibir ou nao unidades tematicas em que pessoa é residente.
    v_showResidencyOfUser boolean;
    -- Opcao para exibir ou nao as atividades programadas
    v_showScheduledActivity boolean;

    -- Opcao 'personId', especificando a pessoa cuja agenda seré montada
    v_personId bigint;

BEGIN
    -- abortar em caso de erro na especificacao de parametros
    IF p_opts IS NOT NULL OR p_values IS NOT NULL THEN
        IF COALESCE(array_length(p_opts, 1) != array_length(p_values, 1), TRUE) THEN
            RAISE EXCEPTION 'Quantidade de opçães e valores incompatéveis.';
        END IF;
    END IF;

    -- inicializar opcoes

    -- separador
    v_separator := '<br />';
    -- ocorrencia de curso
    v_courseId := NULL;
    v_courseVersion := NULL;
    v_turnId := NULL;
    v_unitId := NULL;
    -- o que sera listado
    v_showCurricularComponents := TRUE;
    v_showEvents := TRUE;
    v_showEnrollPeriods := TRUE;
    v_showAcademicCalendar := TRUE;
    v_showResidencyPreceptor := TRUE;
    v_showResidencyOfUser := TRUE;
    v_showScheduledActivity := TRUE;

    -- personId
    v_personId := NULL;

    -- processar opcoes
    v_index := 1;
    WHILE v_index <= array_length(p_opts, 1) LOOP
        CASE p_opts[v_index]
            WHEN 'separator' THEN
                v_separator := p_values[v_index];
            WHEN 'courseId' THEN
                v_courseId := p_values[v_index];
            WHEN 'courseVersion' THEN
                v_courseVersion := p_values[v_index];
            WHEN 'turnId' THEN
                v_turnId := p_values[v_index];
            WHEN 'unitId' THEN
                v_unitId := p_values[v_index];
            WHEN 'showCurricularComponents' THEN
                v_showCurricularComponents := p_values[v_index] = 't';
            WHEN 'showEvents' THEN
                v_showEvents := p_values[v_index] = 't';
            WHEN 'showEnrollPeriods' THEN
                v_showEnrollPeriods := p_values[v_index] = 't';
            WHEN 'showAcademicCalendar' THEN
                v_showAcademicCalendar := p_values[v_index] = 't';
            WHEN 'showResidencyPreceptor' THEN
                v_showResidencyPreceptor := p_values[v_index] = 't';
            WHEN 'showResidencyOfUser' THEN
                v_showResidencyOfUser := p_values[v_index] = 't';
            WHEN 'showScheduledActivity' THEN
                v_showScheduledActivity := p_values[v_index] = 't';

            WHEN 'personId' THEN
                v_personId := p_values[v_index]::bigint;
            ELSE
                RAISE EXCEPTION 'Opção não reconhecida: %', p_opts[v_index];
        END CASE;
        v_index := v_index + 1;
    END LOOP;

    -- gera a lista de dias que serao pesquisados
    v_sql := 'SELECT A.referenceDate::date,
                     ARRAY_TO_STRING(ARRAY_AGG(B.eventDescription), ''' || v_separator || ''')::text AS dayEvents
                FROM (SELECT GENERATE_SERIES(''' || p_beginDate || '''::date,
                                             ''' || p_endDate || '''::date+1,
                                             ''1 day'') AS referenceDate) A
          INNER JOIN (';

    -- se configurado para exibir disciplinas
    IF v_showCurricularComponents THEN
        v_sql := v_sql || '
                      -- Disciplinas oferecidas por data
                      SELECT DISTINCT UNNEST(D.occurrenceDates) AS occurrenceDate,
                             1 AS appearanceOrder,
                             -- obtem dados do curso
                             B.courseId || ''/'' || B.courseVersion || '' '' || E.shortName || '' - '' ||
                             -- obtem dados da disciplina
                             A.curricularComponentId || ''/'' || A.curricularComponentVersion || '' '' || A.shortName ||
                             -- obtem professores separados por virgula
                             COALESCE((SELECT '' ('' || ARRAY_TO_STRING(ARRAY_AGG(M.name), '', '') || '')''
                                         FROM (SELECT DISTINCT R.name
                                                 FROM acdSchedule P
                                           INNER JOIN acdScheduleProfessor Q
                                                   ON Q.scheduleId = P.scheduleId
                                           INNER JOIN ONLY basPerson R
                                                   ON R.personId = Q.professorId
                                                WHERE P.groupId = C.groupId
                                             ORDER BY R.name) M), '''')
                             AS eventDescription
                        FROM acdCurricularComponent A
                  INNER JOIN acdCurriculum B
                          ON B.curricularComponentId = A.curricularComponentId
                         AND B.curricularComponentVersion = A.curricularComponentVersion
                  INNER JOIN acdGroup C
                          ON C.curriculumId = B.curriculumId
                  INNER JOIN acdSchedule D
                          ON D.groupId = C.groupId
                  INNER JOIN acdCourse E
                          ON E.courseId = B.courseId';

        v_where := '';
        IF v_courseId IS NOT NULL THEN
            v_where := v_where || ' AND B.courseId = ''' || v_courseId || '''';
        END IF;
        IF v_courseVersion IS NOT NULL THEN
            v_where := v_where || ' AND B.courseVersion = ' || v_courseVersion;
        END IF;
        IF v_turnId IS NOT NULL THEN
            v_where := v_where || ' AND B.turnId = ' || v_turnId;
        END IF;
        IF v_unitId IS NOT NULL THEN
            v_where := v_where || ' AND B.unitId = ' || v_unitId;
        END IF;
        IF v_personId IS NOT NULL THEN
            -- obter tanto disciplinas nas quais a pessoa esté
            -- matriculada quanto disciplinas das quais a pessoa
            -- é profesor.
            v_where := v_where || ' AND (EXISTS (SELECT 1
                                                   FROM acdEnroll X
                                             INNER JOIN acdContract Y
                                                     ON Y.contractId = X.contractId
                                                  WHERE X.groupId = C.groupId
                                                    AND Y.personId = ' || v_personId || ')
                                         OR
                                         EXISTS (SELECT 1
                                                   FROM acdScheduleProfessor X
                                                  WHERE X.scheduleId = D.scheduleId
                                                    AND X.professorId = ' || v_personId || ')
                                        )';
        END IF;

        v_sql := v_sql || CASE WHEN LENGTH(v_where) > 0 THEN ' WHERE ' || SUBSTR(v_where, 6) ELSE '' END || ' UNION';
    END IF;

    -- se configurado para exibir eventos
    IF v_showEvents THEN
        v_sql := v_sql || '
                      -- Eventos (acdEvent)
                      SELECT GENERATE_SERIES(A.beginDate, A.endDate, ''1 day'') AS occurrenceDate,
                             2 AS appearanceOrder,
                             A.description AS eventDescription
                        FROM acdEvent A';
        -- se especificada pessoa, verificar se ela participa do evento
        IF v_personId IS NOT NULL THEN
            v_sql := v_sql || ' WHERE EXISTS (SELECT 1
                                                FROM acdEventParticipation X
                                               WHERE X.eventId = A.eventId
                                                 AND X.personId = ' || v_personId || ')';
        END IF;

        v_sql := v_sql || ' UNION';
    END IF;

    -- se configurado para exibir períodos de matrícula
    IF v_showEnrollPeriods THEN
        v_sql := v_sql || '
                      -- períodos de matrícula (acdPeriodEnrollDate)
                      SELECT GENERATE_SERIES(A.beginDate, A.endDate+1, ''1 day'') AS occurrenceDate,
                             3 AS appearanceOrder,
                             A.description AS eventDescription
                        FROM acdPeriodEnrollDate A
                  INNER JOIN acdLearningPeriod B
                          ON A.learningPeriodId = B.learningPeriodId';

        v_where := '';
        IF v_courseId IS NOT NULL THEN
            v_where := v_where || ' AND B.courseId = ''' || v_courseId || '''';
        END IF;
        IF v_courseVersion IS NOT NULL THEN
            v_where := v_where || ' AND B.courseVersion = ' || v_courseVersion;
        END IF;
        IF v_turnId IS NOT NULL THEN
            v_where := v_where || ' AND B.turnId = ' || v_turnId;
        END IF;
        IF v_unitId IS NOT NULL THEN
            v_where := v_where || ' AND B.unitId = ' || v_unitId;
        END IF;
        IF v_personId IS NOT NULL THEN
            v_where := v_where || ' AND EXISTS (SELECT 1
                                                  FROM acdContract X
                                                 WHERE X.courseId = B.courseId
                                                   AND X.courseVersion = B.courseVersion
                                                   AND X.unitId = B.unitId
                                                   AND X.turnId = B.turnId
                                                   AND X.personId = ' || v_personId || ')';
        END IF;

        v_sql := v_sql || CASE WHEN LENGTH(v_where) > 0 THEN ' WHERE ' || SUBSTR(v_where, 6) ELSE '' END || ' UNION';
    END IF;

    -- se configurado para exibir entradas do calendário acadêmico
    IF v_showAcademicCalendar THEN
        v_sql := v_sql || '
                      -- Calendario academico (acdAcademicCalendar)
                      SELECT A.eventDate AS occurrenceDate,
                             4 AS appearanceOrder,
                             A.description AS eventDescription
                        FROM acdAcademicCalendarEvent A';

        v_where := '';
        IF v_courseId IS NOT NULL THEN
            v_where := v_where || ' AND (A.courseId = ''' || v_courseId || ''' OR A.courseId IS NULL)';
        END IF;
        IF v_courseVersion IS NOT NULL THEN
            v_where := v_where || ' AND (A.courseVersion = ' || v_courseVersion || ' OR A.courseVersion IS NULL)';
        END IF;
        IF v_turnId IS NOT NULL THEN
            v_where := v_where || ' AND (A.turnId = ' || v_turnId || ' OR A.turnId IS NULL)';
        END IF;
        IF v_unitId IS NOT NULL THEN
            v_where := v_where || ' AND (A.unitId = ' || v_unitId || ' OR A.unitId IS NULL)';
        END IF;
        IF v_personId IS NOT NULL THEN
            -- nao ha curso definido ou eh um curso
            -- no qual a pessoa tenha contrato
            v_where := v_where || ' AND ((A.courseId IS NULL
                                          AND A.courseVersion IS NULL
                                          AND A.unitId IS NULL
                                          AND A.turnId IS NULL)
                                         OR
                                         (EXISTS (SELECT 1
                                                    FROM acdContract X
                                                   WHERE X.personId = ' || v_personId || '
                                                     AND X.courseId = A.courseId
                                                     AND X.courseVersion = A.courseVersion
                                                     AND X.unitId = A.unitId
                                                     AND X.turnId = A.turnId)))';
        END IF;

        v_sql := v_sql || CASE WHEN LENGTH(v_where) > 0 THEN ' WHERE ' || SUBSTR(v_where, 6) ELSE '' END || ' UNION';
    END IF;

    -- se configurado para exibir unidades temáticas das quais o usuário é preceptor
    IF v_showResidencyPreceptor THEN
        v_sql := v_sql || '
                      SELECT GENERATE_SERIES(OUT.inicio, OUT.fim+1, ''1 day'') AS occurrenceDate,
                             5 AS appearanceOrder,
                             ''Resid: '' || UT.descricao AS eventDescription
                        FROM res.unidadeTematica UT
                  INNER JOIN res.ofertaDeUnidadeTematica OUT
                          ON OUT.unidadeTematicaId = UT.unidadeTematicaId';

        IF v_personId IS NOT NULL THEN
            v_sql := v_sql || ' WHERE OUT.personId = ' || v_personId;
        END IF;

            --RAISE NOTICE '%', v_sql;


        v_sql := v_sql || ' UNION';
    END IF;

    -- se configurado para exibir unidades temáticas das quais o usuário é residente
    IF v_showResidencyOfUser THEN
        v_sql := v_sql || '
                      SELECT GENERATE_SERIES(OUT.inicio, OUT.fim+1, ''1 day'') AS occurrenceDate,
                             6 AS appearanceOrder,
                             ''Resid: '' || UT.descricao AS eventDescription
                        FROM res.unidadeTematica UT
                  INNER JOIN res.ofertaDeUnidadeTematica OUT
                          ON OUT.unidadeTematicaId = UT.unidadeTematicaId';

        IF v_personId IS NOT NULL THEN
            v_sql := v_sql || ' WHERE OUT.ofertadeunidadetematicaid IN (
                  SELECT ODR.ofertadeunidadetematicaid
                    FROM res.ofertaDoResidente ODR
              INNER JOIN res.residente R
                      ON R.residenteId = ODR.residenteId
                   WHERE R.personId = ' || v_personId || ')';
        END IF;

            --RAISE NOTICE '%', v_sql;


        v_sql := v_sql || ' UNION';
    END IF;

    -- se configurado para exibir atividades programadas da pessoa
    IF v_showScheduledActivity THEN
        v_sql := v_sql || '
                      SELECT GENERATE_SERIES(SA.startDate::date, SA.endDate::date, ''1 day'') AS occurrenceDate,
                             7 AS appearanceOrder,
                             ''Atividade programada: '' || TO_CHAR(SA.startDate, getParameter(''BASIC'', ''MASK_TIMESTAMP_DEFAULT'')) || '' - '' || TO_CHAR(SA.endDate, getParameter(''BASIC'', ''MASK_TIMESTAMP_DEFAULT''))  || '' : '' || SA.description AS eventDescription
                        FROM hur.scheduledactivity SA
                  INNER JOIN hur.scheduledactivityparticipant SAP
                          ON SA.scheduledActivityId = SAP.scheduledActivityId';

        IF v_personId IS NOT NULL THEN
            v_sql := v_sql || ' WHERE SAP.personId = ' || v_personId;
        END IF;
            
            --RAISE NOTICE '%', v_sql;

        v_sql := v_sql || ' UNION';
    END IF;

    v_sql := SUBSTR(v_sql, 1, LENGTH(v_sql)-5) || ') B
                  ON B.occurrenceDate = A.referenceDate::date
            GROUP BY A.referenceDate,
                     B.appearanceOrder
            ORDER BY A.referenceDate,
                     B.appearanceOrder';

    RETURN QUERY EXECUTE v_sql;
END;
$$;

