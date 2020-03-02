CREATE OR REPLACE FUNCTION isFinalAccount(p_accountSchemeId varchar(30))
RETURNS BOOLEAN
AS $$
DECLARE

    v_parentSchemeId varchar(30);
    
BEGIN

    SELECT INTO v_parentSchemeId parentaccountschemeid FROM accaccountscheme WHERE accountschemeid = p_accountSchemeId;
    RAISE NOTICE '%', v_parentSchemeId;

    IF ( v_parentSchemeId IS NOT NULL ) THEN
        SELECT INTO v_parentSchemeId parentaccountschemeid FROM accaccountscheme WHERE parentaccountschemeid = p_accountSchemeId LIMIT 1;
        RETURN v_parentSchemeId IS NULL;
    ELSE
        RETURN FALSE;
    END IF;    
END;
$$ LANGUAGE plpgsql;
