CREATE OR REPLACE FUNCTION atualizarMediasConcluintes()
RETURNS boolean 
AS $BODY$
DECLARE

    v_contractId integer;    
    v_globalAverage double precision;

BEGIN

    FOR v_contractId IN SELECT DISTINCT contractid FROM acdmovementcontract WHERE statecontractid = getParameter('ACADEMIC', 'STATE_CONTRACT_ID_CONCLUSION_ALL_CURRICULAR_COMPONENT')::INT LOOP
    BEGIN
        SELECT INTO v_globalAverage obterMediaGlobal(v_contractId);

        IF ( v_globalAverage IS NOT NULL ) THEN
        BEGIN
            UPDATE acdcontract SET globalaverage = v_globalAverage WHERE contractid = v_contractId;
        END;
        END IF;
        
    END;
    END LOOP;
    
    RETURN true;

END;
$BODY$
LANGUAGE plpgsql;
