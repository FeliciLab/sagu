CREATE OR REPLACE FUNCTION gerarparcela(p_contractid integer, p_learningperiodid integer, p_parcelnumber integer, p_value numeric, p_ecalouro boolean, p_valor_devolucao numeric)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: gerarParcela
  PURPOSE: Gera uma parcela (título) específica

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       05/03/13   Leovan T. da Silva 1. FUNÇÃO criada.
  1.0       24/02/2014 Samuel Koch        1. Alterado função para atualizar
                                             a política.
**************************************************************************************/
DECLARE
    -- Valor a lançar, quando já existe título
    VLNC numeric;

    -- Código do título gerado
    v_invoiceid integer;
    
    v_referencia_primeira_parcela boolean;
    -- Objeto que representa o contrato
    v_contrato acdcontract;
    -- Objeto que representa o período letivo
    v_periodo acdlearningperiod;
    -- Objeto que representa o título existente referente à parcela
    v_titulo finreceivableinvoice;
    -- Objeto que representa o preço de curso
    v_preco record;

    -- Data de referência - é a data de emissão do primeiro título ou a data atual
    v_data_referencia date;
    -- Operação do lançamento
    v_operacao integer;
    -- Política para o novo título
    v_politica integer;

    -- Variáveis referentes à data de vencimento
    -- Data de vencimento
    v_data_vencimento date;
    -- Dia
    v_dia_vencimento integer;
    -- Mês
    v_mes_vencimento integer;
    -- Ano
    v_ano_vencimento integer;
    -- Dia da semana
    v_dia_da_semana integer;

    -- Variáveis referentes à data de referência
    -- Dia
    v_dia_referencia integer;
    -- Mês
    v_mes_referencia integer;
    -- Ano
    v_ano_referencia integer;

    -- Dias a serem somados/subtraídos da data de vencimento qdo esta cair em finais de semana
    v_dias_diferenca integer;

    -- Data temporária (para ajustes)
    v_tmp_data date;

    -- Centro de custo
    v_centro_de_custo varchar;
    v_vencimento_referencia date;
BEGIN
    SELECT INTO v_contrato * FROM acdContract WHERE contractId = p_contractId;
    SELECT INTO v_periodo * FROM acdLearningPeriod WHERE learningperiodid = p_learningPeriodId;

    -- Verifica se já existe um título gerado para a parcela
    SELECT INTO v_titulo * 
                       FROM finReceivableInvoice A
                      WHERE isCanceled IS FALSE
                        AND parcelNumber = p_parcelnumber
                        AND EXISTS (SELECT 1
                                      FROM finentry
                                     WHERE invoiceid = A.invoiceid
                                       AND contractId = p_contractId)
                        AND EXISTS (SELECT 1
                                      FROM finentry AA
                                     INNER JOIN acdlearningperiod BB USING (learningperiodid)
                                     WHERE invoiceid = A.invoiceid
                                       AND BB.periodid = v_periodo.periodid);
    
    -- Se já existe título, utiliza o existente, apenas atualizando o valor através dos lançamentos
    IF v_titulo.invoiceid IS NOT NULL THEN

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Reprocessando parcela já criada: '|| v_titulo.invoiceid, '7 - PARCELA-' || p_parcelnumber);    
        VLNC := round((p_value + obterValorTaxaDeMatriculaDoTitulo(v_titulo.invoiceid) + coalesce( (select sum(obtemvalortotaldeumaoperacao(x.invoiceid, xx.operationid)) from only finreceivableinvoice x inner join finentry xx using(invoiceid) where personid = v_contrato.personid and xx.contractid = p_contractid and xx.learningperiodid = p_learningperiodid and x.parcelnumber = p_parcelnumber and x.iscanceled = 'f' and xx.operationid = (SELECT operacaodevolucao FROM ONLY findefaultoperations LIMIT 1)), 0)) - v_titulo.nominalvalue ,2);        

        IF VLNC > 0 
    THEN
              SELECT INTO v_operacao addcurricularcomponentoperation FROM findefaultoperations LIMIT 1;
          ELSE 
              IF VLNC < 0 
        THEN
                  VLNC := VLNC * (-1);
                  SELECT INTO v_operacao cancelcurricularcomponentoperation FROM findefaultoperations LIMIT 1;
              END IF;
          END IF;

    IF VLNC >= 0.01
    THEN
            INSERT INTO finentry 
                        (invoiceid, 
                         operationid, 
                         entrydate, 
                         value, 
                         costcenterid, 
                         contractid, 
                         learningperiodid)
                 VALUES (v_titulo.invoiceid,
                         v_operacao,
                         now()::date,
                         ROUND(VLNC::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                         v_titulo.costcenterid,
                         v_contrato.contractid,
                         v_periodo.learningperiodid);

            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Inserindo lançamento de recálculo - Operação: '|| COALESCE(v_operacao::text,'Não definido')|| '-'|| (SELECT description FROM finoperation WHERE operationid = v_operacao) ||'   Data do lançamento: '|| TO_CHAR(now()::date, 'dd/mm/yyyy') ||'   Valor: R$ ' || COALESCE(ROUND(VLNC::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),0) || '   Centro de custo: ' || COALESCE(v_titulo.costcenterid||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_centro_de_custo),'') , '7 - PARCELA-' || p_parcelnumber);   

        END IF;
        v_invoiceid := v_titulo.invoiceid;
        SELECT INTO v_politica policyid FROM fininvoice WHERE invoiceid = v_invoiceid;
        IF ( v_politica IS NULL ) 
        THEN
            v_politica := obtempoliticadopreco(p_contractId, p_parcelnumber, p_learningPeriodId);            
        END IF;
        
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Política da parcela: '|| v_politica , '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Novo valor da parcela: R$ '|| round(balance(v_invoiceid),2) , '7 - PARCELA-' || p_parcelnumber);

        UPDATE fininvoice SET value = balance(v_invoiceid), policyid = v_politica WHERE invoiceid = v_invoiceid;

        --Insere registro do título na tabela finstatusdotitulolog com status de ajustado
        INSERT INTO finStatusDoTituloLog
                    (invoiceId, 
                     statusDoTituloId)
              VALUES (v_titulo.invoiceid,
                     2);

    -- Senao deve-se criar um novo título
    ELSE
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Criando parcela nova', '7 - PARCELA-' || p_parcelnumber);        

        v_politica := obtempoliticadopreco(p_contractId, p_parcelnumber, p_learningPeriodId);
        v_data_referencia := obterDataReferenciaTitulos(p_contractId, p_learningPeriodId);

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: GERAR_TITULOS_RETROATIVOS   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'GERAR_TITULOS_RETROATIVOS') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'GERAR_TITULOS_RETROATIVOS') , '6 - PARÂMETROS PARCELA');         

        IF (getParameter('FINANCE', 'GERAR_TITULOS_RETROATIVOS') = 'YES' OR
            v_data_referencia < v_periodo.begindate)
        THEN
            v_data_referencia := v_periodo.begindate;
        END IF;

        v_preco := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_periodo.begindate);
        
        -- Se é a primeira parcela e se a primeira deve ser à vista, o vencimento é no dia seguinte ou
        -- todos os títulos estão fehado e gera um novo título com o vencimento para o dia seguinte 
        IF (p_parcelnumber = 1 AND v_preco.firstParcelatsightfreshman IS TRUE) OR (p_parcelnumber > COALESCE(obterNumeroDeParcelas(p_contractid, p_learningperiodid), 0) AND p_parcelnumber != 0) 
  THEN
            v_data_vencimento := now()::date + 1;
            
            PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Tipo: A vista', '7 - PARCELA-' ||   p_parcelnumber);        
            
        ELSE    
            -- Caso o dia de vencimento for configurado como o dia da matricula obtém o dia da matricula, caso contrário utiliza o dia de vencimento do preço
            v_dia_vencimento:= obterdiavencimento(p_contractid, p_learningperiodid);
        IF v_preco.parceltype != 'D' THEN            
            v_mes_referencia := EXTRACT(month FROM v_data_referencia + ((p_parcelnumber - 1) || ' month')::interval);
        END IF;
     
            v_ano_referencia := EXTRACT(year FROM v_data_referencia + ((p_parcelnumber - 1) || ' month')::interval);

            v_tmp_data := TO_DATE(v_dia_vencimento || '/' || v_mes_referencia || '/' || v_ano_referencia, 'dd/mm/yyyy');

            --Caso a data passar do mes de referencia antecipa até o último dia do mes
            WHILE (EXTRACT (MONTH FROM v_tmp_data) <> v_mes_referencia) LOOP
                v_dia_vencimento := v_dia_vencimento - 1;

                v_tmp_data := TO_DATE(v_dia_vencimento || '/' || v_mes_referencia || '/' || v_ano_referencia, 'dd/mm/yyyy');
            END LOOP;
            
            v_data_vencimento := TO_DATE(v_dia_vencimento || '/' || v_mes_referencia || '/' || v_ano_referencia, 'dd/mm/yyyy');
            
        END IF;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: VENCIMENTO_EM_FINAIS_DE_SEMANA   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'VENCIMENTO_EM_FINAIS_DE_SEMANA') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'VENCIMENTO_EM_FINAIS_DE_SEMANA') , '6 - PARÂMETROS PARCELA');  

        IF getParameter('FINANCE', 'VENCIMENTO_EM_FINAIS_DE_SEMANA') = 'NO'
        THEN
            -- Se a data de vencimento cair num final de semana deve-se alterar o dia de vencimento
            IF EXTRACT(DOW FROM v_data_vencimento) = 0 THEN
                v_dias_diferenca := 1;
            ELSE 
                IF EXTRACT(DOW FROM v_data_vencimento) = 6 THEN
                    v_dias_diferenca := 2;
                ELSE
                    v_dias_diferenca := 0;
                END IF;
            END IF;
        ELSE
            v_dias_diferenca := 0;
        END IF;

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: ANTECIPATED_MATURITY_DATE   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'ANTECIPATED_MATURITY_DATE') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'ANTECIPATED_MATURITY_DATE') , '6 - PARÂMETROS PARCELA');
        IF getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_VENCIMENTO'
        THEN
            SELECT INTO v_referencia_primeira_parcela COUNT(*) > 0
              FROM finreceivableinvoice A
             WHERE iscanceled IS FALSE
               AND parcelnumber <> p_parcelnumber
               AND EXTRACT(MONTH FROM v_data_vencimento) = EXTRACT(MONTH FROM A.maturityDate) 
               AND EXISTS (SELECT contractid FROM finentry
                            WHERE invoiceid = A.invoiceid
                              AND contractid = p_contractid)
               AND EXISTS (SELECT E.learningperiodid 
                             FROM finentry E
                       INNER JOIN acdlearningperiod F
                               ON E.learningperiodid = F.learningperiodid
                            WHERE invoiceid = A.invoiceid
                              AND F.periodid IN (SELECT periodid 
                                                   FROM acdlearningperiod
                                                  WHERE learningperiodid = p_learningperiodid));

            -- Se a data de geração for maior que a de vencimento e o parâmetro habilitado, deve gerar a primeira parcela para o proximo mes. Ticket #34316.
            IF ( ( -- Se primeiro título for com data de vencimento menor que hoje (vencido)
                  (now()::DATE > v_data_vencimento AND p_parcelnumber = 1 ) OR
                   -- Se já existe um título gerado para aquele mês com a parcelnumber diferente do que está sendo gerado  
                  (v_referencia_primeira_parcela = true))
                   -- Se configuração de geração de parcelas restantes for configurado para gerar apenas parcelas restantes após a data de vencimento
                  AND getParameter('FINANCE', 'GERAR_PARCELAS_RESTANTES') = 'APOS_VENCIMENTO' ) 
            THEN
                --Posterga data de vencimento para o próximo mes
                v_mes_referencia := EXTRACT(month FROM v_data_referencia + ((p_parcelnumber) || ' month')::interval);
                v_data_vencimento := TO_DATE(v_dia_vencimento || '/' || v_mes_referencia || '/' || v_ano_referencia, 'dd/mm/yyyy');
            END IF;
        END IF;  

        IF v_dias_diferenca > 0 THEN
            -- Se a primeira parcela for à vista ou se estiver configurado para adiar, o vencimento cai na próxima segunda-feira
            IF (p_parcelnumber = 1 AND v_preco.firstparcelatsight IS TRUE) OR GETPARAMETER('FINANCE', 'ANTECIPATED_MATURITY_DATE') = 'f' THEN
                v_data_vencimento := v_data_vencimento + v_dias_diferenca;
            -- Senão adianta para a sexta-feira
            ELSE
                PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Vencimento adiado para sexta feira (ANTECIPATED_MATURITY_DATE)'||v_data_vencimento, '7 - PARCELA-' || p_parcelnumber);
                v_data_vencimento := v_data_vencimento - (3 - v_dias_diferenca);
            END IF;
        END IF;

        -- Define a data de referência para a primeira parcela
        -- necessário para não aplicar convênios do período anterior
        -- quando a parcela é a vista
        v_vencimento_referencia := v_periodo.begindate;

        IF v_vencimento_referencia < v_data_vencimento OR p_parcelnumber <> 1 THEN
            v_vencimento_referencia := v_data_vencimento;
        END IF;

        -- Obtem atributos do título
        -- Centro de custo
        -- Centro de custo
        SELECT INTO v_centro_de_custo costcenterid
                                 FROM acccourseaccount
                                WHERE courseid = v_contrato.courseid
                                  AND courseversion = v_contrato.courseversion
                                  AND unitid = v_contrato.unitid;

        v_invoiceid := nextval('seq_invoiceid');
 
        -- Insere o título na fininvoice
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
                                referencematuritydate)
                        VALUES (v_invoiceid,
                                v_contrato.personid,
                                v_centro_de_custo,
                                p_parcelnumber,
                                now()::date,
                                v_data_vencimento,
                                0,
                                v_politica,
                                getParameter('FINANCE', 'INCOME_SOURCE_ID')::integer,
                                v_preco.bankaccountid,
                                getParameter('BASIC', 'DEFAULT_EMISSION_TYPE_ID')::integer,
                                v_vencimento_referencia);

        -- Insere o título na finreceivableinvoice (redundância por causa das heranças do Sagu)
        INSERT INTO finreceivableinvoice 
                               (invoiceid,
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
                                referencematuritydate)
                        VALUES (v_invoiceid,
                                v_contrato.personid,
                                v_centro_de_custo,
                                p_parcelnumber,
                                now()::date,
                                v_data_vencimento,
                                0,
                                v_politica,
                                getParameter('FINANCE', 'INCOME_SOURCE_ID')::integer,
                                v_preco.bankaccountid,
                                getParameter('BASIC', 'DEFAULT_EMISSION_TYPE_ID')::integer,
                                v_vencimento_referencia);

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Data de referência: ' || TO_CHAR(v_data_vencimento, 'dd/mm/yyyy'), '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Centro de custo: ' || v_centro_de_custo ||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_centro_de_custo), '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Data de emissão: ' || TO_CHAR(now()::date, 'dd/mm/yyyy'), '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Data de vencimento: '|| TO_CHAR(v_data_vencimento, 'dd/mm/yyyy'), '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Política: '|| v_politica ||'-'||(SELECT description FROM finpolicy where policyid = v_politica), '7 - PARCELA-' || p_parcelnumber);
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: ANTECIPATED_MATURITY_DATE   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'ANTECIPATED_MATURITY_DATE') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'ANTECIPATED_MATURITY_DATE') , '6 - PARÂMETROS PARCELA');
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: INCOME_SOURCE_ID   ' ||    
                                      'Valor: '|| getParameter('FINANCE', 'INCOME_SOURCE_ID') ||'   '||
                                      'Descrição: '|| getParameterdescription('FINANCE', 'INCOME_SOURCE_ID') , '6 - PARÂMETROS PARCELA');
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 
                                      'Parâmetro: DEFAULT_EMISSION_TYPE_ID   ' ||    
                                      'Valor: '|| getParameter('BASIC', 'DEFAULT_EMISSION_TYPE_ID') ||'   '||
                                      'Descrição: '|| getParameterdescription('BASIC', 'DEFAULT_EMISSION_TYPE_ID') , '6 - PARÂMETROS PARCELA');

        --Insere registro do título na tabela finstatusdotitulolog com status de novo
        INSERT INTO finStatusDoTituloLog
                    (invoiceId, 
                     statusDoTituloId)
              VALUES (v_invoiceid,
                     1);
         
        -- Casos especias para primeira parcela
  IF ( p_parcelnumber = 1 )
  THEN
            -- Operação da primeira parcela para calouros
            IF ( p_ecalouro )
            THEN
                v_operacao := ( SELECT enrollOperation
          FROM finDefaultOperations );
            ELSE
                -- Verifica se para o período existe uma movimentação contratual de reingresso, caso sim a operação da primeira parcela deve ser de reingresso e não de renovação.
    IF ( verificaSeAlunoEReingressanteNoPeriodo(p_contractid, p_learningperiodId) )
    THEN
        v_operacao := ( SELECT reentryOperation
              FROM finDefaultOperations );
    ELSE
        v_operacao := ( SELECT renewalOperation
              FROM finDefaultOperations );
    END IF;
            END IF;
  ELSE
            v_operacao := ( SELECT operationId
            FROM finPolicy
           WHERE policyId = v_politica );
  END IF;
 
        -- Insere um lançamento no valor da parcela       
        INSERT INTO finentry 
                        (invoiceid, 
                         operationid, 
                         entrydate, 
                         value, 
                         costcenterid, 
                         contractid, 
                         learningperiodid)
                 VALUES (v_invoiceid,
                         v_operacao,
                         now()::date,
                         ROUND(p_value::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),
                         v_centro_de_custo,
                         v_contrato.contractid,
                         v_periodo.learningperiodid);

        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Inserindo lançamento de recálculo - Operação: '|| COALESCE(v_operacao::text,'Não definido')|| '-'|| (SELECT description FROM finoperation WHERE operationid = v_operacao) ||'   Data do lançamento: '|| TO_CHAR(now()::date, 'dd/mm/yyyy') ||'   Valor: R$ ' || COALESCE(ROUND(p_value::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer),0) || '   Centro de custo: ' || COALESCE(v_centro_de_custo||'-'||(SELECT description FROM acccostcenter where costcenterid  = v_centro_de_custo),'') , '7 - PARCELA-' || p_parcelnumber);   
        PERFORM finresumomatriculalog(p_contractId, p_learningPeriodId, 'Valor da parcela: R$ ' || ROUND(balance(v_invoiceid)::numeric,2), '7 - PARCELA-' || p_parcelnumber);

        -- Atualiza o atributo valor
        UPDATE fininvoice SET value = ROUND(balance(v_invoiceid)::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer) WHERE invoiceid = v_invoiceid;
    END IF;

    IF p_valor_devolucao > 0
    THEN
        PERFORM gerardevolucaovalores(v_invoiceid, p_valor_devolucao);
    END IF;
    --Insere taxa de matrícula
    PERFORM (SELECT gerartaxadematricula(v_invoiceid));

    RETURN v_invoiceid;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
