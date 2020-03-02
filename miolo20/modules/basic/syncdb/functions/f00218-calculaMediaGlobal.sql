CREATE OR REPLACE FUNCTION calculaMediaGlobal()
RETURNS trigger 
AS $BODY$
DECLARE
    
    v_globalAverage double precision;

BEGIN
    
    IF ( NEW.statecontractid = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_CONCLUSION_ALL_CURRICULAR_COMPONENT')::INT ) THEN
    BEGIN
        SELECT INTO v_globalAverage obterMediaGlobal(NEW.contractid);
        UPDATE acdcontract SET globalaverage = v_globalAverage WHERE contractid = NEW.contractid;
    END;
    END IF;
    
    RETURN NEW;
    
END;
$BODY$
language plpgsql;

DROP TRIGGER IF EXISTS acdContractGlobalAverage ON acdMovementContract;
CREATE TRIGGER acdContractGlobalAverage
    AFTER INSERT OR UPDATE ON acdMovementContract
    FOR EACH ROW
    EXECUTE PROCEDURE calculaMediaGlobal();
