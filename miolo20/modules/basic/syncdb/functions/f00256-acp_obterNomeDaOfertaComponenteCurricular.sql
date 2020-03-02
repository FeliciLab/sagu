CREATE OR REPLACE FUNCTION acp_obterNomeDaOfertaComponenteCurricular(p_ofertacomponentecurricularid integer)
  RETURNS TEXT AS
$BODY$
/*************************************************************************************
  NAME: acp_obterNomeDaOfertaComponenteCurricular
  PURPOSE: Retorna o nome do componente curricular ofertado
 REVISIONS:
  Ver       Date       Author               Description
  --------- ---------- -----------------    ------------------------------------
  1.0       19/08/2014 Natanil I. da Silva  1. Função criada.
**************************************************************************************/
DECLARE
                                                                                                                                                  
BEGIN                                                                                   
											 
  RETURN( SELECT C.nome                                                                  
	    FROM acpOfertacomponentecurricular A                                         
 INNER JOIN ONLY acpcomponentecurricularmatriz B                                         
	      ON A.componentecurricularmatrizid = B.componentecurricularmatrizid         
      INNER JOIN acpcomponentecurricular C                                               
	      ON B.componentecurricularid = C.componentecurricularid                     
	   WHERE A.ofertacomponentecurricularid = p_ofertacomponentecurricularid );      
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
