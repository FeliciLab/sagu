CREATE OR REPLACE FUNCTION verificaTituloBloqueadoPorPeriodoContabilFechado(p_invoiceId integer)
RETURNS BOOLEAN AS 
$BODY$
/*******************************************************************************************
  NAME: verificaTituloBloqueadoPorPeriodoContabilFechado
  DESCRIPTION: Verifica se o título está bloqueado, perante ao período contábil

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       28/04/15   Nataniel I. da Silva  1. Função criada.
*******************************************************************************************/
DECLARE
    v_periodo_contabil_flag BOOLEAN := FALSE;
    v_titulo RECORD;
    v_periodo_contabil RECORD;
    v_data_registro DATE;
BEGIN
    FOR v_titulo IN ( SELECT obterDataDeCompetenciaDoLancamento(xy.entryId) as entrydate
                   FROM ONLY fininvoice xx 
                  INNER JOIN finentry xy
                          ON (xy.invoiceid = xx.invoiceid)
                       WHERE xx.invoiceid = p_invoiceId)
    LOOP
        IF (v_periodo_contabil_flag IS FALSE)
        THEN
            v_data_registro:=v_titulo.entrydate::DATE;

            SELECT INTO v_periodo_contabil *
                   FROM accFechamentoDePeriodoContabil
                  WHERE datadefechamento >= v_data_registro
               ORDER BY fechamentoDePeriodoContabilId ASC;

            IF (v_periodo_contabil.fechamentodeperiodocontabilid IS NOT NULL AND v_periodo_contabil.estaFechado IS TRUE)
            THEN
                v_periodo_contabil_flag := TRUE;
            END IF;
        END IF;
    END LOOP;

    RETURN v_periodo_contabil_flag;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE;