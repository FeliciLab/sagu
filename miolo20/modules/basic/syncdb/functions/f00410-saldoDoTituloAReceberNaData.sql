CREATE OR REPLACE FUNCTION saldoDoTituloAReceberNaData(p_invoiceId INT, p_date DATE DEFAULT NOW()::DATE)
RETURNS NUMERIC AS
$BODY$
/******************************************************************************
  NAME: saldoDoTituloNaData
  DESCRIPTION: Retorna o saldo total do título na data passada pelo parâmetro.
  Soma os valores de crédito e débito, retorna a subtração dos valores de débito
  dos valores de crédito.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       28/05/2015 Luís F. Wermann       1. Função criada.
******************************************************************************/
DECLARE
--Valor de crédito
v_valorCredito NUMERIC;

--Valor de débito
v_valorDebito NUMERIC;

BEGIN

    --Soma valor de crédito (já negativando, depois só soma)
    SELECT INTO v_valorCredito
           SUM(ET.value * -1)
 FROM ONLY finReceivableInvoice RI
INNER JOIN finEntry ET
        ON (RI.invoiceId = ET.invoiceId)
INNER JOIN finOperation OP
        ON (ET.operationId = OP.operationId)
     WHERE RI.invoiceId = p_invoiceId
       AND ET.entryDate <= p_date 
       AND OP.operationTypeId = 'C';

    --Soma valor de débito
    SELECT INTO v_valorDebito
           SUM(ET.value)
 FROM ONLY finReceivableInvoice RI
INNER JOIN finEntry ET
        ON (RI.invoiceId = ET.invoiceId)
INNER JOIN finOperation OP
        ON (ET.operationId = OP.operationId)
     WHERE RI.invoiceId = p_invoiceId
       AND ET.entryDate <= p_date 
       AND OP.operationTypeId = 'D';

    RETURN (v_valorCredito + v_valorDebito);

END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;