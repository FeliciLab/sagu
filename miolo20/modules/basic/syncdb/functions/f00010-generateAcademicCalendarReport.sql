DROP TYPE IF EXISTS academicCalendarReportType;
CREATE TYPE academicCalendarReportType AS (
 datas text,
 eventos text);

DROP TYPE IF EXISTS academicCalendarReportEvt;
CREATE TYPE academicCalendarReportEvt AS (
 datas date[],
 evento text);

CREATE OR REPLACE FUNCTION generateAcademicCalendarReport(p_beginDate date, p_endDate date, p_opts character varying[], p_values character varying[])
RETURNS SETOF academicCalendarReportType AS
$BODY$
/*************************************************************************************
  NAME: generateAcademicCalendarReport
  
  PURPOSE: Retorna eventos agrupados quando estao em dias seguidos
           
  DESCRIPTION:
   - Retorna eventos agrupados quando estao em dias seguidos (muda a forma
    de retorno em comparacao com a FUNÇÃO generateAcademicCalendar()).
    
   - Foi feito especificamente para o relatório academico (localizado
    em Academico -> Documento)
   
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       12/04/2012 moises            1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_row record;
    v_out academicCalendarReportType;
    v_evt academicCalendarReportEvt;
    v_evts academicCalendarReportEvt[];
    v_tmpEvts academicCalendarReportEvt[];
    v_datas date[];
    v_index int;
    v_dayIndex int;
    v_found boolean;
    v_evento text;
    v_eventos text[];
BEGIN
    v_evts = null;

    FOR v_row IN (SELECT * FROM generateAcademicCalendar( p_beginDate, p_endDate, p_opts, p_values) ORDER BY referenceDate)
    LOOP    
        v_eventos := STRING_TO_ARRAY(v_row.dayEvents, E'\n');
        v_dayIndex := 1;
        
        WHILE v_dayIndex <= array_length(v_eventos, 1)
        LOOP
            v_evento := v_eventos[v_dayIndex];
            v_dayIndex := v_dayIndex + 1;

            v_found := false;
            v_tmpEvts := null;
            v_index := 1;

            WHILE v_index <= array_length(v_evts, 1)
            LOOP
                v_evt := v_evts[v_index];
                v_index := v_index + 1;

                -- Se evento encontrado no array, adiciona data a mais
                IF ( md5(v_evt.evento) = md5(v_evento) AND EXTRACT(month FROM v_row.referenceDate) = EXTRACT(month FROM p_beginDate) )
                THEN
                    -- Se data anterior for igual data atual - 1 dia, significa que evento esta em sequencia
                    IF v_evt.datas[ array_length(v_evt.datas, 1) ] = (v_row.referenceDate - interval '1day')::date
                    THEN
                        v_evt.datas := array_append( v_evt.datas, v_row.referenceDate );
                        v_found := true;
                    END IF;
                END IF;

                v_tmpEvts := array_append(v_tmpEvts, v_evt);
            END LOOP;

            IF v_found IS FALSE
            THEN
                v_evt.evento := v_evento;
                v_evt.datas := ARRAY[v_row.referenceDate];
                v_tmpEvts := array_append(v_tmpEvts, v_evt);
            END IF;

            v_evts := v_tmpEvts;
        END LOOP;
    END LOOP;

    v_index := 1;
    WHILE v_index <= array_length(v_evts, 1)
    LOOP
        v_evt = v_evts[v_index];
        v_index := v_index + 1;

        IF (EXTRACT(month FROM (SELECT MIN(x) FROM UNNEST(v_evt.datas) AS x)) = EXTRACT(month FROM p_beginDate))
        THEN
            -- Prepara retorno formatado
            v_out.datas := CASE WHEN array_length(v_evt.datas, 1) > 1
                        THEN
                            EXTRACT(day FROM (SELECT MIN(x) FROM UNNEST(v_evt.datas) AS x)) || ' é ' || EXTRACT(day FROM (SELECT MAX(x) FROM UNNEST(v_evt.datas) AS x))
                        ELSE
                            EXTRACT(day FROM (SELECT MIN(x) FROM UNNEST(v_evt.datas) AS x)) || ''
                        END;
            v_out.eventos := v_evt.evento;

            RETURN NEXT v_out;
        END IF;
    END LOOP;

    RETURN;
END;
$BODY$
LANGUAGE 'plpgsql';
