CREATE OR REPLACE VIEW cr_fin_cheques AS (
    SELECT C.chequeid AS cod_cheque,
           C.numerocheque AS numero_cheque,
           TO_CHAR(C.data, getParameter('BASIC', 'MASK_DATE')) AS data_do_cheque,
           TO_CHAR(MC.data, getParameter('BASIC', 'MASK_DATE')) AS data_de_movimentacao,
           C.agencia AS agencia,
           C.eminente AS eminente,
           C.valorcheque AS valor_do_cheque,
           t.tipo AS tipo_do_cheque,
           obterStatusAtualDoCheque(C.chequeid) as status_do_cheque,
           C.bankid || '-' || B.description as banco_do_cheque,
           (CASE WHEN (SELECT 1 
                           FROM finmovimentacaocheque x1
                   INNER JOIN finstatuscheque
                       USING (statuschequeid)
                       WHERE x1.chequeid = c.chequeid
                         AND descricao ilike '%DEVOLVIDO%' LIMIT 1) IS NOT NULL THEN TRUE ELSE FALSE END) AS ja_foi_devolvido,
              (CASE WHEN (SELECT 1 
                           FROM finmovimentacaocheque x2
                  INNER JOIN finstatuscheque
                       USING (statuschequeid)
                       WHERE x2.chequeid = c.chequeid
                         AND descricao ilike '%REAPRESENTADO%' LIMIT 1) IS NOT NULL THEN TRUE ELSE FALSE END) AS ja_foi_representado,
           BA.bankid || '-' || BMC.description as banco_deposito,
           (SELECT MC.data
              FROM finMovimentacaoCheque MC
        INNER JOIN finStatusCheque
             USING (statusChequeId)
             WHERE MC.chequeId = C.chequeId
               AND descricao ILIKE '%DEVOLVIDO%' 
          ORDER BY MC.data ASC, 
                   MC.datetime ASC
             LIMIT 1) AS data_ultima_devolucao,
            C.cpf,
           C.data::DATE AS data_do_cheque_formato_data,
           MC.data::DATE AS data_de_movimentacao_formato_data
      FROM finCheque C
INNER JOIN finMovimentacaoCheque MC
        ON MC.chequeId = C.chequeId
INNER JOIN finStatusCheque SC
        ON SC.statusChequeId = MC.statusChequeId
INNER JOIN finBank B
        ON B.bankid = C.bankId
INNER JOIN fintipocheque t
        ON C.tipo = t.codigo
 LEFT JOIN finbankaccount BA
        ON BA.bankaccountid=MC.bankaccountid
 LEFT JOIN finBank BMC
        ON BMC.bankid = BA.bankid
     WHERE obterStatusAtualDoCheque(C.chequeid) = SC.descricao );
