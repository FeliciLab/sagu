--
CREATE OR REPLACE FUNCTION isFinanceEnrolledInPeriod(p_contractid integer, p_periodid character varying)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: isfinanceenrolledinperiod
  PURPOSE: Verifica se há títulos abertos em determinado período.
**************************************************************************************/
DECLARE
    v_count integer;
BEGIN
    SELECT INTO v_count count(*)
           FROM finReceivableInvoice AA
          INNER JOIN finEntry BB
             ON BB.invoiceId = AA.invoiceId
            AND BB.contractId = p_contractid
            AND EXISTS (SELECT learningPeriodId FROM acdLearningPeriod WHERE periodid = p_periodid AND learningperiodid = BB.learningperiodid)
            AND AA.balance <= 0;
    
    IF v_count > 0
    THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION isfinanceenrolledinperiod(integer, character varying)
  OWNER TO postgres;
--
