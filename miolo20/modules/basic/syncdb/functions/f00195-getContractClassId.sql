CREATE OR REPLACE FUNCTION getcontractclassid(p_contractid integer)
  RETURNS character varying AS
$BODY$
/******************************************************************************
  NAME: getContractClassId
  DESCRIPTION: Função que retorna a turma em que determinado contrato se encontra.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       03/02/2011 Moises Heberle    1. Função alterada.
******************************************************************************/
DECLARE
    v_classId acdClass.classId%TYPE;
BEGIN
    SELECT INTO v_classId
           A.classId
      FROM acdClassPupil A
     WHERE A.contractId = p_contractId
       AND (A.endDate IS NULL OR A.endDate > now()::date)
  ORDER BY A.beginDate DESC
     LIMIT 1;

     RETURN v_classId;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION getcontractclassid(integer)
  OWNER TO postgres;

