CREATE OR REPLACE FUNCTION cr_acd_contratos_por_situacao_academica_no_periodo(p_periodId VARCHAR)
RETURNS SETOF SituacaoDoContratoNoPeriodo AS
$BODY$
BEGIN
    RETURN QUERY (
        SELECT * 
	  FROM obterSituacaoAcademicaDosContratosNoPeriodo(p_periodId, TRUE)
    );
END;
$BODY$
LANGUAGE plpgsql;
