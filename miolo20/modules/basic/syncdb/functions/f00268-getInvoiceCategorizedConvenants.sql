CREATE OR REPLACE FUNCTION getinvoicecategorizedconvenants(p_invoiceid integer, p_date date)
  RETURNS SETOF record AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceCategorizedConvenants
  PURPOSE: Obtém os convênios a serem aplicados em um título
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       07/12/2010 Leovan            1. FUNÇÃO criada.
  1.1       16/04/2012 Moises            1. Alterado calculo de convenios
  1.2       04/06/2012 Jonas Diel        1. Altera calculo de convenios no balance
  1.3       07/11/2012 Jonas Diel        1. Adicionada regra da data de ocorrencia
                                            para finais de semana (ENABLE_BUSINESS_USER = 1)
  1.4       23/11/2012 Jonas Diel        1. Ajustes para calcular convênios somente
                                         para lançamentos com opção 
                                         'Considerar em descontos' habilitada
  1.5       26/09/2014 Jonas Diel        1. Renomeada função para getinvoicecategorizedconvenants
                                          e antigo nome utilizado em nova função que realizará todas
                                          as chamadas.
  1.6       30/09/2014 ftomasini         1. Suporte a incentivos acumulativos
                                            e não acumulativos
                                            Quando convênio não acumulativo representa
                                            um valor maior do que todos acumulativos é 
                                            concedido apenas este, caso contrário 
                                            concede os convênios acumulativos. 
                                         
**************************************************************************************/
DECLARE
    v_invoice RECORD;
    v_release RECORD;
    v_convenant RECORD;
    v_convenantCursor refcursor;
    v_numberDays INTEGER;
    v_select TEXT;
    v_applyConvenant BOOLEAN;
    v_balanceValue NUMERIC;
    v_decimals INTEGER;
    v_maturityMonth INTEGER;
    v_maturityYear INTEGER;
    v_nowMonth INTEGER;
    v_to_date DATE;
    v_to_dateMonth INTEGER;
    v_referenceMaturityDate DATE;
    v_calcNumberDays INTEGER;
    v_date DATE;
    v_td_semester NUMERIC;
    v_td_cr integer;
    v_m_cr integer;
    v_invoiceid_pp RECORD;
    v_total_incentivo NUMERIC;
          
BEGIN
    v_td_semester:=0;
    v_td_cr:=0;
    v_m_cr:=0;
    --v_invoiceid_pp:=0;
    v_date := p_date;
    v_applyConvenant := FALSE;
    IF getParameter('BASIC', 'ENABLE_BUSINESS_USER') = '1'
    THEN
        --Se a data de pagamento for numa segunda-feira é sinal que pode ter sido pago no final de semana no boleto
        IF ( EXTRACT('DOW' FROM p_date::date) = 1 ) THEN 
            v_date := p_date - interval '2 day';
        END IF;
    END IF;

    v_balanceValue := getBalanceDiscounts(p_invoiceid) - getinvoicediscountvalue(p_invoiceid, v_date);

    SELECT INTO v_decimals value::integer FROM basConfig WHERE parameter LIKE 'REAL_ROUND_VALUE';
    
    -- Dados do título
    SELECT INTO v_invoice * FROM ONLY finReceivableInvoice WHERE invoiceId = p_invoiceId;

    -- Verifica se há liberação de convênios - finRelease
    FOR v_release IN SELECT * FROM finrelease WHERE invoiceid = p_invoiceid AND v_date BETWEEN begindate AND enddate
    LOOP
        IF v_release.applyconvenant = TRUE THEN v_applyConvenant := TRUE; END IF;
    END LOOP;

    v_referenceMaturityDate := v_invoice.referenceMaturityDate::date;
    v_calcNumberDays := 0;

    IF getParameter('BASIC', 'ENABLE_BUSINESS_USER') = '1'
    THEN
        IF ( EXTRACT('DOW' FROM v_referenceMaturityDate) = 6 ) THEN
            v_referenceMaturityDate := v_referenceMaturityDate + '2 days'::interval;
            v_calcNumberDays := 2;
        ELSEIF ( EXTRACT('DOW' FROM v_referenceMaturityDate) = 0 ) THEN
            v_referenceMaturityDate := v_referenceMaturityDate + '1 day'::interval;
            v_calcNumberDays := 1;
        END IF;
    END IF;

    -- Busca os convênios a serem aplicados
    v_select := 'SELECT B.convenantid,
                        B.description,

                        -- Trata para casos onde foi definido um contrato especifico para o convenio da pessoa
                        (CASE WHEN
                             ( E.contractId IS NOT NULL
                                AND A.contractId IS NOT NULL
                                AND A.contractId = E.contractId )
                          OR ( A.contractId IS NULL )
                         THEN
                             ROUND(B.value::numeric, ' || v_decimals || ')
                         ELSE
                             0::numeric
                         END) AS value,

                        B.ispercent,
                        B.convenantOperation,
                        B.acumulativo,
                        B.todasdisciplinas,
                        (select x.contractid from finentry x where x.invoiceid = i.invoiceid and e.contractid is not null limit 1) as contractid,
                        (select x.learningperiodid from finentry x where x.invoiceid = i.invoiceid and e.learningperiodid is not null limit 1) as learningperiodid,
                        b.convenantoperation
                   FROM finconvenantperson A
             INNER JOIN finconvenant B
                     ON (B.convenantid = A.convenantid)
        INNER JOIN ONLY finInvoice I
                     ON (I.invoiceId = ' || p_invoiceId || ')
              LEFT JOIN finEntry E
                     ON ( E.entryId = ( SELECT MIN(entryId) FROM finEntry WHERE invoiceId = ' || p_invoiceId || ' AND contractId IS NOT NULL ) )                     
                  WHERE A.personid = ' || v_invoice.personid || '
AND CASE WHEN A.enddate IS NOT NULL THEN ''' || v_invoice.referencematuritydate || ''' BETWEEN A.begindate AND A.enddate ELSE ''' || v_invoice.referencematuritydate || ''' >= A.begindate END';

    IF v_select IS NOT NULL THEN
    BEGIN
    -- Se há liberação, pega todos os convênios ativos
    IF v_applyConvenant = TRUE THEN
        OPEN v_convenantCursor FOR EXECUTE v_select;
    ELSE
        -- Se a data é menor que o vencimento, verifica a aplicação dos convênios que vencem antes do vencimento
        IF v_date <= v_invoice.referenceMaturityDate THEN
            v_numberDays := v_invoice.referenceMaturityDate::date - v_date::date;

            v_select := v_select || ' AND ((B.beforeafter LIKE ''B'' AND B.daystodiscount <= ' || v_numberDays || ')
                                       OR B.beforeafter LIKE ''A'')';
        ELSE
            -- Se a data é maior que o vencimento, verifica a aplicação dos convênios que vencem depois do vencimento

            IF getParameter('FINANCE', 'DESCARTAR_DIA_31') = 'YES' THEN
                v_numberDays := p_date::date - v_referenceMaturityDate;
                v_numberDays := v_numberDays - v_calcNumberDays;

                v_maturityMonth := EXTRACT(MONTH FROM v_referenceMaturityDate);
                v_maturityYear := EXTRACT(YEAR FROM v_referenceMaturityDate);
                v_nowMonth := EXTRACT(MONTH FROM p_date::date);
                v_to_date := TO_DATE('31/' || v_maturityMonth || '/' || v_maturityYear, 'dd/mm/yyyy');
                v_to_dateMonth := EXTRACT(MONTH FROM v_to_date);

                IF ( (v_maturityMonth <> v_nowMonth) AND (v_maturityMonth = v_to_dateMonth) ) THEN
                    v_numberDays := v_numberDays - 1;
                END IF;
            ELSE
                v_numberDays := v_date::date - v_invoice.referenceMaturityDate::date;
            END IF;

            --Caso for segunda feira desconta 1 dia extendendo o desconto de domingo
            IF getParameter('FINANCE', 'EXTENDER_DESCONTOS_NA_SEGUNDA') = 'YES'
            THEN
                IF ( EXTRACT('DOW' FROM (p_date)) = 1 ) THEN --v_referenceMaturityDate+v_numberDays
                    v_numberDays := v_numberDays-1;
                END IF;
            END IF;

            v_select := v_select || ' AND B.beforeafter LIKE ''A'' AND B.daystodiscount >= ' || v_numberDays;
        END IF;

        OPEN v_convenantCursor FOR EXECUTE v_select;
    END IF;

    LOOP
        FETCH v_convenantCursor INTO v_convenant;

        IF NOT FOUND THEN
            EXIT;
        END IF;

        IF v_convenant.ispercent = TRUE 
        THEN

            --Valor de desconto proporcional ao número de créditos do semestre que o aluno está fazendo todas as disciplina
            IF (v_convenant.todasdisciplinas = TRUE)
            THEN

                v_td_semester:= COALESCE(verifica_matricula_todas_disciplinas(
                    v_convenant.contractid, 
                    v_convenant.learningperiodid),0);

                v_td_cr:= obtemcreditoscategorizados(
                    coalesce(v_convenant.contractid,0), 
                    coalesce(v_convenant.learningperiodid,0),
                    coalesce(v_td_semester::integer,0), '=');

                v_m_cr:= COALESCE(obtemcreditomatriculado(
                    v_convenant.contractid, 
                    v_convenant.learningperiodid),1);
                
                --Se a primeira parcela já foi paga pega o mesmo valor de desconto concedido nessa parcela
                IF ( verificaseprimeiraparcelarealmentefoipaga(v_convenant.contractid, v_convenant.learningperiodid) )
                THEN 
                        SELECT INTO v_invoiceid_pp *
                          FROM ONLY finreceivableinvoice A
                    INNER JOIN finEntry B
                            ON B.invoiceId = A.invoiceId
                    INNER JOIN acdLearningPeriod C
                            ON C.learningPeriodid = B.learningPeriodId
                         WHERE A.iscanceled = FALSE
                           AND A.parcelnumber = 1
                           AND B.contractId = v_convenant.contractid
                           AND C.periodId = ( SELECT periodId
                                                FROM acdLearningPeriod
                                               WHERE learningPeriodId = v_convenant.learningperiodid );

                        SELECT INTO v_total_incentivo COALESCE(SUM(obtemvalortotaldeumaoperacao(COALESCE(p_invoiceid,0), finIncentiveType.operationid)), 0)
                          FROM finincentive
                    INNER JOIN ONLY finIncentiveType
                         USING (incentivetypeid)
                         WHERE contractid = v_invoiceid_pp.contractid
                           AND p_date BETWEEN startdate AND endDate;

                    -- Obtém valor nominal da primeira parcela e faz o calculo novamente
                    v_convenant.value :=  ( ( v_td_cr * (v_invoiceid_pp.nominalvalue - v_total_incentivo)) / coalesce(v_m_cr,1) ) * (v_convenant.value/100);
                    -- ( 20 * 1373.4000 /20  ) * 0.25 =  343

                    --v_convenant.value:= obtemvalortotaldeumaoperacao(coalesce(v_invoiceid_pp.invoiceid,0), v_convenant.convenantoperation);

                --Se primeira parcela não foi paga ainda calcula o valor proporcional ao numero de creditos
                ELSE
                    v_convenant.value :=  ( ( v_td_cr * v_balanceValue ) / coalesce(v_m_cr,1) ) * (v_convenant.value/100);
                END IF;                           
            ELSE
                v_convenant.value := v_balanceValue * v_convenant.value/100;
            END IF;
        END IF;

        v_convenant.value := ROUND(v_convenant.value, v_decimals);

        RETURN NEXT v_convenant;
    END LOOP;

    CLOSE v_convenantCursor;
    END;
    END IF;

    RETURN;
END;
$BODY$
  LANGUAGE 'plpgsql';
