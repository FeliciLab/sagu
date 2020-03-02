/******************************************************************************
  NAME: obterPortariaVigente
  DESCRIPTION: Retorna um registro completo da portaria vigente na data passada.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       06/04/2015 Luís F. Wermann       1. Função criada.
******************************************************************************/
CREATE OR REPLACE FUNCTION obterPortariaVigente(p_courseid varchar, p_courseversion int, p_turnid int, p_unitid int, p_dataReconhecimento date) 
RETURNS SETOF acdReconhecimentoDeCurso AS 
$BODY$
DECLARE
    v_acdReconhecimentoCurso acdReconhecimentoDeCurso;

BEGIN
        SELECT INTO v_acdReconhecimentoCurso *
          FROM acdreconhecimentodecurso
         WHERE courseid = p_courseid
           AND courseversion = p_courseversion
           AND turnid = p_turnid
           AND unitid = p_unitid
           AND p_dataReconhecimento::DATE BETWEEN datainicial AND datafinal
      ORDER BY datareconhecimento 
         LIMIT 1;

    RETURN NEXT v_acdReconhecimentoCurso;
END;
$BODY$ LANGUAGE 'plpgsql' IMMUTABLE;