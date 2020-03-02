CREATE OR REPLACE FUNCTION gerardevolucaovalores(p_invoiceid integer, p_valor numeric)
  RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: gerarDevolucaoValores
  PURPOSE: Gera lançamentos para os valores a serem desconsiderados

  REVISIONS:
  Ver       Date       Author              Description
  --------- ---------- ------------------  ----------------------------------
  1.1       06/10/14   ftomasini           1. Adicionada condição que retira 
                                              operações que ainda estão sendo usadas
                                              por convênios que estão vigentes para o
                                              título
******************************************************************************/
DECLARE
    -- Obtém informações da tabela finreceivableinvoice
    v_titulo RECORD;
    v_info_titulo RECORD;
    v_operacao INTEGER;
BEGIN
    SELECT INTO v_info_titulo *
           FROM fininfotitulo
          WHERE titulo = p_invoiceid;

    SELECT INTO v_titulo * 
      FROM ONLY finreceivableinvoice
          WHERE invoiceid = p_invoiceid;

    SELECT INTO v_operacao COALESCE(operacaodevolucao, enrolloperation)
      FROM ONLY findefaultoperations LIMIT 1;


    -- Insere um lançamento para o valor que deve ser devolvido
    INSERT INTO finentry
                (invoiceid, 
                operationid, 
                entrydate, 
                value, 
                costcenterid, 
                contractid, 
                learningperiodid)
        VALUES (p_invoiceid,
                v_operacao,
                now()::DATE,
                p_valor,
                v_titulo.costcenterid,
                v_info_titulo.contrato,
                v_info_titulo.periodo_letivo);

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
