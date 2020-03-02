CREATE OR REPLACE FUNCTION formatarVariavelObservacaoOperacao(p_variavel VARCHAR, p_entryId INT)
RETURNS VARCHAR AS
$BODY$
/*************************************************************************************
  NAME: formatarVariavelObservacaoOperacao
  PURPOSE: Formata/substitui a variável na observação da operação corretamente.

  REVISIONS:
  Ver       Date       Author                    Description
  --------- ---------- ----------------------    ------------------------------------
  1.0       06/03/2015 Luís Felipe Wermann       1. Função criada.
**************************************************************************************/
DECLARE

    v_dadosLancamento RECORD;
    v_retorno VARCHAR;

BEGIN

    v_retorno := ' NÃO FOI POSSÍVEL CONVERTER A VARIÁVEL ' || p_variavel;

    --Obtém dados
    SELECT INTO v_dadosLancamento
           (CASE WHEN B.numeroNotaFiscal IS NULL
                THEN
                    (SELECT BB.numeroNotaFiscal
                       FROM finReceivableInvoice CC
                 INNER JOIN finEntry AA
                         ON (CC.invoiceId = AA.invoiceId)
                 INNER JOIN finNfe BB
                         ON (AA.entryId = BB.rpsId)
                      WHERE BB.estaCancelada IS FALSE
                        AND CC.invoiceId = C.invoiceId
                        AND AA.entryid = A.entryid)
                ELSE
                    B.numeroNotaFiscal
            END) AS numero_nota_fiscal,
           C.personId AS codigo_pessoa,
           D.name AS nome_pessoa 
      FROM finEntry A
 LEFT JOIN finNfe B
        ON (A.entryId = B.rpsId)
INNER JOIN finReceivableInvoice C
        ON (C.invoiceId = A.invoiceId)
INNER JOIN ONLY basPhysicalPerson D
        ON (C.personId = D.personId)
     WHERE A.entryId = p_entryId;

     IF ( p_variavel = 'X_NUMERO_NOTA_FISCAL' )
     THEN
         v_retorno := v_dadosLancamento.numero_nota_fiscal;
     END IF;

     IF ( p_variavel = 'X_NOME_ALUNO' )
     THEN
         v_retorno := v_dadosLancamento.nome_pessoa;
     END IF;

     RETURN v_retorno;
        
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
