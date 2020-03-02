CREATE OR REPLACE FUNCTION verificaprimeiraparcela(p_enrollid integer, p_period varchar)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaPrimeiraParcela
  PURPOSE: Retorna TRUE quando primeira parcela foi paga ou configuracao/modulo estiver desabilitado.
  DESCRIPTION: vide "PURPOSE".
 REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       XX/XX/2012 XXXXXXXXXX        1. FUNÇÂO criada.
  1.1       03/10/2012 Samuel Koch       1. Correção na verificaÃ§Ã£o do perÃ­odo letivo
                                            A verificaÃ§Ã£o nos lanÃ§amentos estava incorreta.
                                         2. Alterada para adicionar uma verificaÃ§Ã£o quanto a
                                            existência de títulos não cancelados e de outros 
                                            contratos.
 1.2        23/11/2012 Samuel Koch       1. Adicionado verificação quanto a existência da primeira
                                            parcela.
**************************************************************************************/
DECLARE
    v_invoice BOOLEAN;
BEGIN

    SELECT count(*) > 0 INTO v_invoice
      FROM acdEnroll A
INNER JOIN acdContract B
        ON (A.contractId = B.contractId)
INNER JOIN finEntry C
        ON (B.contractId = C.contractId)
INNER JOIN finInvoice D
        ON (C.invoiceId = D.invoiceId)
     WHERE A.enrollId = p_enrollId
       AND balance(C.invoiceId)>0
       AND D.iscanceled = 'f'
       AND D.parcelNumber = 1;

    IF v_invoice = 'f'  THEN
        return true;
    END IF;

    RETURN    GETPARAMETER('BASIC', 'MODULE_FINANCE_INSTALLED') <> 'YES'
           OR GETPARAMETER('ACADEMIC', 'GRADE_TYPING_CHECK_FIRST_PARCEL') <> 't'
           OR verificaseprimeiraparcelafoipaga(p_enrollid, p_period);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION verificaprimeiraparcela(integer, varchar)
  OWNER TO postgres;
