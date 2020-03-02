CREATE OR REPLACE FUNCTION bi_getContractInvoice(IN _invoiceid integer, OUT _contractid int, OUT _courseid varchar, OUT _courseversion int, OUT _unitid int, OUT _turnid int) RETURNS SETOF record AS
$$
DECLARE
    resultado RECORD;
BEGIN

    SELECT G.contractid, G.courseid, G.courseversion, G.unitid, G.turnid 
      INTO resultado 
 FROM ONLY fininvoice A 
 LEFT JOIN finEntry F ON (F.invoiceid = A.invoiceid) 
 LEFT JOIN acdContract G ON (F.contractid = G.contractid) 
     WHERE A.invoiceid=$1 
       AND F.contractid IS NOT NULL
  ORDER BY G.datetime desc limit 1;
    _contractid:=resultado.contractid;
    _courseid:=resultado.courseid;
    _courseversion:=resultado.courseversion;
    _unitid:=resultado.unitid;
    _turnid:=resultado.turnid;

    RETURN NEXT;
END;
$$ LANGUAGE 'plpgsql';
