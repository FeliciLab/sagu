CREATE OR REPLACE FUNCTION gerarTitulosDoIncentivo(p_personid integer, p_invoiceid integer, p_entryid integer, p_incentiveid integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: gerarIncentivos
  PURPOSE: gera os títulos a terceiros referente a incentivos de patrocinador e financiador
  PARAMETERS:
  p_personid - pessoa para quem será registrado o título de cobrança
  p_invoiceid - titulo de referência do aluno
  p_entryid - lançamemto do incentivo no título do aluno
  p_incetiveid - código do incentivo da pessoa

  REVISIONS:
  Ver       Date         Author                  Description
  --------- ----------   -----------------      ------------------------------------
  1.0       17/10/2014   Nataniel I. da Silva    1. Funcao criada.
**************************************************************************************/
DECLARE
    -- Título da pessoa
    v_titulo_pessoa RECORD;
    
    -- Lançamento do incentivo para a pessoa
    v_lancamento_incentivo RECORD;

    -- Título do financiador
    v_invoiceid_financiador INTEGER;
    
    -- Lançamento do financiador
    v_entryid_financiador INTEGER;

    -- Incentivo do tipo financiamento
    v_financiamento RECORD;

    -- Incentivo do tipo patrocinador
    v_patrocinador RECORD;

    -- Operação de cobrança cadastrada no incentivo
    v_operacao_cobranca INTEGER;

    -- Obtém informações do título, caso o mesmo seja gerado para o próprio aluno
    v_info_titulo RECORD;

    -- Data de vencimento do novo título gerado
    v_vencimento DATE;

    -- Incentivetypeid do incentivo
    v_incentivetypeid INTEGER;
 
    -- Obtém informações do incentivo da pessoa
    v_info_incentivo RECORD;

    -- Obtém a data de vencimento título financiador/patrocinador aglutinado
    v_data_vencimento TEXT;

    -- Flag aglutinado da tabela fininvoice/finreceivableinvoice
    v_aglutinado BOOLEAN;

    -- Dia de vencimento do título do financiador
    v_dia_vencimento INTEGER;

BEGIN
    v_operacao_cobranca := 0;
    v_invoiceid_financiador := 0;   
    v_aglutinado := FALSE;  
    
    -- Obtém informações do título da pessoa que recebeu incentivo
    SELECT INTO v_titulo_pessoa * 
           FROM ONLY fininvoice 
          WHERE invoiceid = p_invoiceid 
            AND iscanceled IS FALSE;

    -- Obtém informações do lançamento da pessoa que recebeu incentivo
    SELECT INTO v_lancamento_incentivo * 
           FROM finentry 
          WHERE entryid = p_entryid;

    -- Verifica o tipo de incentivo é de patrocinador
    SELECT INTO v_patrocinador A.* 
           FROM finSupport A
     INNER JOIN finIncentive B
             ON (A.incentivetypeid = B.incentivetypeid) 
          WHERE B.incentiveid = p_incentiveid;
        
    -- Verifica o tipo de incentivo é de financiamento
    SELECT INTO v_financiamento A.* 
           FROM finLoan A
     INNER JOIN finIncentive B 
             ON (A.incentivetypeid = B.incentivetypeid)
          WHERE B.incentiveid = p_incentiveid;

    SELECT INTO v_info_incentivo * 
           FROM finincentive 
          WHERE incentiveid = p_incentiveid;


    -- Verifica para o título da pessoa se já existe um título de financiador/patrocinador não cancelado    
    IF v_titulo_pessoa.tituloDeReferencia IS NOT NULL
    THEN
        SELECT INTO v_invoiceid_financiador invoiceid 
          FROM ONLY finInvoice 
              WHERE invoiceid = v_titulo_pessoa.tituloDeReferencia 
                AND iscanceled IS FALSE;
    END IF;


    IF v_invoiceid_financiador IS NULL OR v_invoiceid_financiador = 0
    THEN
        v_vencimento := v_titulo_pessoa.maturitydate;
   
        -- Se for um financiamento e o aluno é o próprio financiador, verifica se já existe um título com o campo tituloDeReferecia preenchido
	IF v_titulo_pessoa.personid = p_personid AND v_financiamento.incentivetypeid IS NOT NULL
	THEN
            SELECT INTO v_info_titulo * 
              FROM ONLY fininvoice 
                  WHERE personid = p_personid 
                    AND iscanceled IS FALSE 
                    AND tituloDeReferencia IS NOT NULL 
                    AND maturityDate = v_info_incentivo.pagamentoValorFinanciado
                  LIMIT 1;
            
            IF v_info_titulo.tituloDeReferencia IS NOT NULL
	    THEN
 	        --v_invoiceid_financiador := v_info_titulo.tituloDeReferencia;
		-- Verifica se o título não está cancelado
		SELECT INTO v_invoiceid_financiador invoiceid 
		  FROM ONLY finInvoice 
		      WHERE invoiceid = v_info_titulo.tituloDeReferencia 
		        AND iscanceled IS FALSE;
	    ELSE
                -- Obtém a data de vencimento do título vinculada ao cadastro do incentivo da pessoa finincentive.pagamentoValorFinanciado
                IF v_info_incentivo.pagamentoValorFinanciado IS NULL AND v_info_incentivo.agglutinate IS FALSE
		THEN
		    RAISE EXCEPTION 'É necessário cadastrar a data do valor financiado, para a pessoa % no incentivo %, no menu Financeiro::Cadastro::Incentivo.',p_personid, v_financiamento.incentivetypeid;
		END IF;      
         
		v_vencimento := v_info_incentivo.pagamentoValorFinanciado; 
                v_titulo_pessoa.parcelnumber := 0;

	    END IF;
	END IF;

        IF v_info_incentivo.agglutinate IS TRUE
        THEN        
            v_invoiceid_financiador := NULL;
            v_aglutinado := TRUE;

            -- Verifica se existe um título para a pessoa financiador/patrocinador com a flag aglutinado
            SELECT INTO v_invoiceid_financiador invoiceid 
              FROM ONLY finInvoice 
                  WHERE personid = p_personid 
                    AND iscanceled IS FALSE
                    AND aglutinado IS TRUE
                    AND balance(invoiceid) > 0
                    AND ((maturityDate >= NOW()::DATE
                    AND (EXTRACT(MONTH FROM maturityDate) = EXTRACT(MONTH FROM v_titulo_pessoa.maturitydate)))
                     OR maturityDate = v_info_incentivo.pagamentoValorFinanciado)
                  LIMIT 1;


	    IF v_info_incentivo.pagamentoValorFinanciado IS NULL
	    THEN
		    -- Obtém o mês e o ano de vencimento do título do financiador/patrocinador a partir da data de vencimento do título do aluno
		    -- e obtém o dia de vencimento do parâmetro
		    v_dia_vencimento := GETPARAMETER('BASIC','MATURITY_DAY');
		    v_data_vencimento := v_dia_vencimento || '/' || (SELECT EXTRACT(MONTH FROM v_titulo_pessoa.maturitydate)) || '/' || (SELECT EXTRACT(YEAR FROM v_titulo_pessoa.maturitydate));
		    v_vencimento := datetodb(v_data_vencimento);

		     --Caso a data passar do mes de referencia antecipa até o último dia do mes
		    WHILE (EXTRACT (MONTH FROM v_vencimento) <> EXTRACT(MONTH FROM v_titulo_pessoa.maturitydate)) 
		    LOOP
			v_dia_vencimento := v_dia_vencimento - 1;

			v_data_vencimento := v_dia_vencimento || '/' || (SELECT EXTRACT(MONTH FROM v_titulo_pessoa.maturitydate)) || '/' || (SELECT EXTRACT(YEAR FROM v_titulo_pessoa.maturitydate));
			v_vencimento := datetodb(v_data_vencimento);
		    END LOOP;
	    END IF;
        END IF;


	IF v_invoiceid_financiador IS NULL OR v_invoiceid_financiador = 0
	THEN
	    -- Cria um novo título para o financiador com o valor nominal do incentivo
	    v_invoiceid_financiador := nextval('seq_invoiceid');	    

	    INSERT INTO fininvoice (invoiceid,
	                            personid,
	                            costcenterid,
	                            parcelnumber,
	                            emissiondate,
	                            maturitydate,
	                            value,
	                            policyid,
	                            incomesourceid,
	                            bankaccountid,
	                            emissiontypeid,
	                            referencematuritydate,
                                    aglutinado)
	                    VALUES (v_invoiceid_financiador,
	                            p_personid,
	                            v_titulo_pessoa.costcenterid,
	                            v_titulo_pessoa.parcelnumber,
	                            v_titulo_pessoa.emissiondate,
	                            v_vencimento,
	                            v_lancamento_incentivo.value,
	                            v_titulo_pessoa.policyid,
	                            v_titulo_pessoa.incomesourceid,
	                            v_titulo_pessoa.bankaccountid,
	                            v_titulo_pessoa.emissiontypeid,
	                            v_vencimento,
                                    v_aglutinado);

	    INSERT INTO finreceivableinvoice (invoiceid,
		    	                     personid,
			                     costcenterid,
			                     parcelnumber,
			                     emissiondate,
			                     maturitydate,
			                     value,
			                     policyid,
			                     incomesourceid,
			                     bankaccountid,
			                     emissiontypeid,
			                     referencematuritydate,
                                             aglutinado)
			              VALUES (v_invoiceid_financiador,
			                     p_personid,
			                     v_titulo_pessoa.costcenterid,
			                     v_titulo_pessoa.parcelnumber,
			                     v_titulo_pessoa.emissiondate,
			                     v_vencimento,
			                     v_lancamento_incentivo.value,
			                     v_titulo_pessoa.policyid,
			                     v_titulo_pessoa.incomesourceid,
			                     v_titulo_pessoa.bankaccountid,
			                     v_titulo_pessoa.emissiontypeid,
			                     v_vencimento,
                                             v_aglutinado);
	END IF;

	-- Atualiza a coluna titulodereferencia com o título criado para o financiador         
        UPDATE fininvoice 
           SET tituloDeReferencia = v_invoiceid_financiador
         WHERE invoiceid = p_invoiceid;

    END IF;

    -- Obtém a operação de cobrança para registrar o lançamento para o financiador
    IF v_patrocinador.incentivetypeid IS NOT NULL
    THEN
	v_operacao_cobranca := v_patrocinador.collectionoperationid;
	v_incentivetypeid := v_patrocinador.incentivetypeid; 
        RAISE NOTICE 'INCENTIVO DO TIPO PATROCINADOR';
    ELSIF v_financiamento.incentivetypeid IS NOT NULL
    THEN
        v_operacao_cobranca := v_financiamento.collectionoperationid;
	v_incentivetypeid := v_financiamento.incentivetypeid;
        RAISE NOTICE 'INCENTIVO DO TIPO FINANCIADOR';
    END IF;
	
    IF v_operacao_cobranca IS NOT NULL AND v_operacao_cobranca != 0 
    THEN    
	-- Exclui os lançamento
	DELETE FROM finentry 
	      WHERE invoiceid = v_invoiceid_financiador 
		AND operationid = v_operacao_cobranca
                AND titulodereferencia = p_invoiceid;
	        
	-- Insere um lançamento com o valor do incentivo, no título do financiador
        v_entryid_financiador := nextval('seq_entryid');

        INSERT INTO  finentry
                            (entryid,
			     invoiceid,
                             operationid,
                             entrydate,
                             value,
                             costcenterid,
                             incentivetypeid,
			     titulodereferencia)
                     VALUES (v_entryid_financiador,
                             v_invoiceid_financiador,
                             v_operacao_cobranca,
                             now()::date,
                             ROUND(v_lancamento_incentivo.value::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                             v_lancamento_incentivo.costcenterid,
                             v_incentivetypeid,
			     p_invoiceid);

        
        -- Atualiza o valor do título
        UPDATE fininvoice SET value = ROUND(balance(v_invoiceid_financiador)::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer) WHERE invoiceid = v_invoiceid_financiador;
    
        RAISE NOTICE 'FINANCIADOR % TITULO % LANCAMENTO % VALOR % OPERAÇÃO %', p_personid,v_invoiceid_financiador, v_entryid_financiador,v_lancamento_incentivo.value,v_operacao_cobranca;
        
	RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
