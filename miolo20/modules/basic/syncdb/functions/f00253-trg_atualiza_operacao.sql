/**
 * Trigger que utiliza a operação do convênio da pessoa na importação de retorno de títulos.
 */
CREATE OR REPLACE FUNCTION trg_atualiza_operacao()
RETURNS TRIGGER AS
$BODY$
DECLARE
    v_personid INTEGER; 
    v_bankMovement RECORD;
    v_convenio RECORD;
    v_valorConvenio DOUBLE PRECISION;
    v_discountoperation INTEGER;
    v_otherdiscountsoperation INTEGER;
BEGIN

    SELECT INTO v_personid personid FROM ONLY fininvoice WHERE invoiceid = NEW.invoiceid;
    SELECT INTO v_bankMovement * FROM temp_bank_movement WHERE tempbankmovementid = NEW.tempbankmovementid;
    SELECT INTO v_convenio * FROM finconvenantperson P INNER JOIN finconvenant C USING (convenantid) WHERE P.personid = v_personid AND now()::date BETWEEN P.begindate AND P.enddate LIMIT 1;   
    SELECT INTO v_discountoperation discountoperation FROM finDefaultOperations;
    SELECT INTO v_otherdiscountsoperation otherdiscountsoperation FROM finDefaultOperations;

    IF ( v_convenio.convenantid IS NOT NULL ) THEN
    BEGIN   
        IF ( v_convenio.ispercent ) THEN
        BEGIN
            v_valorConvenio := ROUND( v_bankMovement.balance * (v_convenio.value::float/100::float)::numeric, 2 );          
        END;
        ELSE
        BEGIN
            v_valorConvenio := v_convenio.value;
        END;
        END IF;

        IF ( v_valorConvenio = NEW.value AND ( NEW.operationid = v_discountoperation OR NEW.operationid = v_otherdiscountsoperation) ) THEN
        BEGIN
            NEW.operationid := v_convenio.convenantoperation;
        END;
        END IF;
    END;
    END IF;

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_atualiza_operacao ON temp_bank_movement_entries;
CREATE TRIGGER trg_atualiza_operacao BEFORE INSERT OR UPDATE ON temp_bank_movement_entries FOR EACH ROW EXECUTE PROCEDURE trg_atualiza_operacao();
