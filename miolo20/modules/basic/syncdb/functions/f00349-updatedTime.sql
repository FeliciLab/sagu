CREATE OR REPLACE FUNCTION updatedTime(integer)
  RETURNS character varying AS
$BODY$
SELECT CASE WHEN balance($1) = 0
            THEN '(x)'
            ELSE 
                (SELECT ( (NOW()::DATE) - (SELECT maturityDate 
                                             FROM finReceivableInvoice
                                            WHERE invoiceId = $1) )
                 )::varchar
            END $BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION updatedtime(integer)
  OWNER TO postgres;
