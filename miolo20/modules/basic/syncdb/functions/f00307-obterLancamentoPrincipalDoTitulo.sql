-- Somente contempla contas a receber.
CREATE OR REPLACE FUNCTION obterLancamentoPrincipalDoTitulo(p_invoiceId INT)
RETURNS TABLE (
    codigo_lancamento INT,
    plano_de_contas VARCHAR
) AS
$BODY$
BEGIN
    RETURN QUERY(
        SELECT E.entryId AS codigo_lancamento,
               COALESCE((CASE WHEN char_length(CA.accountSchemeId) = 0
                              THEN
                                   NULL
                              ELSE
                                   CA.accountSchemeId
                         END),
                        (CASE WHEN char_length(O.accountSchemeId) = 0
                              THEN
                                   NULL
                              ELSE
                                   O.accountSchemeId
                         END)) AS plano_de_contas
     FROM ONLY finInvoice I
    INNER JOIN finEntry E
            ON E.invoiceId = I.invoiceId
    INNER JOIN finOperation O
         USING (operationId)
    INNER JOIN finBankAccount BA
         USING (bankAccountId)
     LEFT JOIN acdContract CON
	    ON CON.contractid = E.contractid
     LEFT JOIN accCourseAccount CA
            ON (CA.courseId,
                CA.courseVersion,
                CA.unitId) = (CON.courseId,
                              CON.courseVersion,
                              CON.unitId)
	 WHERE I.invoiceId = p_invoiceid
	   AND (O.operationId IN (SELECT UNNEST(ARRAY[
                                                   monthlyfeeoperation, --MENSALIDADES
                                                   enrollOperation, --MATRÍCULA
                                                   renewalOperation, --RENOVAÇÃO
                                                   reemissaodetitulooperation, --REEMISSÃO DE TÍTULOS
                                                   (SELECT operationId
                                                      FROM fin.invoiceNegociationConfig
                                                     LIMIT 1) --NEGOCIAÇÃO
                                                ])
				    FROM finDefaultOperations)
            OR O.operationId IN (SELECT operationId
                                   FROM finCondicoesDePagamentoPerfil) -- NOVA TELA DE NEGOCIAÇÃO
            OR O.operationId IN (SELECT operationId
                                   FROM prcPrecoCurso)) --PRECO DO CURSO DO PEDEAGÓGICO
         LIMIT 1
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
