create or replace view cr_fin_titulos_cheques_a_receber as
SELECT a.invoiceid::varchar AS titulo_cheque,
    a.referencematuritydate as vencimento,
    a.value AS valor,
        CASE
            WHEN balance(a.invoiceid) > 0::numeric THEN balancewithpoliciesdated(a.invoiceid, now()::date)
            ELSE round(0::numeric, getparameter('BASIC'::character varying, 'REAL_ROUND_VALUE'::character varying)::integer)
        END AS areceber,
    a.nominalvalue AS valor_nominal, 'Título' as Origem
   FROM ONLY finreceivableinvoice a
  WHERE a.iscanceled IS FALSE
  AND balance(a.invoiceid) > 0


  UNION ALL

  select numero_cheque as titulo_cheque, 
         data_do_cheque::date as vencimento,
         valor_do_cheque as valor,
         valor_do_cheque as areceber,
         valor_do_cheque as valor_nominal,
         'CHEQUE' as origem
   from cr_fin_cheques
   where status_do_cheque = 'EM ABERTO' or status_do_cheque = 'DEVOLVIDO' ;
