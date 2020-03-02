CREATE OR REPLACE VIEW cr_fin_titulo_cheque AS (
    SELECT A.*,
           D.invoiceid as codigo_titulo,
           D.personid as codigo_pessoa,
           F.name as nome_pessoa,
           d.maturitydate as vencimento_titulo,
           D.value as valor_titulo,
           D.balance as saldo_titulo,
           dateToUser(d.maturitydate) AS vencimento_titulo_formatada
      FROM cr_fin_cheques A
INNER JOIN fincountermovementcheque B
        ON B.chequeid = A.cod_cheque
INNER JOIN fincountermovement C
        ON C.countermovementid = B.countermovementid
INNER JOIN ONLY fininvoice D
        ON D.invoiceid = C.Invoiceid
INNER JOIN ONLY basperson F
        ON F.personid = D.personid
     WHERE d.iscanceled = false);