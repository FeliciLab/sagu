CREATE OR REPLACE FUNCTION obterCreditosDisciplinaTitulos()
   RETURNS VARCHAR AS
/*************************************************************************************
  NAME: obterCreditosDisciplinaTitulos
  DESCRIPTION: Obtem os creditos da disciplina no formato ex.: T.P.C.D.L (teorico, 
    pratico, campo, distancia, laboratorio... ). Util para exibir em grids
    e relatórios.
**************************************************************************************/
$BODY$ 
DECLARE
BEGIN
    RETURN ( SELECT ARRAY_TO_STRING(ARRAY(
        SELECT curricularcomponentcategoryid
          FROM acdcurricularcomponentcategory
      ORDER BY description
    ) , '.') );
END; 
$BODY$ 
language plpgsql;
