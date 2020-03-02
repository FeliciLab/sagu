CREATE OR REPLACE FUNCTION bi_balance(integer, date) RETURNS numeric
    LANGUAGE sql
    AS $_$
SELECT SUM( CASE WHEN A.operationTypeId = 'D' THEN ( 1 * B.value )
                 WHEN A.operationTypeId = 'C' THEN ( -1 * B.value )
            END
          )
  FROM finOperation A,
       finEntry B
 WHERE A.operationId = B.operationId
   AND B.invoiceId = $1
   AND B.entrydate <= $2
$_$;
