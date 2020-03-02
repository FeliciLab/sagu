CREATE OR REPLACE FUNCTION verificaPeriodoContabil()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaPeriodoContabil
  DESCRIPTION: Trigger que verifica e bloqueia a alteração de registros contábeis,
  caso o período do registro esteja fechado.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       23/10/14   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    -- Recebe a data do registro
    v_data_registro DATE;

    -- Recebe a data do período contábil que o resgistro se encontra
    v_periodo_contabil RECORD;

    -- Data formatada de fechamento do período contabil
    v_data_periodo_contabil TEXT;

    --Recebe títulos que estão sendo alterados
    v_titulo RECORD;

    v_periodo_contabil_flag BOOLEAN;
BEGIN
    -- Verifica de qual tabela foi disparada a trigger para obter a data do registro
    IF TG_RELNAME = 'finentry' THEN
        IF TG_OP = 'INSERT' THEN
	    v_data_registro := obterDataDeCompetenciaDoLancamento(NEW.entryId);
        ELSE
            v_data_registro := obterDataDeCompetenciaDoLancamento(OLD.entryId);
        END IF;
    END IF;

    IF TG_RELNAME = 'caplancamento' THEN
	IF TG_OP = 'INSERT' THEN
            v_data_registro := NEW.datalancamento::DATE;
	ELSE
	    v_data_registro := OLD.datalancamento::DATE;
	END IF;
    END IF;

    IF TG_RELNAME = 'finlancamentosemvinculo' THEN
	IF TG_OP = 'INSERT' THEN
            v_data_registro := NEW.datadecaixa::DATE;
	ELSE
	    v_data_registro := OLD.datadecaixa::DATE;
	END IF;
    END IF;

    IF TG_RELNAME = 'fincountermovement' THEN
	IF TG_OP = 'INSERT' THEN
            v_data_registro := NEW.movementdate::DATE;
	ELSE
	    v_data_registro := OLD.movementdate::DATE;
	END IF;
    END IF;

    IF TG_RELNAME = 'fin.bankmovement' THEN
	IF TG_OP = 'INSERT' THEN
            v_data_registro := NEW.occurrencedate::DATE;
	ELSE
	    v_data_registro := OLD.occurrencedate::DATE;
	END IF;
    END IF;

    IF TG_RELNAME = 'fininvoice' 
    THEN
        IF TG_OP = 'UPDATE' AND OLD.iscanceled = 'f' AND NEW.iscanceled = 't' 
        THEN
            v_periodo_contabil_flag:= 'f';

            FOR v_titulo IN ( SELECT obterDataDeCompetenciaDoLancamento(xy.entryId) as entrydate
                           FROM ONLY fininvoice xx 
                          INNER JOIN finentry xy
                                  ON (xy.invoiceid = xx.invoiceid)
                               WHERE xx.invoiceid = OLD.invoiceid)
            LOOP
                IF (v_periodo_contabil_flag = 'f')
                THEN
                    v_data_registro:=v_titulo.entrydate::DATE;

                    SELECT INTO v_periodo_contabil *
                           FROM accFechamentoDePeriodoContabil
                          WHERE datadefechamento >= v_data_registro
                       ORDER BY fechamentoDePeriodoContabilId ASC;
                    
                    IF (v_periodo_contabil.fechamentodeperiodocontabilid IS NOT NULL)
                    THEN
                        v_periodo_contabil_flag:= 't';
                    END IF;
                END IF;
            END LOOP;
        END IF;
    END IF;

    -- Precisa buscar o período contábil sempre, porque senão no IF abaixo estoura erro
    SELECT INTO v_periodo_contabil *
      FROM accFechamentoDePeriodoContabil
     WHERE datadefechamento >= v_data_registro
  ORDER BY fechamentoDePeriodoContabilId ASC;

    IF TG_RELNAME = 'fincheque' OR TG_RELNAME = 'finmovimentacaocheque' THEN
	IF TG_OP = 'INSERT' THEN
            v_data_registro := NEW.data::DATE;
	ELSE
	    v_data_registro := OLD.data::DATE;
	END IF;
    END IF;

    -- Verifica se a data passada está dentro de um período contábil fechado
    IF verificaDataPeriodoContabilFechado(v_data_registro) IS TRUE THEN
        v_data_periodo_contabil := datetouser(v_periodo_contabil.datadefechamento);
	RAISE EXCEPTION 'Desculpe, mas esta operação não pode ser efetuada, pois altera informações do período contábil % já fechado pelo usuário(a) %. Caso seja necessária a efetivação deste registro, reabra o período contábil.', v_data_periodo_contabil, v_periodo_contabil.username;
    END IF;
    
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
        RETURN NEW;
    ELSE
        RETURN OLD;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION verificaPeriodoContabil()
  OWNER TO postgres;

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON finentry;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON finentry
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON fininvoice;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE UPDATE
  ON fininvoice
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON finlancamentosemvinculo;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON finlancamentosemvinculo
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON caplancamento;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON caplancamento
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON fincountermovement;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON fincountermovement
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON fin.bankmovement;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON fin.bankmovement
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON fincheque;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON fincheque
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();

DROP TRIGGER IF EXISTS trg_verificaPeriodoContabil ON finmovimentacaocheque;
CREATE TRIGGER trg_verificaPeriodoContabil
  BEFORE INSERT OR UPDATE OR DELETE
  ON finmovimentacaocheque
  FOR EACH ROW
  EXECUTE PROCEDURE verificaPeriodoContabil();
