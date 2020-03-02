CREATE OR REPLACE FUNCTION usuarioTemPermissao(v_iduser integer, v_unitid integer)
  RETURNS boolean AS
$BODY$
/******************************************************************************
    Retorna se usuario (userId) tem permissao na unidade (unitId) passada.
******************************************************************************/
DECLARE
BEGIN
    RETURN EXISTS(
        SELECT 1
          FROM miolo_groupuser
         WHERE iduser = v_iduser
           AND (unitid = v_unitid OR unitid IS NULL)
    );
END;
$BODY$
LANGUAGE plpgsql;
