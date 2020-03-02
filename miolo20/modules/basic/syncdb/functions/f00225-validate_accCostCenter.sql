CREATE OR REPLACE FUNCTION validate_accCostCenter()
RETURNS trigger AS
$$
DECLARE
    v_check boolean;
BEGIN

    v_check = TRUE;
    
    IF ( length(NEW.parentcostcenterid) > 0 ) THEN
    BEGIN
        SELECT INTO v_check isCostCenterActive(NEW.parentcostcenterid);
    END;
    END IF;
    
    IF ( v_check IS FALSE ) THEN
    BEGIN
        RAISE EXCEPTION 'O centro de custo % está inativo.', NEW.parentcostcenterid;
    END;
    END IF;
    
    RETURN NEW;

END;
$$
LANGUAGE plpgsql;


