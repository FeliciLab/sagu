CREATE OR REPLACE FUNCTION boletosEmitidosNoPeriodo (p_datainicial character varying, p_datafinal character varying, p_bankaccountid integer) RETURNS TABLE(nossonumero character varying, matricula bigint, nome character varying, parcela integer, vencimento character varying, origem character varying, valor numeric, situacao character varying) AS $$
    DECLARE
        v_select text;
        v_datainicial character varying;
        v_datafinal  character varying;
    BEGIN

        IF p_datainicial IS NULL THEN
            SELECT INTO v_datainicial TO_CHAR(now()::date, getparameter('BASIC', 'MASK_DATE'));
        ELSE 
            v_datainicial:=p_datainicial;
        END IF;

        IF p_datafinal IS NULL THEN
            SELECT INTO v_datafinal TO_CHAR(now()::date, getparameter('BASIC', 'MASK_DATE'));
        ELSE 
            v_datafinal:=p_datafinal;
        END IF;

        IF p_bankaccountid IS NULL THEN
            v_select := 'SELECT A.ournumber,
                            B.personId,
                            getpersonname(B.personid)::varchar as nome,
                            B.parcelnumber,
                            TO_CHAR(B.maturitydate, ''dd/mm/yyyy'')::varchar,
                            (SELECT description FROM finincomesource WHERE incomesourceid = B.incomesourceid)::varchar,
                            ROUND(B.value,2),
                            CASE WHEN EXISTS (SELECT entryId FROM finEntry WHERE invoiceid = A.invoiceId AND operationid = 30)
                            THEN ''PAGO''::varchar ELSE ''ABERTO''::varchar END
                       FROM finbankinvoiceinfo A
                      INNER JOIN finreceivableinvoice B ON (B.invoiceid = A.invoiceid)
                      WHERE B.datetime::date BETWEEN TO_DATE(''' || v_datainicial || ''', ''dd/mm/yyyy'') AND TO_DATE(''' || v_datafinal || ''', ''dd/mm/yyyy'')
                      ORDER BY B.personid, B.maturitydate';
        ELSE
            v_select := 'SELECT A.ournumber,
                                B.personId,
                                getpersonname(B.personid)::varchar as nome,
                                B.parcelnumber,
                                TO_CHAR(B.maturitydate, ''dd/mm/yyyy'')::varchar,
                                (SELECT description FROM finincomesource WHERE incomesourceid = B.incomesourceid)::varchar,
                                ROUND(B.value,2),
                                CASE WHEN EXISTS (SELECT entryId FROM finEntry WHERE invoiceid = A.invoiceId AND operationid = 30)
                                THEN ''PAGO''::varchar ELSE ''ABERTO''::varchar END
                           FROM finbankinvoiceinfo A
                          INNER JOIN finreceivableinvoice B ON (B.invoiceid = A.invoiceid)
                          WHERE A.bankaccountid = ' || p_bankaccountid || '
                            AND B.datetime::date BETWEEN TO_DATE(''' || v_datainicial || ''', ''dd/mm/yyyy'') AND TO_DATE(''' || v_datafinal || ''', ''dd/mm/yyyy'')
                          ORDER BY B.personid, B.maturitydate';
        END IF;

        RETURN QUERY EXECUTE v_select;
    END;
$$ LANGUAGE plpgsql;
