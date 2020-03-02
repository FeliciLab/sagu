CREATE OR REPLACE FUNCTION med.obterPeriodoDoResidente(p_residenteid integer)
  RETURNS character varying AS
$BODY$
/*************************************************************************************
  NAME: med.obterPeriodoDoResidente
  PURPOSE: Função que recebe o código de um residente, e retorna o período em que ele
  se encontra (P1, P2, P3 ou NULL).

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ---------------------------------------------
  1.0       22/09/2011 Arthur Lehdermann 1. Função criada.
  1.1       01/12/2014 Luís F. Wermann   Modificada para funcionar na nova
                                         residência médica.
**************************************************************************************/
DECLARE
    v_periodo VARCHAR;
BEGIN
    SELECT MAX(C.periodo) INTO v_periodo
      FROM med.ofertaDoResidente A
INNER JOIN med.ofertaDeUnidadeTematica B
        ON A.ofertaDeUnidadeTematicaId = B.ofertaDeUnidadeTematicaId
INNER JOIN med.unidadeTematica C
        ON C.unidadeTematicaId = B.unidadeTematicaId
     WHERE A.residenteId = p_residenteId;

    RETURN v_periodo;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION med.obterperiododoresidente(integer)
  OWNER TO postgres;
