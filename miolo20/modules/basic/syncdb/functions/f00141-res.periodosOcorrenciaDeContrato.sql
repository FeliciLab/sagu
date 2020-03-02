CREATE OR REPLACE FUNCTION res.periodosOcorrenciaDeContrato(
    p_residenteId int
) RETURNS text AS
$BODY$
/*************************************************************************************
  NAME: res.periodosOcorrenciaDeContrato
  PURPOSE: Retorna periodos separados por \n

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       23/08/2011 Moises Heberle    1. FUNÇÃO criada.
  1.0       20/10/2011 ftomasini         1. Alteração para não aparecer 1° periodo
                                            quando só existe um.
**************************************************************************************/
DECLARE
    v_retVal text[];
    v_tempVal text;
    v_row RECORD;
    v_bloqueia boolean;
    v_lastPeriod timestamp;
    v_count int;
    v_forCount int;
    v_periodCount int;
    v_aux varchar;
BEGIN
    v_aux := ' ';
    v_forCount := 0;
    v_periodCount := 1;

    SELECT INTO v_count COUNT(*)
           FROM res.ocorrenciaDeContrato ODC
          WHERE ODC.residenteId = p_residenteId;

    FOR v_row IN (SELECT ODC.dataHora,
                         SOC.bloqueiaResidencia
                    FROM res.ocorrenciaDeContrato ODC
              INNER JOIN res.statusDaOcorrenciaDeContrato SOC
                      ON SOC.statusdaocorrenciadecontratoid = ODC.statusdaocorrenciadecontratoid
                   WHERE ODC.residenteId = p_residenteId
                ORDER BY dataHora)
    LOOP
        v_forCount := v_forCount + 1;

        -- Caso nao tenha ainda status, define o atual
        IF v_bloqueia IS NULL
        THEN
            v_bloqueia = v_row.bloqueiaResidencia;
        END IF;

        -- Caso nao tenha ainda lastPeriod, define ultimo
        IF v_lastPeriod IS NULL
        THEN
            v_lastPeriod = v_row.dataHora;
        END IF;

        -- Quando muda o status OU ultimo contador, adiciona uma mensagem na fila
        IF ( (v_row.bloqueiaResidencia != v_bloqueia) OR (v_count = v_forCount) )
        THEN
            IF ( v_bloqueia IS FALSE )
            THEN
                v_aux := 'Período: ' || dataPorExtenso(v_lastPeriod::date) || ' à ' || dataPorExtenso(v_row.dataHora::date);
                IF v_periodCount > 1
                THEN
                   v_aux := v_periodCount ||'° '|| v_aux;
                END IF;

                v_retVal := array_append(v_retVal,  v_aux::text);
                v_periodCount := v_periodCount + 1;
            END IF;

            v_lastPeriod := v_row.dataHora;
        END IF;

        -- Define o status atual
        v_bloqueia = v_row.bloqueiaResidencia;
    END LOOP;

    RETURN array_to_string(v_retVal, E'\n');
END;
$BODY$
LANGUAGE 'plpgsql';
--
