CREATE OR REPLACE FUNCTION res.obterPeriodoDoResidente(p_residenteid integer)
  RETURNS character varying AS
$BODY$
/*************************************************************************************
  NAME: res.obterPeriodoDoResidente
  PURPOSE: Função que recebe o código de um residente, e retorna o período em que ele
  se encontra (P1, P2, P3 ou NULL).

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ---------------------------------------------
  1.0       27/09/2011 Augusto A. Silva  1. Função criada.
**************************************************************************************/
DECLARE
    v_periodo VARCHAR;
BEGIN
    SELECT MAX(C.periodo) INTO v_periodo
      FROM res.ofertaDoResidente A
INNER JOIN res.ofertaDeUnidadeTematica B
        ON A.ofertaDeUnidadeTematicaId = B.ofertaDeUnidadeTematicaId
INNER JOIN res.unidadeTematica C
        ON C.unidadeTematicaId = B.unidadeTematicaId
     WHERE A.residenteId = p_residenteId;

    RETURN v_periodo;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION res.obterperiododoresidente(integer)
  OWNER TO postgres;
