/*************************************************************************************
  NAME: obtemValorDoIncentivoDeContratoQueTenhaIncentivo
  PURPOSE: Obtém valor com desconto de um contrato que tenha incentivo.
  AUTOR: Luís Felipe Wermann
**************************************************************************************/
CREATE OR REPLACE FUNCTION obtemValorDoIncentivoDeContratoQueTenhaIncentivo(p_contractid integer, p_incentiveid integer)
  RETURNS float AS
$BODY$

DECLARE
    v_incentivoPercent RECORD;
    v_incentivoValorFixo RECORD;    

BEGIN
        --Ao procurar por incentivos de percent, precisa verificar todos os lançamentos que se encaixam e multiplicar pela porcentagem
           SELECT INTO v_incentivoPercent
		       ROUND(SUM((E.nominalvalue*B.value/100))::NUMERIC, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT )AS valor,
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
                   AND B.valueIsPercent = 't'
              GROUP BY B.incentiveid;

           --Quando for valor fixo é só buscar o valor concedido ao incentivo, vai ser aplicado em qualquer lançamento
           SELECT INTO v_incentivoValorFixo 
                       ROUND(B.value, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS valor
                  FROM finIncentive B
                 WHERE incentiveId = p_incentiveid
                   AND B.valueIsPercent = 'f';

    RETURN (COALESCE(v_incentivoPercent.valor, 0) + COALESCE(v_incentivoValorFixo.valor, 0));
END;
$BODY$
  LANGUAGE plpgsql;
--
