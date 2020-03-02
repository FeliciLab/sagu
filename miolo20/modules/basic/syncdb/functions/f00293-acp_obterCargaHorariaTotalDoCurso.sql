CREATE OR REPLACE FUNCTION acp_obterCargaHorariaTotalDoCurso (p_cursoid INTEGER)
RETURNS NUMERIC(10,2) AS
$BODY$
DECLARE
    v_cargahorariadocurso NUMERIC(10,2);
BEGIN

    SELECT INTO v_cargahorariadocurso SUM(COALESCE(CCD.cargahoraria, CCTC.cargahoraria))::NUMERIC(10,2)
      FROM acpcurso C 
 LEFT JOIN acpmatrizcurricular MC ON (C.cursoid = MC.cursoid) 
 LEFT JOIN acpmatrizcurriculargrupo MCG ON (MC.matrizcurricularid = MCG.matrizcurricularid) 
 LEFT JOIN acpcomponentecurricularmatriz CCM ON (MCG.matrizcurriculargrupoid = CCM.matrizcurriculargrupoid) 
 LEFT JOIN acpcomponentecurricular CC ON (CCM.componentecurricularid = CC.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculardisciplina CCD ON (CC.componentecurricularid = CCD.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculartrabalhoconclusao CCTC ON (CC.componentecurricularid = CCTC.componentecurricularid) 
     WHERE C.cursoid = p_cursoid;

     RETURN v_cargahorariadocurso;

END;
$BODY$ LANGUAGE plpgsql;
