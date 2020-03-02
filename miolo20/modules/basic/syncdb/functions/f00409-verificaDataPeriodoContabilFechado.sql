CREATE OR REPLACE FUNCTION verificaDataPeriodoContabilFechado(p_data DATE)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: verificaDataPeriodoContabilFechado
  DESCRIPTION: Função que verifica se uma detarminada data está dentro de um período
  contábil fechado, retorna true caso esteja e false caso contrário.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       25/05/15   Nataniel I. da Silva  1. Função criada.
******************************************************************************/
DECLARE
    -- Recebe registros da tabela accFechamentoDePeriodoContabil
    v_periodo_contabil RECORD;
BEGIN
    SELECT INTO v_periodo_contabil *
           FROM accFechamentoDePeriodoContabil
          WHERE datadefechamento >= p_data
       ORDER BY fechamentoDePeriodoContabilId ASC;

    IF v_periodo_contabil.fechamentodeperiodocontabilid IS NOT NULL AND v_periodo_contabil.estaFechado IS TRUE THEN
        RETURN TRUE;
    END IF;
    
    RETURN FALSE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION verificaDataPeriodoContabilFechado(DATE)
  OWNER TO postgres;
