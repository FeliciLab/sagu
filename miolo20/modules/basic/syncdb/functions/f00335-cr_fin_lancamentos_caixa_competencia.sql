CREATE OR REPLACE FUNCTION cr_fin_lancamentos_caixa_competencia(tipo integer, p_data_inicial date, p_data_final date, p_local_de_pagamento varchar, p_cod_plano_de_contas varchar, p_cod_centrodecusto varchar, p_cod_tipo_operacao_lancamento char)
   
/*************************************************************************************
NAME: cr_fin_lancamentos_caixa_competencia
PURPOSE: Utilizada para relatótios do regime de caixa e regime de competências

REGIME DE CAIXA
No Regime de Caixa, consideramos o registro dos documentos na data que foram pagos ou recebidos,
como se fosse uma conta bancária.

REGIME DE COMPETÊNCIA
No Regime de Competência, o registro do documento se dá na data que o evento aconteceu.
Este evento pode ser uma entrada (venda) ou uma saída (despesas e custos).
A contabilidade define o Regime de Competência como sendo o registro do documento na data do fato gerador (ou seja,
na data do documento, não importando quando vou pagar ou receber).
A Contabilidade utiliza o Regime de Competência, ou seja, as Receitas ou Despesas tem os valores contabilizados
dentro do mês onde ocorreu o fato Gerador, isto é, na data da realização do serviço, compra do material,
da venda, do desconto, não importando para a Contabilidade quando vou pagar ou receber, mas sim quando foi realizado o ato.

COLUNAS
username = Usuário que efetuou o lançamento';
datetime = Timestamp em que o usuário efetuou o lançamento';
ipaddress = Endereço IP do usuário que efetuou o lançamento';
cod_lancamento = Código do lançamento';
comentario_lancamento = Comentário do lançamento';
valor_lancamento = Valor do lançamento';
data_competencia_lancamento = Data de competência do lançamento';
data_referencia_lancamento = Data de refencia do lançamento';
data_efetivacao_lancamento = Data em que o lançamento foi efetivado';
cod_operacao_lancamento = Codigo da operacao do lançamento';
cod_tipo_operacao_lancamento = Indica se o lançamento é de débito ou crédito';
cod_centrodecusto = Codigo do centro de custo do lancamento';
cod_plano_de_contas = Plano de contas do lançamento';
origemlancamento = Indica se o lançamento é relacionado ao contas a receber CR, contas a pagar CP, sem vinculo SV';
cod_pessoa_ref = Código da pessoa a qual o lançamento está vinculado';
cod_titulo_ref = Código do título à qual o lançamento está vinculado';
cod_contrato_ref = Código do contrato a qual o lançamento está vinculado';
cod_incentivo_ref = Código do incentivo a qual o lançamento está vinculado';
cod_periodo_letivo_ref = Código do período letivo a qual o lançamento está vinculado';
origem varchar = Código da origem do lançamento CAIXA, BANCO, OUTRO,
cod_local_pagamento  = Código do caixa que recebeu o pagamento ou o código do banco que recebeu o pagamento, caso o pagamento foi feito via banco
local_pagamento = Local onde o título foi pago, se foi em um caixa exibe o nome do caixa, se foi um banco exibe dados da conta onde o pagamento foi recebido 
conta_bancaria = Dados da conta onde o pagamento foi recebido
cod_especie_pagamento = Código da espécie de pagamento 
especie_pagamento = Descrição da espécie de pagamento DINHEIRO, VISA, CHEQUE A VISTA
cod_tipo_especie_pagamento = Código do tipo da espécie de pagamento 
tipo_especie_pagamento = Tipo de espécie Ex: Cheque, Cartão de crédito, Banco 

CONTRAPARTIDA

PARAMETROS:
Tipo: 1 para caixa 
      2 para competência

REVISÕES:
1.0 - ftomasini - 17/09/2014 - Criação consultas
1.1 - Jamiel Spezia - 11/02/2015 - Otimização
1.2 - ftomasini - 11/02/2015  - Documentação e adicição de novas colunas

**************************************************************************************/
RETURNS TABLE(username varchar,
              datetime timestamp with time zone,
              ipaddress inet,
              cod_lancamento integer,
              comentario_lancamento text,
              valor_lancamento numeric,
              data_competencia_lancamento date,
              data_referencia_lancamento date,
              data_efetivacao_lancamento date,
              cod_operacao_lancamento integer,
              cod_tipo_operacao_lancamento char,
              descricao_operacao text,
              cod_centrodecusto varchar(30),
              cod_plano_de_contas varchar,
              origemlancamento text,
              cod_pessoa_ref bigint,
              cod_titulo_ref integer,
              cod_contrato_ref integer,
              cod_incentivo_ref integer,
              cod_periodo_letivo_ref integer,
              origem varchar,
              cod_local_pagamento integer,
              local_pagamento text,
              conta_bancaria varchar,
              cod_especie_pagamento integer,
              especie_pagamento text,
              cod_tipo_especie_pagamento integer,
              tipo_especie_pagamento varchar,
              cod_arquivo_retorno_bancario varchar,
              data_de_caixa date,
              data_vencimento_titulo date,
              codigo_solicitacao_parcela int,
              valor_titulo numeric,
              numero_da_parcela_do_titulo int,
              valor_em_aberto numeric,
              titulo_esta_em_aberto boolean,
              descricao_parcela text,
              justificativa_parcela text,
              codigo_fornecedor int,
              codigo_forma_de_pagamento int,
              descricao_forma_de_pagamento varchar,
              data_de_solicitacao date,
              nome_pessoa_ref varchar,
              centro_de_custo text,
              codigo_grupo_de_operacao character,
              grupo_de_opercao text,
              valor_com_operacao_para_saldo numeric,
              e_lancamento_principal_do_tiulo boolean) AS

$BODY$ 
DECLARE
    v_sql TEXT;
    v_filtro_data_cr TEXT;
    v_filtro_data_cp TEXT;
    v_filtro_data_sv TEXT;
    v_filtro_data_co TEXT;

BEGIN

    --Veriáveis setadas por default para o regime de caixa
    v_filtro_data_cr := 'b.entrydate';
    v_filtro_data_sv := 'a.datadecaixa';
    v_filtro_data_cp := 'a.datalancamento::date';
    v_filtro_data_co := 'MC.movementdate';
    --Obtém lançamentos registrados apenas nas tabelas de caixa e banco
    --contrapartida?

    --Caso o tipo for competência então acrescenta um NOT que será utilizado no select como um NOT EXISTS
    IF (tipo = 2) 
    THEN
        v_filtro_data_cr := 'obterDataDeCompetenciaDoLancamento(b.entryId)';
        v_filtro_data_sv := 'a.datadecompetencia';
        v_filtro_data_cp := 'a.datalancamento::date';
    END IF;

    v_sql := '-- traz todos os lançamentos do contas a receber que estao relacionados a caixa e banco 
        SELECT  b.username,
                b.datetime,
                b.ipaddress,
                b.entryid as cod_lancamento,
                b.comments as comentario_lancamento,

                -- Para casos em que o lançamento foi registrado com valor negativo, deixa o valor positivo para inverter a operação.
                round((CASE WHEN b.value < 0 
                            THEN 
                                 (b.value * -1) 
                            ELSE 
                                 b.value 
                       END), getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT) as valor_lancamento,
                obterDataDeCompetenciaDoLancamento(b.entryId) AS data_competencia_lancamento,
                a.referencematuritydate as data_referencia_lancamento,
                b.entrydate as data_efetivacao_lancamento,
                c.operationid as cod_operacao_lancamento,

                -- Para casos em que o lançamento foi registrado com valor negativo, inverte a operação.
                converteTipoDeOperacaoDoValorSeNecessario(b.value, c.operationtypeid) as cod_tipo_operacao_lancamento,
                c.description AS descricao_operacao,
                a.costcenterid as cod_centrodecusto,
                b.accountschemeid as cod_plano_de_contas,
                ''CR'' as origemlancamento,
                a.personid as cod_pessoa_ref,
                b.invoiceid as cod_titulo_ref,
                getinvoicecontract(A.invoiceid) as cod_contrato_ref, --contas a receber
                b.incentivetypeId as cod_incentivo_ref,
                b.learningperiodid as cod_periodo_letivo_ref,
                (CASE WHEN ((SELECT movementdate
                               FROM obterMovimentacaoDeCaixaDoLancamento(b.entryid, 1)) IS NOT NULL)
                      THEN
                           ''CAIXA''
                      WHEN ((SELECT occurrencedate
                             FROM obterMovimentacaoBancariaDoLancamento(b.entryid, 1)) IS NOT NULL)
                      THEN 
                           ''BANCO''
                      ELSE
                           ''NENHUM''
                 END)::VARCHAR AS origem,
                COALESCE(OC.counterId::TEXT, BM.bankId::TEXT)::INT AS cod_local_pagamento,
                COALESCE(CO.description || '' - '' || CO.counterid, BA.description || '' - '' || BC.accountnumber) AS local_pagamento,
                BC.accountnumber as conta_bancaria,
                COALESCE(SP.speciesid, NULL) as cod_especie_pagamento,
                COALESCE(SP.description,''BANCO'') as especie_pagamento,
                COALESCE(ST.speciestypeid, NULL) as cod_tipo_especie_pagamento,
                COALESCE(ST.description, NULL) as tipo_especie_pagamento,
                b.bankReturnCode AS cod_arquivo_retorno_bancario,
                (COALESCE((SELECT movementdate
                             FROM obterMovimentacaoDeCaixaDoLancamento(b.entryid, 1))::DATE,
                          (SELECT occurrencedate
                             FROM obterMovimentacaoBancariaDoLancamento(b.entryid, 1))::DATE)) AS data_de_caixa,
                a.maturityDate AS data_vencimento_titulo,
                NULL::INT AS codigo_solicitacao_parcela,
                ROUND(a.value::NUMERIC, getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT)::NUMERIC AS valor_titulo,
                a.parcelnumber AS numero_da_parcela_do_titulo,
                ROUND(a.balance::NUMERIC, getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT)::NUMERIC AS valor_em_aberto,
                (a.balance > 0) AS titulo_esta_em_aberto,
                a.comments AS descricao_parcela,
                NULL::TEXT AS justificativa_parcela,
                NULL::INT AS codigo_fornecedor,
                NULL::INT AS codigo_forma_de_pagamento,
                NULL::VARCHAR AS descricao_forma_de_pagamento,
                NULL::DATE AS data_de_solicitacao,
                getPersonName(a.personid) AS nome_pessoa_ref,
                CC.description::TEXT as centro_de_custo,
                operationgroup.operationgroupid AS codigo_grupo_de_operacao,
                operationgroup.description AS grupo_de_operacao,
                --quando for para contabilizar o que saiu entrou do caixa a lógica do valor precisa ser diferenciada
                (CASE WHEN c.operationtypeid = ''C''
                      THEN
                          B.value
                      ELSE
                          (B.value * -1)
                 END) AS valor_com_operacao_para_saldo,
                (SELECT (b.entryId = codigo_lancamento)
                   FROM obterLancamentoPrincipalDoTitulo(a.invoiceId)) AS e_lancamento_principal_do_tiulo
           FROM ONLY finreceivableinvoice a
     INNER JOIN finentry b
             ON a.invoiceid = b.invoiceid
      LEFT JOIN finoperation c
             ON b.operationid = c.operationid
      LEFT JOIN finoperationgroup operationgroup
             ON operationgroup.operationgroupid = c.operationgroupid


      LEFT JOIN finCounterMovement CM
               ON ( 
                    (
                        CM.counterMovementId = b.counterMovementId
                    ) 
                    OR 
                    (
                        CM.invoiceId = A.invoiceId
                        AND CM.value = A.value
                        AND CM.operation = C.operationTypeId 
                    )
                 )

      LEFT JOIN finOpenCounter OC
               ON OC.openCounterId = CM.openCounterId
      LEFT JOIN finCounter CO
               ON CO.counterId = OC.counterId

      LEFT JOIN fin.bankMovement BM
                ON ( BM.bankMovementId = b.bankMovementId AND BM.invoiceId = A.invoiceId )

      LEFT JOIN finBank BA
               ON BA.bankId = BM.bankId
      LEFT JOIN finbankaccount BC
             ON BA.bankid = BC.bankid 
                AND A.bankaccountid = BC.bankaccountid 
      LEFT JOIN finspecies SP 
             ON SP.speciesid = CM.speciesid
      LEFT JOIN finspeciestype ST   
             ON ST.speciestypeid = SP.speciestypeid   
     INNER JOIN accCostCenter CC
             ON CC.costCenterId = b.costCenterId            
          WHERE a.iscanceled = ''f''';
    -- Se for regime de caixa obtém apenas lançamentos que estejam relacionados a uma movimentação de caixa e banco
    IF (tipo = 1) 
    THEN
        -- obtem todos lancamentos que  estao relacionados a uma movimentacao de caixa
        v_sql :=  v_sql || ' AND ( (SELECT COUNT(counterMovementId) > 0 
                                      FROM obterMovimentacaoDeCaixaDoLancamento(b.entryid, 1))

        -- obtem todos lancamentos que  estao relacionados a uma movimentacao bancária
                     OR (SELECT COUNT(bankMovementId) > 0 
                           FROM obterMovimentacaoBancariaDoLancamento(b.entryid, 1)) )';
    END IF;
    
    v_sql := v_sql || ' AND ' || v_filtro_data_cr || ' between ''' || p_data_inicial || ''' and ''' || p_data_final || '''';

    IF (p_local_de_pagamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND upper(COALESCE(CO.description || '' - '' || CO.counterid, BA.description || '' - '' || BC.accountnumber)) like upper(''' || p_local_de_pagamento || '%'')';
    END IF;


    IF (p_cod_plano_de_contas IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND b.accountschemeid = '''|| p_cod_plano_de_contas || '''';
    END IF;

    IF (p_cod_centrodecusto IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.costcenterid = '''|| p_cod_centrodecusto || '''';
    END IF;

    IF (p_cod_tipo_operacao_lancamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND converteTipoDeOperacaoDoValorSeNecessario(b.value, c.operationtypeid) = '''|| p_cod_tipo_operacao_lancamento || '''';
    END IF;

    v_sql :=  v_sql ||  '
        --Obtém lançamentos que estão apenas em movimentações de caixa ex: transferência de valores entre caixas e sangria
        UNION ALL
        SELECT MC.username,
               MC.datetime,
               MC.ipaddress,
               NULL AS cod_lancamento,
               MC.observation AS comentario_lancamento,
               MC.value AS valor_lancamento,
               MC.movementdate::DATE AS data_competencia_lancamento,
               MC.movementdate::DATE AS data_referencia_lancamento,
               MC.movementdate::DATE AS data_efetivacao_lancamento,
               MC.operationid AS cod_operacao_lancamento,
               OP.operationtypeid AS cod_tipo_operacao_lancamento,
               OP.description AS descricao_operacao,
               MC.costcenterid AS cod_centrodecusto,
               CO.accountschemeid AS cod_plano_de_contas,
               ''CR'' AS origemlancamento,
               NULL AS cod_pessoa_ref,
               NULL AS cod_titulo_ref,
               NULL AS cod_contrato_ref,
               NULL AS cod_incentivo_ref,
               NULL AS cod_periodo_letivo_ref,
               ''CAIXA'' AS origem,
               CO.counterId AS cod_local_pagamento,
               CO.description || '' - '' || CO.counterid AS local_pagamento,
               NULL AS conta_bancaria,
               COALESCE(SP.speciesid, NULL) as cod_especie_pagamento,
               COALESCE(SP.description,''BANCO'') as especie_pagamento,
               COALESCE(ST.speciestypeid, NULL) as cod_tipo_especie_pagamento,
               COALESCE(ST.description, NULL) as tipo_especie_pagamento,
               NULL::varchar AS cod_arquivo_retorno_bancario,
               MC.movementdate::DATE AS data_de_caixa,
               NULL::DATE AS data_vencimento_titulo,
               NULL::INT AS codigo_solicitacao_parcela,
               NULL::NUMERIC AS valor_titulo,
               NULL::INT AS numero_da_parcela_do_titulo,
               NULL::NUMERIC AS valor_em_aberto,
               FALSE AS titulo_esta_em_aberto,
               NULL::TEXT AS descricao_parcela,
               NULL::TEXT AS justificativa_parcela,
               NULL::INT AS codigo_fornecedor,
               NULL::INT AS codigo_forma_de_pagamento,
               NULL::VARCHAR AS descricao_forma_de_pagamento,
               NULL::DATE AS data_de_solicitacao,
               NULL::VARCHAR AS nome_pessoa_ref,
               NULL::TEXT as centro_de_custo,
               operationgroup.operationgroupid AS codigo_grupo_de_operacao,
               operationgroup.description AS grupo_de_operacao,
                --quando for para contabilizar o que saiu entrou do caixa a lógica do valor precisa ser diferenciada
                (CASE WHEN OP.operationtypeid = ''C''
                      THEN
                          MC.value
                      ELSE
                          (MC.value * -1)
                 END) AS valor_com_operacao_para_saldo,
               FALSE AS e_lancamento_principal_do_tiulo
          FROM fincountermovement MC
    INNER JOIN finoperation OP
            ON OP.operationid = MC.operationid
    INNER JOIN finopencounter OC
            ON OC.opencounterid = MC.opencounterid
    INNER JOIN fincounter CO
            ON CO.counterid = OC.counterid
     LEFT JOIN finoperationgroup operationgroup
            ON operationgroup.operationgroupid = OP.operationgroupid
     LEFT JOIN finspecies SP 
            ON SP.speciesid = MC.speciesid
     LEFT JOIN finspeciestype ST   
            ON ST.speciestypeid = SP.speciestypeid
         WHERE MC.invoiceid is null and tituloid is NULL
         AND ' || v_filtro_data_co || ' between ''' || p_data_inicial || ''' and ''' || p_data_final || ''''; 


    IF (p_cod_plano_de_contas IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND CO.accountschemeid = '''|| p_cod_plano_de_contas || '''';
    END IF;

    IF (p_cod_centrodecusto IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND MC.costcenterid = '''|| p_cod_centrodecusto || '''';
    END IF;

    IF (p_cod_tipo_operacao_lancamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND OP.operationtypeid = '''|| p_cod_tipo_operacao_lancamento || '''';
    END IF;

    v_sql :=  v_sql || '
    -- traz todos os lançamentos sem vinculo que  estao relacionados a caixa e banco
     UNION ALL
        SELECT a.username,
               a.datetime,
               a.ipaddress,
               a.lancamentosemvinculoid as cod_lancamento,
               a.obs as comentario_lancamento,
               (round(a.valor, 2) * (CASE WHEN (DATAESTANOINTERVALO(a.datadecompetencia::date, CR.datainicial, COALESCE(CR.datafinal, ''infinity'')) AND c.operationtypeid = ''D'') -- Nesse caso é necessário testar se é uma operação de débito
	                                  THEN COALESCE(CRCC.parcentualrateio / 100, 1)
	                                  ELSE 1
	                            END))::numeric(12, 2) as valor_lancamento,
               a.datadecompetencia as data_competencia_lancamento,
               a.datadecompetencia as data_referencia_lancamento,
               a.datadecaixa as data_efetivacao_lancamento,
               a.operationid as cod_operacao_lancamento,
               c.operationtypeid as cod_tipo_operacao_lancamento,
               c.description AS descricao_operacao,
               a.costcenterid as cod_centrodecusto,
               a.accountschemeid as cod_plano_de_contas,
               ''SV'' as origemlancamento, -- sem vinculo
               NULL as cod_pessoa_ref,
               NULL as cod_titulo_ref,
               NULL as cod_contrato_ref,
               NULL as cod_incentivo_ref,
               NULL as cod_periodo_letivo_ref,
               (CASE WHEN A.bankaccountid IS NOT NULL
                      THEN
                           ''BANCO''
                      WHEN A.opencounterid IS NOT NULL
                      THEN 
                           ''CAIXA''
                      ELSE
                           ''NENHUM''
                 END)::VARCHAR AS origem,
                COALESCE(D.counterId::TEXT, F.bankid::TEXT)::INT AS cod_local_pagamento,
                COALESCE(E.description || '' - '' || E.counterid, G.description || '' - '' || f.accountnumber) AS local_pagamento,
                f.accountnumber as conta_bancaria,
                COALESCE(SP.speciesid, NULL) as cod_especie_pagamento,
                COALESCE(SP.description,''NÃO INFORMADO'') as especie_pagamento,
                COALESCE(ST.speciestypeid, NULL) as cod_tipo_especie_pagamento,
                COALESCE(ST.description, NULL) as tipo_especie_pagamento,
                NULL::varchar AS cod_arquivo_retorno_bancario,
                a.datadecaixa AS data_de_caixa,
                NULL::DATE AS data_vencimento_titulo,
                NULL::INT AS codigo_solicitacao_parcela,
                NULL::NUMERIC AS valor_titulo,
                NULL::INT AS numero_da_parcela_do_titulo,
                NULL::NUMERIC AS valor_em_aberto,
                FALSE AS titulo_esta_em_aberto,
                NULL::TEXT AS descricao_parcela,
                NULL::TEXT AS justificativa_parcela,
                NULL::INT AS codigo_fornecedor,
                NULL::INT AS codigo_forma_de_pagamento,
                NULL::VARCHAR AS descricao_forma_de_pagamento,
                NULL::DATE AS data_de_solicitacao,
                NULL::VARCHAR AS nome_pessoa_ref,
                CC.description::TEXT as centro_de_custo,
                operationgroup.operationgroupid AS codigo_grupo_de_operacao,
                operationgroup.description AS grupo_de_operacao,
                --quando for para contabilizar o que saiu entrou do caixa a lógica do valor precisa ser diferenciada
                (CASE WHEN C.operationtypeid = ''C''
                      THEN
                          A.valor
                      ELSE
                          (A.valor * -1)
                 END) AS valor_com_operacao_para_saldo,
                FALSE AS e_lancamento_principal_do_tiulo
           FROM finlancamentosemvinculo a
     INNER JOIN finoperation c
             ON a.operationid = c.operationid
      LEFT JOIN finopencounter d
             ON a.opencounterid = d.opencounterid
      LEFT JOIN fincounter e
             ON d.counterid = e.counterid
      LEFT JOIN finoperationgroup operationgroup
             ON operationgroup.operationgroupid = c.operationgroupid
      LEFT JOIN finbankaccount f
             ON a.bankaccountid = f.bankaccountid
      LEFT JOIN finbank g
             ON f.bankid = g.bankid
      LEFT JOIN finspecies SP 
             ON SP.speciesid = A.specieid
      LEFT JOIN finspeciestype ST
             ON ST.speciestypeid = SP.speciestypeid 
     INNER JOIN accCostCenter CC
             ON CC.costCenterId = a.costCenterId
      LEFT JOIN caprateio CR
             ON CR.accountSchemeId = a.accountSchemeId
      LEFT JOIN caprateiocentrodecusto CRCC
             ON CRCC.rateioid = CR.rateioid AND CRCC.costCenterId = a.costCenterId      
          WHERE  ' || v_filtro_data_sv || '  between ''' || p_data_inicial || ''' and ''' || p_data_final || '''';


    IF (p_local_de_pagamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  'AND upper(COALESCE(E.description || '' - '' || E.counterid, G.description || '' - '' || f.accountnumber)) like upper(''' || p_local_de_pagamento || '%'')'; 
    END IF;

    IF (p_cod_plano_de_contas IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.accountschemeid = '''|| p_cod_plano_de_contas || '''';
    END IF;

    IF (p_cod_centrodecusto IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.costcenterid = '''|| p_cod_centrodecusto || '''';
    END IF;

    IF (p_cod_tipo_operacao_lancamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND c.operationtypeid = '''|| p_cod_tipo_operacao_lancamento || '''';
    END IF;

    v_sql :=  v_sql ||  '
        --traz todos os lancamentos do contas a pagar que estão relacionados a caixa e banco
        UNION ALL
        SELECT a.username,
               a.datetime,
               a.ipaddress,
               a.lancamentoid as cod_lancamento,
               NULL as comentario_lancamento,
               (round(a.valor, 2) * (CASE WHEN DATAESTANOINTERVALO(a.datalancamento::date, CR.datainicial, COALESCE(CR.datafinal, ''infinity''))
	                                  THEN COALESCE(CRCC.parcentualrateio / 100, 1)
	                                  ELSE 1
	                            END))::numeric(12, 2) as valor_lancamento,
               a.datalancamento::date as data_competencia_lancamento,
               a.datalancamento::date as data_referencia_lancamento,
               a.datalancamento::date as data_efetivacao_lancamento,
               NULL as cod_operacao_lancamento,
               a.tipolancamento as cod_tipo_operacao_lancamento,
               NULL AS descricao_operacao,
               a.costcenterid as cod_centrodecusto,
               a.accountschemeid as cod_plano_de_contas,
               ''CP'' as origemlancamento, -- contas a pagar
               S.personid as cod_pessoa_ref,
               a.tituloid as cod_titulo_ref,
               NULL as cod_contrato_ref,
               NULL as cod_incentivo_ref,
               NULL as cod_periodo_letivo_ref,
               (CASE WHEN ((SELECT movementdate
                              FROM obterMovimentacaoDeCaixaDoLancamento(a.lancamentoId, 2)) IS NOT NULL)
                     THEN
                          ''CAIXA''
                     WHEN ((SELECT occurrencedate
                            FROM obterMovimentacaoBancariaDoLancamento(a.lancamentoId, 2)) IS NOT NULL)
                     THEN 
                          ''BANCO''
                     ELSE
                          ''NENHUM''
                END)::VARCHAR AS origem,
               NULL AS cod_local_pagamento,
               NULL AS local_pagamento,
               NULL as conta_bancaria,
               SS.speciesId as cod_especie_pagamento,
               SS.description as especie_pagamento,
               NULL::integer as cod_tipo_especie_pagamento,
               NULL::varchar as tipo_especie_pagamento,
               NULL::varchar AS cod_arquivo_retorno_bancario,
               (COALESCE((SELECT movementdate
                            FROM obterMovimentacaoDeCaixaDoLancamento(a.lancamentoid, 2))::DATE,
                         (SELECT occurrencedate
                            FROM obterMovimentacaoBancariaDoLancamento(a.lancamentoid, 2))::DATE)) AS data_de_caixa,
                b.vencimento AS data_vencimento_titulo,
                b.solicitacaoparcelaid AS codigo_solicitacao_parcela,
                ROUND(b.valor::NUMERIC, getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT)::NUMERIC AS valor_titulo,
                b.numeroparcela AS numero_da_parcela_do_titulo,
                ROUND(b.valoraberto::NUMERIC, getParameter(''BASIC'', ''REAL_ROUND_VALUE'')::INT)::NUMERIC AS valor_em_aberto,
                b.tituloaberto AS titulo_esta_em_aberto,
                S.dadosCompra AS descricao_parcela,
                S.justificativa AS justificativa_parcela,
                S.fornecedorId AS codigo_fornecedor,
                S.formadepagamentoid AS codigo_forma_de_pagamento,
                FDP.descricao AS descricao_forma_de_pagamento,
                S.datasolicitacao AS data_de_solicitacao,
                getPersonName(S.personId) AS nome_pessoa_ref,
                CC.description::TEXT as centro_de_custo,
                NULL AS codigo_grupo_de_operacao,
                NULL AS grupo_de_operacao,
                --quando for para contabilizar o que saiu entrou do caixa a lógica do valor precisa ser diferenciada
                (CASE WHEN A.tipolancamento = ''C''
                      THEN
                          A.valor
                      ELSE
                          (A.valor * -1)
                 END) AS valor_com_operacao_para_saldo,
                FALSE AS e_lancamento_principal_do_tiulo
           FROM caplancamento a
     INNER JOIN capTitulo b
             ON b.tituloId = a.tituloId
     INNER JOIN capSolicitacaoParcela SP
	     ON SP.solicitacaoParcelaId = b.solicitacaoParcelaId 
     INNER JOIN capSolicitacao S
	     ON S.solicitacaoId = SP.solicitacaoId
     INNER JOIN capFormaDePagamento FDP
	     ON FDP.formaDePagamentoId = S.formaDePagamentoId
      LEFT JOIN finSpecies SS
             ON SS.speciesId = (SELECT speciesid
                                  FROM obterMovimentacaoDeCaixaDoLancamento(a.lancamentoId, 2))
      LEFT JOIN accCostCenter CC
             ON CC.costCenterId = a.costCenterId
      LEFT JOIN caprateio CR
             ON CR.accountSchemeId = a.accountSchemeId
      LEFT JOIN caprateiocentrodecusto CRCC
             ON CRCC.rateioid = CR.rateioid AND CRCC.costCenterId = a.costCenterId
          WHERE ' || v_filtro_data_cp || ' between ''' || p_data_inicial || ''' and ''' || p_data_final || '''';

    IF (tipo = 1)
    THEN
        -- obtem todos lancamentos que  estao relacionados a uma movimentacao de caixa
        v_sql :=  v_sql || ' AND ( (SELECT COUNT(counterMovementId) > 0 
                                      FROM obterMovimentacaoDeCaixaDoLancamento(a.lancamentoid, 2))

        -- obtem todos lancamentos que  estao relacionados a uma movimentacao bancária
                     OR (SELECT COUNT(bankMovementId) > 0 
                           FROM obterMovimentacaoBancariaDoLancamento(a.lancamentoid, 2)) )';
    END IF;

    IF (p_cod_plano_de_contas IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.accountschemeid = '''|| p_cod_plano_de_contas || '''';
    END IF;

    IF (p_cod_centrodecusto IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.costcenterid = '''|| p_cod_centrodecusto || '''';
    END IF;

    IF (p_cod_tipo_operacao_lancamento IS NOT NULL)
    THEN
        v_sql :=  v_sql ||  ' AND a.tipolancamento = '''|| p_cod_tipo_operacao_lancamento || '''';
    END IF;

        --RAISE NOTICE '%', v_sql; --Desdocumentar somente para debug, não manter nos clientes, motivo desempenho.
        RETURN QUERY EXECUTE v_sql;
    END; 
$BODY$ 
language plpgsql IMMUTABLE;
