CREATE OR REPLACE FUNCTION cr_fin_lancamentos_contasreceber_competencia(p_dataini DATE, p_datafim DATE)
RETURNS SETOF DadosContabeis AS
$BODY$
/*************************************************************************************
  NAME: cr_fin_lancamentos_contasreceber_competencia
  PURPOSE: Retorna informações contábeis de lançamentos a receber, 
           pelas datas de competência.
           Recebe os filtros de data inicial, final

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ----------------------    ------------------------------------
  1.0       22/12/2014 Augusto Alves da silva    1. Função criada.
**************************************************************************************/
BEGIN
    RETURN QUERY (
        SELECT *
          FROM cr_fin_lancamentos_contasreceber(p_dataini, p_datafim, 'CO')
    );
END;
$BODY$
LANGUAGE plpgsql;
