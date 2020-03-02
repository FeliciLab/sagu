CREATE OR REPLACE FUNCTION gerarFerias(p_contractid integer, p_learningperiodid integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: gerarmensalidades
  PURPOSE: Gera os títulos referentes as disciplinas de férias

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       XX/XX/XXXX XXXXXXXXXXX        1. FUNÇÃO criada.
  1.0       17/07/2014 Samuel Koch        1. Alterado função para não gerar parcela
                                             quando não existe matrícula.
**************************************************************************************/
DECLARE
    -- Valor a lançar, quando já existe título
    VLNC numeric;

    -- Valor do curso de férias
    p_value numeric;

    -- Código do título gerado
    v_invoiceid integer;

    -- Objeto que representa o contrato
    v_contrato acdcontract;

    -- Objeto que representa o período letivo
    v_periodo acdlearningperiod;

    -- Objeto que representa o preço de curso
    v_preco record;

    -- Numero de creditos em disciplinas de ferias
    v_creditos_ferias integer;

    -- Objeto que representa o título existente referente à parcela
    v_titulo finreceivableinvoice;

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
BEGIN
    SELECT INTO v_contrato * FROM acdContract WHERE contractId = p_contractId;
    SELECT INTO v_periodo * FROM acdLearningPeriod WHERE learningperiodid = p_learningPeriodId;

    v_creditos_ferias := obtemcreditoferias(p_contractId, p_learningPeriodId);

    v_politica := obtemPoliticaDoPreco(p_contractId, 0, p_learningPeriodId);

    -- Se é a primeira parcela e se a primeira deve ser à vista, o vencimento é no dia seguinte
    v_preco := obterprecoatual(v_contrato.courseid, v_contrato.courseversion, v_contrato.turnid, v_contrato.unitid, v_periodo.begindate);

    p_value := v_creditos_ferias * v_preco.valorcreditoferias;

--Validações
RAISE NOTICE 'Preço: %', v_preco.value;
RAISE NOTICE 'Política: %', v_politica;
RAISE NOTICE 'Creditos ferias: %', v_creditos_ferias;
RAISE NOTICE 'Valor: %', p_value ;

    -- Verifica se já existe um título gerado para a parcela
    SELECT INTO v_titulo * 
                       FROM finReceivableInvoice A
                      WHERE isCanceled IS FALSE
                        AND parcelNumber = 0
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

        IF v_titulo.balance <= 0 THEN RETURN NULL; END IF;

        VLNC := p_value - v_titulo.nominalvalue;        

        IF VLNC > 0 THEN
            SELECT INTO v_operacao addcurricularcomponentoperation FROM findefaultoperations LIMIT 1;
        ELSE 
            IF VLNC < 0 THEN
                VLNC := VLNC * (-1);
                SELECT INTO v_operacao cancelcurricularcomponentoperation FROM findefaultoperations LIMIT 1;
            END IF;
        END IF;

        IF VLNC > 0 THEN
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
        END IF;

        v_invoiceid := v_titulo.invoiceid;

        UPDATE fininvoice SET value = balance(v_invoiceid) WHERE invoiceid = v_invoiceid;
    -- Senao deve-se criar um novo título
    ELSE
        IF p_value > 0 THEN

            v_data_vencimento := now()::date + 1;            

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

            IF v_dias_diferenca > 0 THEN
                -- Se a primeira parcela for à vista ou se estiver configurado para adiar, o vencimento cai na próxima segunda-feira
                v_data_vencimento := v_data_vencimento + v_dias_diferenca;
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
                                    0,
                                    now()::date,
                                    v_data_vencimento,
                                    0,
                                    v_politica,
                                    getParameter('FINANCE', 'INCOME_SOURCE_ID')::integer,
                                    v_preco.bankaccountid,
                                    getParameter('BASIC', 'DEFAULT_EMISSION_TYPE_ID')::integer,
                                    v_data_vencimento);

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
                                    referencematuritydate,
                                    sem_descontos)
                            VALUES (v_invoiceid,
                                    v_contrato.personid,
                                    v_centro_de_custo,
                                    0,
                                    now()::date,
                                    v_data_vencimento,
                                    0,
                                    v_politica,
                                    getParameter('FINANCE', 'INCOME_SOURCE_ID')::integer,
                                    v_preco.bankaccountid,
                                    getParameter('BASIC', 'DEFAULT_EMISSION_TYPE_ID')::integer,
                                    v_data_vencimento,
                                    true);

            -- Obter a operação padrão de mensalidades
            SELECT INTO v_operacao monthlyfeeoperation FROM findefaultoperations LIMIT 1;

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

            -- Atualiza o atributo valor
            UPDATE fininvoice SET value = ROUND(balance(v_invoiceid)::numeric, GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::integer) WHERE invoiceid = v_invoiceid;
        END IF;
    END IF;

    RETURN v_invoiceid;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION gerarferias(integer, integer)
  OWNER TO postgres;
--
