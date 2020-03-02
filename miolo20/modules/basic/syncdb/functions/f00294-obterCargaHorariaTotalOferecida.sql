CREATE OR REPLACE FUNCTION acp_obterCargaHorariaTotalOferecida (p_cursoid INTEGER, p_personid INTEGER)
RETURNS NUMERIC(10,2) AS
$BODY$
DECLARE
    v_cargahorariaoferecida NUMERIC(10,2);
BEGIN

    SELECT INTO v_cargahorariaoferecida SUM(COALESCE(CCD.cargahoraria, CCTC.cargahoraria))::NUMERIC(10,2)
      FROM acpcurso C
 LEFT JOIN acpocorrenciacurso OC ON (C.cursoid = OC.cursoid) 
 LEFT JOIN acpofertacurso OFC ON (OFC.ocorrenciacursoid = OC.ocorrenciacursoid)
 LEFT JOIN acpofertaturma OFT ON (OFC.ofertacursoid = OFT.ofertacursoid)
 LEFT JOIN acpinscricaoturmagrupo ITG ON (ITG.ofertaturmaid = OFT.ofertaturmaid)
 LEFT JOIN acpinscricao I ON (I.inscricaoid = ITG.inscricaoid)
 LEFT JOIN acpofertacomponentecurricular OCC ON (OFT.ofertaturmaid = OCC.ofertaturmaid)
 LEFT JOIN acpcomponentecurricularmatriz CCM ON (OCC.componentecurricularmatrizid = CCM.componentecurricularmatrizid)
 LEFT JOIN acpcomponentecurricular CC ON (CCM.componentecurricularid = CC.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculardisciplina CCD ON (CC.componentecurricularid = CCD.componentecurricularid) 
 LEFT JOIN acpcomponentecurriculartrabalhoconclusao CCTC ON (CC.componentecurricularid = CCTC.componentecurricularid) 
     WHERE C.cursoid = p_cursoid
       AND I.personid = p_personid
       AND OCC.datafechamento IS NOT NULL;

     RETURN v_cargahorariaoferecida;

END;
$BODY$ LANGUAGE plpgsql;
