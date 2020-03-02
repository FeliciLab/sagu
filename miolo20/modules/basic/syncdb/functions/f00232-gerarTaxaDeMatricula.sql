CREATE OR REPLACE FUNCTION gerarTaxaDeMatricula(p_invoiceid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: gerartaxadematricula
  PURPOSE: Verifica se o titulo deve receber taxa de matricula
  **************************************************************************************/
DECLARE
    -- Recebe todas as taxas referente ao periodo letivo do titulo
    v_taxas RECORD;

    -- Obtem informacoes da tabela fininfotitulo
    v_info_titulo RECORD;

    -- Obtem informacoes da tabela finreceivableinvoice
    v_titulo RECORD;

    -- Recebe o valor da taxa da mensalidade
    v_valor_taxa NUMERIC;

    -- Verifica se o aluno eh calouro
    v_isfreshman BOOLEAN;

    -- Recebe o valor da taxa de matricula ja aplicada em um titulo
    v_verifica_taxa NUMERIC := 0;
    
BEGIN
    -- Obtem informacoes do titulo
    SELECT INTO v_info_titulo *
           FROM fininfotitulo
          WHERE titulo = p_invoiceid;

    SELECT INTO v_titulo * 
      FROM ONLY finreceivableinvoice
          WHERE invoiceid = p_invoiceid;

    --Verifica se o aluno eh calouro
    IF ( char_length(v_info_titulo.periodo) > 0 )
    THEN
        SELECT INTO v_isfreshman isFreshManByPeriod(v_info_titulo.contrato, v_info_titulo.periodo::VARCHAR );
    ELSE
        SELECT INTO v_isfreshman isFreshMan(v_info_titulo.contrato);
    END IF;

    --Obtem todas as taxas referentes ao periodo letivo do titulo
    FOR v_taxas IN ( SELECT * 
		       FROM finenrollfee 
                      WHERE learningperiodid = v_info_titulo.periodo_letivo )
    LOOP
        v_valor_taxa := 0;
        
	--Verifica em quantas parcelas deve inserir a taxa
	IF v_titulo.parcelnumber <= v_taxas.parcelsnumber THEN

	    -- Obtem as taxas de calouro
	    IF v_taxas.isfreshman = TRUE AND v_isfreshman = TRUE THEN
	        IF v_taxas.valueispercent = TRUE THEN
		    v_valor_taxa := (v_info_titulo.valor_nominal * v_taxas.value/100);
	        ELSE
		    v_valor_taxa := v_taxas.value;
	        END IF;
	     END IF;

	    -- Obtem as taxas de veterano
	    IF v_taxas.isfreshman = FALSE AND v_isfreshman = FALSE THEN
	        IF v_taxas.valueispercent = TRUE THEN
		    v_valor_taxa := (v_info_titulo.valor_nominal * v_taxas.value/100);
	        ELSE
		    v_valor_taxa := v_taxas.value;
	        END IF;
	    END IF; 

            v_valor_taxa := ROUND(v_valor_taxa, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::INTEGER);

            -- Verifica se existe taxa de matricula para o titulo
            SELECT INTO v_verifica_taxa COALESCE(ROUND(SUM(value), GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::INTEGER), 0)
              FROM finEntry A
             WHERE invoiceid = p_invoiceid
               AND operationid = v_taxas.operationid;
		
            v_valor_taxa := v_valor_taxa - v_verifica_taxa;   

            IF v_valor_taxa > 0 THEN

		    -- Insere um lancamento para o titulo com o valor da taxa
		    INSERT INTO finentry
				(invoiceid, 
				 operationid, 
				 entrydate, 
				 value, 
				 costcenterid, 
				 contractid, 
				 learningperiodid)
			  VALUES (p_invoiceid,
				  v_taxas.operationid,
				  now()::DATE,
				  v_valor_taxa,
				  v_titulo.costcenterid,
				  v_info_titulo.contrato,
				  v_info_titulo.periodo_letivo);

                PERFORM finresumomatriculalog(v_info_titulo.contrato, v_info_titulo.periodo_letivo, 'Inserindo lançamento de taxa de matrícula - Operação: '|| COALESCE(v_taxas.operationid::text,'Não definido'::text)|| '-'|| (SELECT description FROM finoperation WHERE operationid = v_taxas.operationid) ||'   Data do lançamento: '|| TO_CHAR(now()::date, 'dd/mm/yyyy') ||'   Valor: R$ ' || COALESCE(ROUND(v_valor_taxa::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),0) || '   Centro de custo: ' || COALESCE(v_titulo.costcenterid||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_titulo.costcenterid),'') , '7 - PARCELA-' || v_titulo.parcelnumber);
        
            ELSIF v_valor_taxa < 0 THEN
                /**
                * Caso o valor seja menor (porque foi diminuido o valor da taxa), eh necessario obter a operacao de cancelamento  
                * cadastrada junto ao cadastro da taxa, deve ser resolvido no ticket #35767
                *
                **/

                v_valor_taxa := ABS(v_valor_taxa);
                v_valor_taxa := ROUND(v_valor_taxa, getparameter('BASIC', 'REAL_ROUND_VALUE')::INTEGER);

                -- Insere lancamento com o valor e operacao de desconto
		    INSERT INTO finentry
				(invoiceid, 
				 operationid, 
				 entrydate, 
				 value, 
				 costcenterid, 
				 contractid, 
				 learningperiodid)
			  VALUES (p_invoiceid,
				  v_taxas.operacaocancelamento,
				  now()::DATE,
				  v_valor_taxa,
				  v_titulo.costcenterid,
				  v_info_titulo.contrato,
				  v_info_titulo.periodo_letivo);

                PERFORM finresumomatriculalog(v_info_titulo.contrato, v_info_titulo.periodo_letivo, 'Inserindo lançamento cancelamento da taxa de matrícula - Operação: '|| COALESCE(v_taxas.operationid::text,'Não definido'::text)|| '-'|| (SELECT description FROM finoperation WHERE operationid = v_taxas.operationid) ||'   Data do lançamento: '|| TO_CHAR(now()::date, 'dd/mm/yyyy') ||'   Valor: R$ ' || COALESCE(ROUND(v_valor_taxa::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),0) || '   Centro de custo: ' || COALESCE(v_titulo.costcenterid||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_titulo.costcenterid),'') , '7 - PARCELA-' || v_titulo.parcelnumber);

	    END IF;
        RAISE NOTICE 'VALOR DA TAXA DE MENSALIDADE % PARA O TÍTULO %', v_valor_taxa, p_invoiceid;
	END IF;
    END LOOP;
    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
