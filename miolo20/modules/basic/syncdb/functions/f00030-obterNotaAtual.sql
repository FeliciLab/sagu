CREATE OR REPLACE FUNCTION obterNotaAtual(p_degreeId int, p_enrollId int)
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterNotaAtual
  PURPOSE: Obtem a nota atual de um degreeId + enrollId passados.
  DESCRIPTION: Obtem a nota atual de um degreeId + enrollId passados.
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN (SELECT nota FROM getDegreeEnrollCurrentGrade(p_degreeId, p_enrollId, false));
END; 
$BODY$ 
language plpgsql;
--
