CREATE OR REPLACE FUNCTION curriculumPossuiVinculoDeMatricula(p_curriculumId INT, p_periodo VARCHAR)
RETURNS BOOLEAN AS
/******************************************************************************************
  NAME: curriculumPossuiVinculoDeMatricula
  PURPOSE: Verifica se currículo possui vínculo para ser utilizado em matrícula.
  DESCRIPTION: vide "PURPOSE".
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       06/02/2015 Luís F. Wermann   1. Criada função.
******************************************************************************************/
$BODY$
DECLARE

--Estado da matrícula
v_result TEXT;
    
BEGIN

    SELECT INTO v_result (COUNT(CL.curriculumId) > 0)
           FROM acdCurriculumLink CL
     INNER JOIN acdGroup GR
             ON GR.curriculumId = CL.curriculumLinkId
     INNER JOIN acdLearningPeriod LP
             ON LP.learningPeriodId = GR.learningPeriodId
          WHERE CL.curriculumId = p_curriculumId
            AND CL.utilizaVinculoParaMatricula
            AND LP.periodId = p_periodo;

RETURN v_result;

END;
$BODY$ LANGUAGE plpgsql IMMUTABLE;