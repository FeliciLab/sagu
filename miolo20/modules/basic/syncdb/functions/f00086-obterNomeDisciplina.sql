CREATE OR REPLACE FUNCTION obterNomeDisciplina(p_groupId int)
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterNomeDisciplina
  DESCRIPTION: Obtem nome da disciplina a partir de disciplina oferecida passada.
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN (
             SELECT CC.curricularcomponentid || ' - ' || CC.name
               FROM acdGroup G
         INNER JOIN acdCurriculum CU
                 ON G.curriculumId = CU.curriculumId
         INNER JOIN acdCurricularComponent CC
                 ON CC.curricularcomponentid = CU.curricularcomponentid
                AND CC.curricularcomponentversion = CU.curricularcomponentversion
              WHERE G.groupId = p_groupId
    );
END; 
$BODY$ 
language plpgsql;
