CREATE OR REPLACE FUNCTION obtemChequeParaContabilizacao(p_entryid INTEGER, p_movimentacaoChequeId INTEGER)
  RETURNS TABLE(data_contabil TEXT,
                codigo_conta_contabil TEXT,
                codigo_contra_partida TEXT) AS
$BODY$
/*************************************************************************************
  NAME: obtemChequeParaContabilizacao
  PURPOSE: Retorna tabela(data_contabil, codigo_conta_contabil,codigo_contra_partida) com informações para contabilização de um lançamento que
esteja vinculado a um cheque
 REVISIONS:
  Ver       Date        Author                  Description
  --------- ----------  -----------------       ------------------------------------
  1.0       11/06/2015  Nataniel I da Silva     1. FUNÇÂO criada.
**************************************************************************************/
DECLARE
    v_entry RECORD;
    v_invoice RECORD;
    v_movimentacaoCheque RECORD;
    v_movimentacaoAnteriorCheque RECORD;
    v_cheque RECORD;
    v_chequeId INTEGER;
    v_sql TEXT;

    v_statusEmAberto INTEGER := 1;
    v_statusDepositado INTEGER := 2;
    v_statusDevolvido INTEGER := 3;
    v_statusRepassado INTEGER := 4;
    v_statusResgatado INTEGER := 5;
    v_statusReapresentado INTEGER := 6;
    v_statusSobCustodia INTEGER := 7;
    
    v_dataContabil TEXT;
    v_contaContabil TEXT;
    v_contraPartida TEXT;
BEGIN
    SELECT INTO v_entry * FROM finEntry WHERE entryId = p_entryid;
    SELECT INTO v_invoice * FROM finReceivableInvoice WHERE invoiceId = v_entry.invoiceId;

    IF v_entry.countermovementid IS NOT NULL 
    THEN
        SELECT INTO v_chequeId chequeId FROM fincountermovementcheque WHERE countermovementid = v_entry.countermovementid;
        SELECT INTO v_cheque * FROM fincheque WHERE chequeId = v_chequeId;
        
        IF v_chequeId IS NOT NULL
        THEN
           SELECT INTO v_movimentacaoCheque * FROM finmovimentacaocheque WHERE chequeId = v_chequeId AND movimentacaoChequeId = p_movimentacaoChequeId ORDER BY data DESC LIMIT 1;
           
           -- Situação em aberto 
           IF ( v_movimentacaoCheque.statuschequeid = v_statusEmAberto )
           THEN
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO'); 
               v_contraPartida := obterPlanoDeContasDoLancamentoPrincipalDoTitulo(v_invoice.invoiceid);
           
           -- Situação repassado
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusRepassado )
           THEN 
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := NULL; -- Não temos resolvido ainda a forma para obter essa informação, será resolvido no ticket #38591
               
               -- Se a situação anterior for devolvido
               IF ( obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusDevolvido )
               THEN
                   v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_DEVOLVIDO');
               ELSE
                   v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO');
               END IF;
           
           -- Situação resgatado
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusResgatado )
           THEN 
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := obterPlanoDeContasDoLancamentoPrincipalDoTitulo(v_invoice.invoiceid);
               v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO');
           
           -- Situação sob-custódia
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusSobCustodia )
           THEN 
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_SOB_CUSTODIA');
               v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO');

           -- Situação reapresentado
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusReapresentado )
           THEN
               SELECT INTO v_movimentacaoAnteriorCheque * 
                 FROM finmovimentacaocheque 
                WHERE chequeId = v_chequeId 
                  AND statuschequeid = v_statusReapresentado;
                  
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := obterPlanoDeContasDaContaBancaria(v_movimentacaoAnteriorCheque.bankaccountid);
               v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_DEVOLVIDO');
               
           -- Situação depositado 
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusDepositado )
           THEN
               v_dataContabil := v_movimentacaoCheque.data;

               -- Depositado antecipado
               IF ( (v_cheque.data > v_movimentacaoCheque.data) AND (obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusEmAberto) )
               THEN
                   v_contaContabil := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_ANTECIPADO');
                   v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO');

               -- Situação anterior a depositado é em aberto
               ELSIF ( obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusEmAberto )
               THEN
                   v_contaContabil := obterPlanoDeContasDaContaBancaria(v_movimentacaoCheque.bankaccountid);
                   v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_EM_ABERTO');

               -- Situação anterior a depositado é sob-custódia
               ELSIF ( obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusSobCustodia )
               THEN
                   v_contaContabil := obterPlanoDeContasDaContaBancaria(v_movimentacaoCheque.bankaccountid);
                   v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_SOB_CUSTODIA');
               END IF;
           
           -- Situação devolvido
           ELSIF ( v_movimentacaoCheque.statuschequeid = v_statusDevolvido )
           THEN 
               v_dataContabil := v_movimentacaoCheque.data;
               v_contaContabil := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_DEVOLVIDO');
               
               -- Situação anterior a devolvido é depositado
               IF ( obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusDepositado )
               THEN
                   SELECT INTO v_movimentacaoAnteriorCheque * 
                     FROM finmovimentacaocheque 
                    WHERE chequeId = v_chequeId 
                      AND movimentacaoChequeId < v_movimentacaoCheque.movimentacaoChequeId
                 ORDER BY movimentacaoChequeId DESC 
                    LIMIT 1;

                   -- Se foi depositado antecipado
                   IF ( (v_cheque.data > v_movimentacaoAnteriorCheque.data) AND (obtemMovimentacaoAnteriorDoCheque(v_movimentacaoAnteriorCheque.movimentacaoChequeId, v_chequeId) = v_statusEmAberto) )
                   THEN
                       v_contraPartida := GETPARAMETER('ACCOUNTANCY', 'CONTA_CONTABIL_CHEQUE_ANTECIPADO');
                   ELSE
                       v_contraPartida := obterPlanoDeContasDaContaBancaria(v_movimentacaoAnteriorCheque.bankaccountid);
                   END IF;
               
               -- Situação anterior a devolvido é repassado
               ELSIF ( obtemMovimentacaoAnteriorDoCheque(v_movimentacaoCheque.movimentacaoChequeId, v_chequeId) = v_statusRepassado )
               THEN
                   v_contraPartida := NULL; -- Não temos resolvido ainda a forma para obter essa informação, será resolvido no ticket #38591
               END IF;
           END IF;

           -- Retorna a data formatada padrão dd/mm/yyyy
           v_dataContabil := datetouser(COALESCE(v_dataContabil::DATE, v_cheque.data::DATE));
           v_contaContabil := COALESCE(v_contaContabil, getParameter('ACCOUNTANCY', 'CONTA_CONTABIL_DEBITO_PADRAO'));
           v_contraPartida := COALESCE(v_contraPartida, getParameter('ACCOUNTANCY', 'CONTA_CONTABIL_CREDITO_PADRAO'));

           v_sql = ' SELECT ''' || v_dataContabil || '''::TEXT AS data_contabil, ''' || v_contaContabil || '''::TEXT AS codigo_conta_contabil, ''' || v_contraPartida || '''::TEXT AS codigo_contra_partida';
        END IF;
    END IF;

    RETURN QUERY EXECUTE v_sql; 
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
