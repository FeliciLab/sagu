CREATE OR REPLACE FUNCTION acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso(p_courseId CHAR, p_courseVersion INT, p_turnId INT, p_unitId INT)
RETURNS NUMERIC AS
$BODY$
/*********************************************************************************************
  NAME: acd_obterCargaHorariaTotalDeAtividadesComplementaresDoCurso
  PURPOSE: Obtém a carga horária total de atividades complementares do curso.
	   Soma todas as horas acadêmicas de disciplinas do tipo atividade
	   complementar, na matriz curricular da ocorrência de curso.
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       29/04/2014 Augusto A. SIlva  1. Função criada
*********************************************************************************************/
BEGIN
    RETURN (
	SELECT SUM(CC.academicNumberHours)::NUMERIC
	  FROM acdCurriculum C
    INNER JOIN acdCurricularComponent CC
	    ON (CC.curricularComponentId,
		CC.curricularComponentVersion) = (C.curricularComponentId,
						  C.curricularComponentVersion)
	 WHERE C.curriculumTypeId = getParameter('ACADEMIC', 'ACD_CURRICULUM_TYPE_COMPLEMENTARY_ACTIVITY')::INT
	   AND (C.courseId,
		C.courseVersion,
		C.turnId,
		C.unitId) = (p_courseId, 
			     p_courseVersion, 
			     p_turnId, 
			     p_unitId)
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
