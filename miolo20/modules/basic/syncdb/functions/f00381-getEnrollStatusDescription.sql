CREATE OR REPLACE FUNCTION getEnrollStatusDescription(p_enrollStatusId INT)
RETURNS TEXT AS
/******************************************************************************************
  NAME: getEnrollStatusDescription
  PURPOSE: Obtem a descrição do estado da matrícula do aluno, se ele tiver, na oferecida.
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Criada função para obter estado da matrícula 
                                         na oferecida.
******************************************************************************************/
$BODY$
DECLARE

--Estado da matrícula
v_description TEXT;
    
BEGIN

    SELECT INTO v_description description
      FROM acdEnrollStatus
     WHERE statusId = p_enrollStatusId;

RETURN v_description;

END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;