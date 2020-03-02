--
CREATE OR REPLACE FUNCTION insereLancamentosTemporariosReferenteAosConvenios()
RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: insereLancamentosTemporariosReferentesAosConvenios
  PURPOSE: Insere lançamentos na tabela temporária temp_bank_movement_entries, processo de retorno bancário.
**************************************************************************************/
DECLARE
    v_invoice record;
    v_convenant record;
    v_mask_date varchar := getParameter('BASIC', 'MASK_DATE');
    v_data_hoje date := ( SELECT TO_CHAR(NOW()::DATE, 'dd/mm/yyyy') )::date;
BEGIN
    -- Percorre todos os registros do retorno bancário que possuem convênios.
    FOR v_invoice IN
        ( SELECT DISTINCT A.invoiceId,
                          A.occurrenceDate,
                          B.costCenterId,
                          A.bankReturnId,
                          A.occurrenceDate
		     FROM temp_bank_movement A
	  INNER JOIN ONLY finInvoice B
		       ON B.invoiceId = A.invoiceId
	       INNER JOIN finConvenantPerson C
		       ON C.personId = B.personId
		      AND B.referenceMaturityDate BETWEEN C.beginDate AND C.endDate
		      AND A.statusId IS NULL
		      AND A.convenantValue > 0
		      AND A.valuePaid = A.balancewithpolicies )
    LOOP
	v_convenant := NULL;

	-- Percorre todos os convênios do registro
	FOR v_convenant IN
	    ( SELECT * 
		FROM getInvoiceConvenants(v_invoice.invoiceId, TO_DATE(v_invoice.occurrenceDate, v_mask_date))
	          AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer) )
	LOOP
	    -- Insere lançamento temporário do convênio do registro.
	    INSERT INTO temp_bank_movement_entries
	                ( invoiceId, 
	                  costCenterId, 
	                  comments, 
	                  bankReturnCode, 
	                  entryDate, 
	                  operationId, 
	                  value )
	         VALUES ( v_invoice.invoiceId,
			  v_invoice.costCenterId,
			  'ARQUIVO DE RETORNO IMPORTADO EM ' || v_data_hoje,
			  v_invoice.bankReturnId,
			  COALESCE(v_invoice.occurrenceDate::DATE, v_data_hoje),
			  v_convenant.convenantoperation,
			  v_convenant.value );
	END LOOP;
    END LOOP;

    RETURN TRUE;
END;
$BODY$
LANGUAGE 'plpgsql';

