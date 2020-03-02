CREATE OR REPLACE FUNCTION obterCreditosDisciplinaValores(p_curricularComponentId varchar, p_curricularcomponentversion integer)
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterCreditosDisciplinaValores
  DESCRIPTION: Obtem os creditos da disciplina no formato ex.: 0.0.1.0.3. Util
    para exibir em grids e relatórios.
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN (
            SELECT ARRAY_TO_STRING(ARRAY(
            SELECT COALESCE(

                   (SELECT CRE.credits
                      FROM acdcurricularcomponentcategorycredit CRE
                     WHERE CRE.curricularcomponentid = p_curricularComponentId
                       AND CRE.curricularcomponentversion = p_curricularcomponentversion
                       AND CRE.curricularcomponentcategoryid = CAT.curricularcomponentcategoryid
                     LIMIT 1)

            , 0)
              FROM acdcurricularcomponentcategory CAT
          ORDER BY CAT.description ), '.') );
END; 
$BODY$ 
language plpgsql;
