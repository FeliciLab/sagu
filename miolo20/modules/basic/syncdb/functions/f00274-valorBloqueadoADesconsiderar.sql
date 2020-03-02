CREATE OR REPLACE FUNCTION valorBloqueadoADesconsiderar(p_invoiceid integer)
/******************************************************************************
  NAME: valorbloqueadoadesconsiderar
  PURPOSE: Retorna a soma do valor a ser descontado dos títulos bloqueados.

  REVISIONS:
  Ver       Date       Author              Description
  --------- ---------- ------------------  ----------------------------------
  1.0       16/12/13   Nataniel I. Silva   1. FUNÇÃO criada.
  1.1       06/10/14   ftomasini           1. Adicionada condição que retira 
                                              operações que ainda estão sendo usadas
                                              por convênios que estão vigentes para o
                                              título
******************************************************************************/
RETURNS numeric AS $BODY$
DECLARE
    --Recebe o valor das operações salvas no parâmetro
    v_operacao_a_desconsiderar TEXT;
    
    --Recebe o valor dos invoices
    v_value NUMERIC;

BEGIN
    v_operacao_a_desconsiderar := GETPARAMETER('FINANCE','DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES');
    v_value := 0;

    IF LENGTH(v_operacao_a_desconsiderar)>0 THEN

	SELECT INTO v_value COALESCE(sum(a.value),0) 
	       FROM finentry a
         INNER JOIN finreceivableinvoice b
              USING(invoiceid)
	      WHERE invoiceid = p_invoiceid
	        AND operationid::TEXT IN (SELECT regexp_split_to_table(GETPARAMETER('FINANCE','DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES'), E','))
                -- operação não deve estar relacionada a um convênio vigente para o título na data de vencimento do mesmo
                AND a.operationid NOT IN (SELECT convenantoperation FROM getinvoiceconvenants(p_invoiceid, b.referencematuritydate) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer));

    RETURN v_value;

    END IF;

    RETURN 0;
END;
$BODY$ 
language plpgsql;
