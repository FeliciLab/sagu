DROP TRIGGER IF EXISTS valid_acccostcenter ON acccostcenter;
CREATE TRIGGER valid_acccostcenter BEFORE INSERT ON acccostcenter
    FOR EACH ROW EXECUTE PROCEDURE validate_acccostcenter();
   
CREATE OR REPLACE FUNCTION validate_costCenter()
RETURNS trigger AS
$$
DECLARE
    v_check boolean;
BEGIN

    v_check = TRUE;
    
    IF ( length(NEW.costcenterid) > 0 ) THEN
    BEGIN
        SELECT INTO v_check isCostCenterActive(NEW.costcenterid);
    END;
    END IF;
    
    IF ( v_check IS FALSE ) THEN
    BEGIN
        RAISE EXCEPTION 'O centro de custo % está inativo.', NEW.costcenterid;
    END;
    END IF;
    
    RETURN NEW;

END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS valid_acccourseaccount ON acccourseaccount;
CREATE TRIGGER valid_acccourseaccount BEFORE INSERT ON acccourseaccount
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();

DROP TRIGGER IF EXISTS valid_acdcourseoccurrence ON acdcourseoccurrence;
CREATE TRIGGER valid_acdcourseoccurrence BEFORE INSERT ON acdcourseoccurrence
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();

DROP TRIGGER IF EXISTS valid_acdevent ON acdevent;
CREATE TRIGGER valid_acdevent BEFORE INSERT ON acdevent
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_fincountermovement ON fincountermovement;
CREATE TRIGGER valid_fincountermovement BEFORE INSERT ON fincountermovement
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_finentry ON finentry;
CREATE TRIGGER valid_finentry BEFORE INSERT ON finentry
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_finincentive ON finincentive;
CREATE TRIGGER valid_finincentive BEFORE INSERT ON finincentive
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_finincomeforecast ON finincomeforecast;
CREATE TRIGGER valid_finincomeforecast BEFORE INSERT ON finincomeforecast
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_fininvoice ON fininvoice;
CREATE TRIGGER valid_fininvoice BEFORE INSERT ON fininvoice
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_capsolicitacao ON capsolicitacao;
CREATE TRIGGER valid_capsolicitacao BEFORE INSERT ON capsolicitacao
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_financeinformation ON spr.financeinformation;
CREATE TRIGGER valid_financeinformation BEFORE INSERT ON spr.financeinformation
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
    
DROP TRIGGER IF EXISTS valid_prcprecocurso ON prcprecocurso;
--CREATE TRIGGER valid_prcprecocurso BEFORE INSERT ON prcprecocurso
--    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();                                

DROP TRIGGER IF EXISTS valid_invoicenegociationconfig ON fin.invoicenegociationconfig;
CREATE TRIGGER valid_invoicenegociationconfig BEFORE INSERT ON fin.invoicenegociationconfig
    FOR EACH ROW EXECUTE PROCEDURE validate_costcenter();
