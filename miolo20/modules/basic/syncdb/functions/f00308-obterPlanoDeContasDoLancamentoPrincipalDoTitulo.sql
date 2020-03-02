CREATE OR REPLACE FUNCTION obterPlanoDeContasDoLancamentoPrincipalDoTitulo(p_invoiceid INT)
RETURNS VARCHAR AS
$BODY$
/*************************************************************************************
  NAME: obterPlanoDeContasDoLancamentoPrincipalDoTitulo
  PURPOSE: Retorna o plano de contas para operação de mensalidade,
           obtido pelo lançamento de mensalidade do título recebido 
           por parâmetro. Método utilizado para obter os dados contábeis
  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- -----------------         ------------------------------------
  1.0       22/12/2014 Augusto Alves da silva    1. Função criada.
**************************************************************************************/
BEGIN

    RETURN (
        SELECT plano_de_contas
          FROM obterLancamentoPrincipalDoTitulo(p_invoiceid)
    );     
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
