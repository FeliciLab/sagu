CREATE OR REPLACE FUNCTION isCostCenterActive(p_costcenterid TEXT)
RETURNS BOOLEAN AS $$
DECLARE
    v_active boolean;
BEGIN

    SELECT INTO v_active active FROM acccostcenter WHERE costcenterid = p_costcenterid;
    
    RETURN v_active;

END;
$$
LANGUAGE plpgsql;
