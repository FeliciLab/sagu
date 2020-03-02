CREATE OR REPLACE FUNCTION baixasNoPeriodo(IN p_datainicial character varying, IN p_datafinal character varying)
  RETURNS TABLE(matricula integer, nome character varying, operador character varying, data character varying, parcela integer, vencimento character varying, origem character varying, valor character varying) AS
$BODY$
/******************************************************************************
  NAME: baixasNoPeriodo
  DESCRIPTION: Função que retorna os lançamentos de pagamentos(paymentoperation) 
  efetuados em determinado período 

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       -          Leovan Silva          1. Função criada.
  1.1       19/05/15   Nataniel I. da Silva  1. Função adicionada ao SAGU.
******************************************************************************/
DECLARE
    v_select text;
BEGIN
    v_select := 'SELECT B.personid::integer,
                        getpersonname(B.personid),
                        COALESCE(getpersonname(D.operatorId), A.username),
                        TO_CHAR(A.entrydate, ''dd/mm/yyyy'')::varchar,
                        B.parcelNumber,
                        TO_CHAR(B.maturityDate, ''dd/mm/yyyy'')::varchar,
                        (SELECT description::varchar FROM finincomesource WHERE incomesourceid = B.incomesourceid),
                        ROUND(B.value, GETPARAMETER(''BASIC'', ''GRADE_ROUND_VALUE'')::INT)::varchar
                   FROM finEntry A
             INNER JOIN finReceivableInvoice B USING (invoiceId)
              LEFT JOIN finCounterMovement C USING (invoiceId)
              LEFT JOIN finOpenCounter D USING (openCounterId)
                  WHERE A.operationId = (SELECT paymentoperation FROM finDefaultOperations LIMIT 1)::INT
                    AND A.entryDate BETWEEN datetodb(''' || p_datainicial || ''') AND datetodb(''' || p_datafinal || ''')
               ORDER BY A.datetime, 3, 1';

    RETURN QUERY EXECUTE v_select;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION baixasnoperiodo(character varying, character varying)
  OWNER TO postgres;
