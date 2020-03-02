CREATE OR REPLACE FUNCTION syncAccountScheme()
RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: syncaccountscheme
  DESCRIPTION: Trigger que sincroniza o plano de contas da operação para o
  lançamento sempre que um novo lançamento inserido

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       24/07/14   Jonas Diel         Função criada.
******************************************************************************/
DECLARE
    v_accountschemeid varchar(30); --Plano de contas
BEGIN
    IF NEW.accountSchemeId IS NULL
    THEN
        SELECT INTO v_accountschemeid accountschemeid FROM finOperation WHERE operationid = NEW.operationid;
        IF v_accountschemeid IS NOT NULL
        THEN
            UPDATE finEntry SET accountschemeid = v_accountschemeid WHERE entryid = NEW.entryid;
        END IF;
    END IF;

    RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql ;

DROP TRIGGER IF EXISTS trg_syncaccountscheme ON finentry;
CREATE TRIGGER trg_syncaccountscheme AFTER INSERT ON finentry FOR EACH ROW EXECUTE PROCEDURE syncaccountscheme();
