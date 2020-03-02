CREATE OR REPLACE FUNCTION obterDescricaoDaSituacaoAcademica(p_codigo_situacao INT)
RETURNS VARCHAR AS

$BODY$
BEGIN
    RETURN (CASE p_codigo_situacao
		WHEN 1 THEN 'Não renovado'
	        WHEN 2 THEN 'Cancelamento'
	        WHEN 3 THEN 'Trancamento'
	        WHEN 4 THEN 'Transferido (S)'
	        WHEN 5 THEN 'Transferido (E)' --Contrato ativo
	        WHEN 6 THEN 'Reingresso' --Contrato ativo
	        WHEN 7 THEN 'Renovado' --Contrato ativo
	        WHEN 8 THEN 'Pré-matriculado' --Contrato ativo
	        WHEN 9 THEN 'Vestibulando' --Contrato ativo
                WHEN 10 THEN 'Concluínte'
                WHEN 11 THEN 'NDA Matriculado' --Contrato ativo
                WHEN 12 THEN 'Portador de diploma' --Contrato ativo
	        WHEN 0 THEN 'NDA' 
	    END);
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
