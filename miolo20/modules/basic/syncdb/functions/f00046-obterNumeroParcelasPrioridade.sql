CREATE OR REPLACE FUNCTION obterNumeroParcelasPrioridade(p_contrato int, p_pletivo int, p_price int)
RETURNS INT AS
$BODY$
/*********************************************************************************************
  NAME: obternumeroparcelasprioridade
  PURPOSE: Obtem o preco atual do curso e periodo letivo.
    Obtém o número de parcelas, que pode ser definido em trés lugares, por ordem de prioridade:
       1) No contrato do aluno;
       2) No peréodo letivo;
       3) No preéo do curso.
*********************************************************************************************/
DECLARE
BEGIN
    RETURN (CASE WHEN p_contrato > 0 THEN p_contrato
                 WHEN p_pletivo > 0 THEN p_pletivo
                 ELSE p_price END);
END
$BODY$
LANGUAGE 'plpgsql';
