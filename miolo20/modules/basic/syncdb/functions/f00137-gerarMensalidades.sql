CREATE OR REPLACE FUNCTION gerarMensalidades(p_contractid integer, p_learningperiodid integer, p_ecalouro boolean)
  RETURNS SETOF integer AS
$BODY$
/*************************************************************************************
  NAME: gerarmensalidades
  PURPOSE: Gera os títulos referentes às mensalidades

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       05/03/13   Leovan T. da Silva 1. FUNÇÃO criada.
  1.0       27/02/2014 Samuel Koch        1. Alterado função para gerar a primeira
                                             parcela sempre cheia.
  1.0       17/07/2014 Samuel Koch        1. Alterado função para não gerar parcela
                                             quando não existe matrícula.
**************************************************************************************/
DECLARE
    -- Número de parcelas nas quais o valor do semestre deve ser dividido
    NPCL integer;
    -- Número de parcelas bloqueadas
    BPCL integer;
    -- Valor a pagar pelo semestre
    VPRG numeric;
    -- Valor bloqueado
    VBLK numeric;
    -- Valor restante a pagar
    VAPG numeric;
    -- Valor da parcela
    VPCL numeric;
    -- Valor nominal do título já existente
    VTIT numeric;
    -- Valor a lançar no título já existente
    VLNC numeric;
    -- Valor previsto de parcela
    VPCLPREV numeric;
    -- Preco do curso
    PRECO record;

    --Valor que faltou colocar na última parcela
    VRESTO numeric := 0;

    -- Objeto que representa o contrato
    v_contrato acdcontract;

    -- Objeto que representa o período letivo
    v_periodo acdlearningperiod;

    -- Titulo que está sendo processado
    v_invoiceid integer;

    --Valor a ser descontado dos títulos bloqueados
    v_valor_desconto numeric;

    --Recebe o invoiceid dos titulos bloqueados 
    v_titulos record;

    --Recebe informações do titulo
    v_info_titulo RECORD;

    --Recebe o status que o título irá ser salvo na finstatusdotitulolog
    v_status_titulo INTEGER;
    
    v_valor_ja_devolvido NUMERIC;

    -- Verifica se o titulo foi remetido ao banco
    v_remessa BOOLEAN;
BEGIN
    
    PERFORM verificaresumomatriculalog(p_contractId, p_learningPeriodId);
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'INICIO', 'RESUMO');
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Aluno é calouro: ' || CASE WHEN p_ecalouro = 't' THEN 'Sim' ELSE 'Não' END , '5 - CÁLCULOS EFETUADOS');

    IF existematricula(p_contractId, p_learningPeriodId) THEN
        SELECT INTO v_contrato * FROM acdcontract WHERE contractid = p_contractId;

        SELECT INTO v_periodo * FROM acdlearningperiod WHERE learningperiodid = p_learningPeriodId;

        -- verifica se o aluno está com mais de uma turma ativa
        PERFORM verificaTurmasAtivasDoAluno(p_contractId);

        --Obtem o numero de parcelas
        NPCL := COALESCE(obterNumeroDeParcelas(p_contractId, p_learningPeriodId), 0);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de parcelas que serão criadas ou ajustadas: ' || NPCL, '2 - CONFIGURAÇÕES GERAIS');  

        --Obtem o valor total a pagar
        VPRG := COALESCE(obterValorAPagar(p_contractId, p_learningPeriodId), 0);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor total a pagar: R$ ' || ROUND(VPRG,2), '5 - CÁLCULOS EFETUADOS');
        BPCL := COALESCE(count(invoiceid),0) FROM obterTitulosBloqueados(p_contractId, p_learningPeriodId);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Número de títulos bloqueados: ' || BPCL, '5 - CÁLCULOS EFETUADOS');
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Título bloqueado: ' || a.invoiceid || ' Número da parcela: ' || a.parcelnumber || ' Valor nominal do título: R$ '|| ROUND(a.nominalvalue,2), '5 - CÁLCULOS EFETUADOS') FROM obterTitulosBloqueados(p_contractId, p_learningPeriodId) a;
        VBLK := COALESCE(sum(nominalvalue),0) FROM obterTitulosBloqueados(p_contractId, p_learningPeriodId);    
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor total bloqueado: R$ ' || ROUND(VBLK,2) || '   Soma de valores que já foram pago pelo aluno, caso o processamento tenha sido feito após o pagamento da primeira parcela o valor bloqueado vai ser o valor pago na primeira parcela.', '5 - CÁLCULOS EFETUADOS');

        VPCLPREV := 0;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: PRIMEIRA_PARCELA_INTEGRAL   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'PRIMEIRA_PARCELA_INTEGRAL') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'PRIMEIRA_PARCELA_INTEGRAL') , '3 - PARÂMETROS GERAIS');   

        -- Verifica se a primeira parcela deve ser integral para atualizar o valor programado de acordo
        IF (getParameter('FINANCE', 'PRIMEIRA_PARCELA_INTEGRAL') = 'YES') THEN
            PRECO := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_periodo.begindate);

            -- Só executa alguma coisa se o preco for fixo
            IF PRECO.valueisfixed = 't' AND VPRG < PRECO.value THEN        
                VPCLPREV := (PRECO.value / getParameter('BASIC', 'DEFAULT_PARCELS_NUMBER')::int);

                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                              'Parâmetro: DEFAULT_PARCELS_NUMBER   ' ||    
                                              'Valor: '|| getParameter('BASIC', 'DEFAULT_PARCELS_NUMBER') ||'   '||
                                              'Descrição: '|| getParameterdescription('BASIC', 'DEFAULT_PARCELS_NUMBER') , '3 - PARÂMETROS GERAIS');
   
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor previsto para cada parcela: R$'|| round(VPCLPREV,2), '5 - CÁLCULOS EFETUADOS');
                --Verifica se a primeira parcela está bloqueada, caso não esteja, atribui valor cheio nessa parcela  
                IF ( SELECT invoiceid FROM obtertitulosbloqueados(p_contractId, p_learningPeriodId) WHERE parcelnumber = 1) IS NULL
                THEN
                     PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Primeira parcela bloqueada: Sim', '5 - CÁLCULOS EFETUADOS');
                     PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor programado: R$'|| round(VPRG,2), '5 - CÁLCULOS EFETUADOS');
                END IF;
            END IF;
        END IF;

        v_valor_desconto := 0;
        v_valor_ja_devolvido :=0;
        v_valor_ja_devolvido := coalesce( (select sum(obtemvalortotaldeumaoperacao(x.invoiceid, xx.operationid)) from only finreceivableinvoice x inner join finentry xx using(invoiceid) where personid = v_contrato.personid and xx.contractid = p_contractid and xx.learningperiodid = p_learningperiodid and x.iscanceled = 'f' and xx.operationid = (SELECT operacaodevolucao FROM ONLY findefaultoperations LIMIT 1)), 0);

        --Obtém os títulos bloqueados
        FOR v_titulos IN 
        ( SELECT invoiceid 
            FROM obterTitulosBloqueados(p_contractId, p_learningPeriodId) )
        LOOP
            v_status_titulo = 2;
        --Função que retorna o valor total a ser descontado dos títulos bloqueados

            v_valor_desconto := v_valor_desconto + valorbloqueadoadesconsiderar(v_titulos.invoiceid);  
            
            raise notice 'titulo %',v_titulos.invoiceid; 
            RAISE NOTICE ' VALOR DESCONTO %', v_valor_desconto;
            RAISE NOTICE 'VALOR JA DEVOLVIDO %', v_valor_ja_devolvido;

            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                          'Parâmetro: DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES   ' ||    
                                          'Valor: '|| getParameter('FINANCE','DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES') ||'   '||
                                          'Descrição: '|| getParameter('FINANCE','DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES') , '3 - PARÂMETROS GERAIS');

            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Título : '||v_titulos.invoiceid||' Valor bloqueado a ser desconsiderado nesse título(DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES): R$'||  round(valorbloqueadoadesconsiderar(v_titulos.invoiceid),2), '5 - CÁLCULOS EFETUADOS');
            
            v_remessa := FALSE;
            SELECT INTO v_info_titulo * FROM ONLY fininvoice WHERE invoiceid = v_titulos.invoiceid;
            SELECT INTO v_remessa COUNT(*) > 0 FROM finhistoricoremessa WHERE invoiceid = v_titulos.invoiceid;

            IF v_remessa = TRUE AND (SELECT balance(v_titulos.invoiceid)) != 0.00 THEN
                --Status do titulo como enviado para remessa
                v_status_titulo = 5;
            ELSEIF v_info_titulo.maturitydate < now()::DATE AND (SELECT balance(v_titulos.invoiceid)) != 0.00 THEN
                --Status do titulo como vencido
                v_status_titulo = 4;
            ELSEIF (SELECT balance(v_titulos.invoiceid)) = 0.00 THEN
                --Status do titulo como pago
                v_status_titulo = 3;
            END IF;

            --Insere registro do título na tabela finstatusdotitulolog com status de v_status_titulo
            INSERT INTO finStatusDoTituloLog
                        (invoiceId, 
                         statusDoTituloId)
                  VALUES (v_titulos.invoiceid,
                         v_status_titulo);    

            RAISE NOTICE 'STATUS DO TITULO BLOQUEIADO %', v_status_titulo;     

        END LOOP;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor total de descontos a ser desconsiderado no novo valor a pagar: R$ '|| ROUND(v_valor_desconto,2) || '   (Se o parâmetro DESCONSIDERA_DO_VALOR_PAGO_MENSALIDADES tiver códigos de operações cadastrados, todos os lançamentos serão desconsiderados do valor bloqueado ou seja do valor pago pelo aluno. Essa funcionalidade é utilizada quando o valor de incentivo concedido ao aluno não deve ser considerado no reprocessamento de títulos.)', '5 - CÁLCULOS EFETUADOS');

        -- Calcula VAPG
        VAPG := VPRG - VBLK  ;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor a pagar(valor programado - (valor bloqueado - valor total de descontos a desconsiderar)): R$ ' || ROUND(VAPG,2), '5 - CÁLCULOS EFETUADOS');
                
        -- Se o valor bloqueado é maior que o valor a pagar, então VAPG é 0, pois não há mais nada a cobrar
        IF VAPG < 0
        THEN 
            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor bloqueado é maior que valor a pagar!' || ROUND(VAPG,2), '5 - CÁLCULOS EFETUADOS'); 
            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor a pagar: R$ ' || ROUND(VAPG,2), '5 - CÁLCULOS EFETUADOS');
            VAPG := 0; 
        END IF;


        -- Se o número de parcelas bloqueadas for maior ou igual que o número de parcelas 
        -- ou se VAPG for 0 VPCL é 0, pois significa que não há mais nada para cobrar
        -- Este teste é para evitar erros por divisão com 0
        IF BPCL >= NPCL OR VAPG = 0 THEN
            VPCL := 0;
        ELSE
            
            VPCL := ROUND((VAPG / (NPCL - BPCL)),2);

            -- Pode ser que o valor das parcelas não atinja o valor a ser pago pelo
            -- aluno, nesse caso a variável VRESTO recebe esse valor (normalmente é
            -- apenas 1 centavo) de diferença - ticket #39250
            IF ( VAPG < ROUND((NPCL - BPCL) * VPCL, 2) )
            THEN
                VRESTO := ROUND(VAPG - ROUND((NPCL - BPCL) * VPCL, 2));
            END IF;

            v_valor_desconto:= ROUND(((v_valor_desconto - v_valor_ja_devolvido) / (NPCL - BPCL)),2);
                
        END IF;

        -- Gera as parcelas
        FOR CNT IN 1..NPCL LOOP
            IF CNT NOT IN (SELECT parcelnumber FROM obterTitulosBloqueados(p_contractId, p_learningPeriodId)) 
            THEN
                IF ( getParameter('FINANCE', 'PRIMEIRA_PARCELA_INTEGRAL') = 'YES' AND
                     CNT = 1 AND
                     VPCL < VPCLPREV ) 
                THEN
                    v_invoiceid := gerarparcela(p_contractId, p_learningperiodid, CNT, VPCLPREV, p_ecalouro, v_valor_desconto);
                    --Se valor programado é maior que o valor da primeira parcela calcula o valor das parcelas restantes proporcional,
                    --cado contrário valor da parcela é 0 
                    IF ( VPCLPREV < VPRG )
                    THEN
                        VPCL := VAPG / (NPCL - BPCL);
                    ELSE
                        VPRG := 0;
                    END IF;                    
                ELSE
                    -- Aqui é somado o VRESTO, que é calculado mais acima, ao valor
                    -- da primeira parcela, depois disso recebe 0 para não somar em
                    -- mais nenhuma - ticket #39250
                    v_invoiceid := gerarparcela(p_contractId, p_learningperiodid, CNT, (VPCL + VRESTO), p_ecalouro,v_valor_desconto);
                    VRESTO := 0;
                END IF;
            END IF;
        END LOOP;

        /**
         * Verifica se o número de parcelas pagas é igual ao número de parcela bloqueadas e
         * se o valor a pagar é diferente do valor dos titulos bloqueados
         * então gera um novo único título com o valor restante #27294
         */
        VAPG := ROUND(VAPG, 2);
        IF VPRG > VBLK AND BPCL = NPCL THEN
            v_invoiceid := gerarparcela(p_contractId, p_learningperiodid, NPCL+1, VAPG, p_ecalouro, v_valor_desconto);
        END IF;

        PERFORM gerarincentivos(p_contractId, p_learningPeriodId);
    END IF;

    IF existematriculacursoferias(p_contractId, p_learningPeriodId) THEN
        PERFORM gerarferias(p_contractId, p_learningPeriodId);
    END IF;
    PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'FIM', 'RESUMO');
    -- Retorna os títulos
    RETURN QUERY ( SELECT obterTitulosDaMatricula(p_contractId, p_learningperiodid) );
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION gerarmensalidades(integer, integer, boolean)
  OWNER TO postgres;
--
