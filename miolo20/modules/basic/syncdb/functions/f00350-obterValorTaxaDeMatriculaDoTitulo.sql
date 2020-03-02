CREATE OR REPLACE FUNCTION obterValorTaxaDeMatriculaDoTitulo(p_invoiceid integer)
  RETURNS numeric AS
$BODY$
/*************************************************************************************
  NAME: obterValorTaxaDeMatriculaDoTitulo
  PURPOSE: Obtém o valor da taxa de matricula, caso o título tenha taxa de matrícula
  REVISIONS:
  Ver        Date       Author                      Description
  ---------- ---------- --------------------------- ----------------------------------
  1.0        06/03/2015 Nataniel I. Silva           Função criada.
**************************************************************************************/
DECLARE
    -- Recebe todas as taxas referente ao perodo letivo do ttulo
    v_taxas RECORD;
    
    -- Obtm informaes da tabela fininfotitulo
    v_info_titulo RECORD;

    -- Obtm informaes da tabela finreceivableinvoice
    v_titulo RECORD;

    -- Recebe o valor da taxa da mensalidade
    v_valor_taxa NUMERIC := 0;

    -- Verifica se o aluno  calouro
    v_isfreshman BOOLEAN;

    -- Verifica se determinada taxa já foi aplicada em um ttulo
    v_verifica_taxa NUMERIC := 0;
    
BEGIN
    -- Obtm informaes do ttulo
    SELECT INTO v_info_titulo *
           FROM fininfotitulo
          WHERE titulo = p_invoiceid;

    SELECT INTO v_titulo * 
      FROM ONLY finreceivableinvoice
          WHERE invoiceid = p_invoiceid;

    --Verifica se o aluno  calouro
    IF ( char_length(v_info_titulo.periodo) > 0 )
    THEN
        SELECT INTO v_isfreshman isFreshManByPeriod(v_info_titulo.contrato, v_info_titulo.periodo);
    ELSE
        SELECT INTO v_isfreshman isFreshMan(v_info_titulo.contrato);
    END IF;

    --Obtm todas as taxas referentes ao perodo letivo do ttulo
    FOR v_taxas IN ( SELECT * 
                       FROM finenrollfee 
                      WHERE learningperiodid = v_info_titulo.periodo_letivo
                        AND isfreshman = v_isfreshman )
    LOOP
        v_verifica_taxa := 0;

        -- Verifica se existe uma taxa de matricula para o titulo
        SELECT INTO v_verifica_taxa COALESCE(ROUND(SUM(value), GETPARAMETER('BASIC', 'REAL_ROUND_VALUE')::INTEGER), 0)
          FROM finEntry A
         WHERE invoiceid = p_invoiceid
           AND operationid = v_taxas.operationid;

        v_valor_taxa := v_valor_taxa + v_verifica_taxa;   
    END LOOP;
    RETURN v_valor_taxa;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION obterValorTaxaDeMatriculaDoTitulo(integer)
  OWNER TO postgres;
