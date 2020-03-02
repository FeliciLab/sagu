/*************************************************************************************
  NAME: obtemValorNominalDeContratoQueTenhaIncentivo
  PURPOSE: Obtém valor nominal de um contrato que tenha incentivo.
  AUTOR: Nataniel Ingor da Silva
**************************************************************************************/
CREATE OR REPLACE FUNCTION obtemValorNominalDeContratoQueTenhaIncentivo(p_contractid integer, p_incentiveid integer)
  RETURNS float AS
$BODY$

DECLARE
    v_incentivo RECORD;
BEGIN
           SELECT INTO v_incentivo ROUND(SUM(E.nominalvalue)::NUMERIC, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT ) AS valorNominal,
                       B.incentiveid	               
		  FROM acdContract A
            INNER JOIN finIncentive B
                    ON A.contractId = B.contractId
       INNER JOIN ONLY finIncentiveType C
                    ON C.incentiveTypeId = B.incentiveTypeId
	    INNER JOIN finEntry D
		    ON D.contractId = A.contractId
       INNER JOIN ONLY finreceivableinvoice E
		    ON E.invoiceId = D.invoiceId

	         WHERE A.contractId = p_contractid
                   AND E.iscanceled IS FALSE
	           AND E.maturitydate > B.startDate 
	           AND E.maturitydate < B.endDate
		   AND ( D.operationId IN (SELECT monthlyfeeoperation FROM findefaultoperations)
			 OR D.operationId IN (SELECT enrollOperation FROM findefaultoperations)
			 OR D.operationId IN (SELECT renewalOperation FROM findefaultoperations) )
                   -- Verifica se a data de vencimento do título está dentro das datas do período letivo
                   AND ( E.maturitydate > (SELECT A.beginDate
				        FROM acdlearningperiod A
				       WHERE learningperiodid = D.learningperiodid) 
		   AND E.maturitydate <= (SELECT A.endDate
				       FROM acdlearningperiod A
				      WHERE A.learningperiodid = D.learningperiodid))
                   AND B.incentiveId = p_incentiveid
              GROUP BY B.incentiveid;

    RETURN v_incentivo.valorNominal;
END;
$BODY$
  LANGUAGE plpgsql;
--
