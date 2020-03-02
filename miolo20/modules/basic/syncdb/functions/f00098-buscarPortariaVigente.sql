/*************************************************************************************
  NAME: buscarPortariaVigente
  DESCRIPTION: Obtem dados sobre a Portaria Vigente
**************************************************************************************/
CREATE OR REPLACE FUNCTION buscarPortariaVigente(p_courseid varchar, p_courseversion int, p_turnid int, p_unitid int, p_dataReconhecimento date) 
RETURNS TEXT AS $$
BEGIN
    RETURN ( SELECT documentoreconhecimento
               FROM obterPortariaVigente(p_courseid, p_courseversion, p_turnid, p_unitid, p_dataReconhecimento::DATE)
           );
END;
$$ LANGUAGE 'plpgsql';
