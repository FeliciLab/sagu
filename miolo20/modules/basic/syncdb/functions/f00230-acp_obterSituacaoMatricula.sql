CREATE OR REPLACE FUNCTION acp_obterSituacaoMatricula(p_matriculaid int)
RETURNS TEXT AS
$BODY$
/*************************************************************************************
  NAME: acp_obtersituacaomatricula
  PURPOSE: Retorna a situação por extenso da matricula passada por parâmetro
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/06/2014 Jonas G. Diel        Função criada.    
**************************************************************************************/
DECLARE
    v_situacao acpmatricula.situacao%TYPE;
BEGIN
    SELECT INTO v_situacao situacao FROM acpmatricula WHERE matriculaid = p_matriculaid;
    RETURN CASE v_situacao 
        WHEN 'I' THEN 'Inscrito'
        WHEN 'M' THEN 'Matriculado'
        WHEN 'T' THEN 'Trancado'
        WHEN 'C' THEN 'Cancelado'
        WHEN 'V' THEN 'Reativado'
        WHEN 'A' THEN 'Aprovado'
        WHEN 'R' THEN 'Reprovado'
        WHEN 'F' THEN 'Reprovado por faltas'
    END;
END;
$BODY$
    LANGUAGE 'plpgsql' VOLATILE
    COST 100;
--
