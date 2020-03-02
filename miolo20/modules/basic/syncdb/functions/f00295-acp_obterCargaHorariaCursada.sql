CREATE OR REPLACE FUNCTION acp_obterCargaHorariaCursada (p_cursoid INTEGER, p_personid INTEGER)
RETURNS NUMERIC(10,2) AS
$BODY$
DECLARE
    v_cargahorariacursada NUMERIC(10,2);
BEGIN

    SELECT INTO v_cargahorariacursada SUM(COALESCE(CCD.cargahoraria, CCTC.cargahoraria))::NUMERIC(10,2)
      FROM acpcurso C
 LEFT JOIN acpocorrenciacurso OC ON (C.cursoid = OC.cursoid) 
 LEFT JOIN acpofertacurso OFC ON (OFC.ocorrenciacursoid = OC.ocorrenciacursoid)
 LEFT JOIN acpofertaturma OFT ON (OFC.ofertacursoid = OFT.ofertacursoid)
 LEFT JOIN acpofertacomponentecurricular OCC ON (OFT.ofertaturmaid = OCC.ofertaturmaid)
 LEFT JOIN acpmatricula M ON (OCC.ofertacomponentecurricularid = M.ofertacomponentecurricularid)
 LEFT JOIN acpcomponentecurricularmatriz CCM ON (OCC.componentecurricularmatrizid = CCM.componentecurricularmatrizid)
 LEFT JOIN acpcomponentecurricular CC ON (CCM.componentecurricularid = CC.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculardisciplina CCD ON (CC.componentecurricularid = CCD.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculartrabalhoconclusao CCTC ON (CC.componentecurricularid = CCTC.componentecurricularid) 
     WHERE C.cursoid = p_cursoid AND M.personid = p_personid AND M.situacao IN ('A', 'P', 'E') ;

     RETURN v_cargahorariacursada;

END;
$BODY$ LANGUAGE plpgsql;
