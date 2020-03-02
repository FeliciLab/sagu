CREATE OR REPLACE FUNCTION cr_fin_titulos_financiamento(p_periodid varchar, p_contractid integer, p_incentivetypeid integer)
  RETURNS TABLE (  tipo_incentivo_cod integer,
                   tipo_incentivo_descricao text,
                   tipo_incentivo_operacao_cod integer, 
                   tipo_incentivo_precisa_autorizacao boolean, 
                   tipo_incentivo_envia_titulos boolean,           
                   tipo_incentivo_esta_cancelado boolean,           
                   tipo_incentivo_gera_creditos boolean,        
                   tipo_incentivo_operacao_acrescimo integer,       
                   tipo_incentivo_operacao_reembolso integer,
                   tipo_incentivo_aplica_descontos boolean,        
                   tipo_incentivo_permite_aditamento boolean,        
                   tipo_incentivo_percentual_aproveitamento_para_renovacao integer,        
                   incentivo_cod integer,    
                   incentivo_contrato integer,       
                   incentivo_data_inicio date,         
                   incentivo_data_fim date,            
                   incentivo_e_percentual boolean,         
                   incentivo_valor numeric,                    
                   incentivo_patrocinador_cod integer,           
                   incentivo_financiamento_aglutinado boolean,        
                   incentivo_centro_de_custo varchar,            
                   incentivo_data_cancelamento date,         
                   incentivo_foi_aditado boolean,         
                   incentivo_data_pagamento_valor_financiado date, 
                   incentivo_tipo_concessao char(1),           
                   incentivo_prioridade integer,               
                   incentivo_opcao_de_ajuste char(1),         
                   pessoa_cod bigint,
                   pessoa_nome varchar,
                   titulo_cod integer) AS
$BODY$
/*************************************************************************************
  NAME: cr_fin_financiamento
  PURPOSE: Obtém todos os alunos que tem determinado financiamento no período informado
           com o valor financiado e o valor já pago pelo financiador
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       30/09/2014 ftomasini         1.0 Função criada.
                                         
**************************************************************************************/
DECLARE
    v_select TEXT;
    v_filter_contract varchar;
    v_filter_incentivetypeid varchar;
    
BEGIN
    v_filter_contract:= '';
    v_filter_incentivetypeid:= '';

    IF p_contractid IS NOT NULL
    THEN
        v_filter_contract:='AND c.contractid = '''|| p_contractid || '''';
    END IF;

    IF p_incentivetypeid IS NOT NULL
    THEN
        v_filter_incentivetypeid:='AND b.incentivetypeid = '''|| p_incentivetypeid ||'''';
    END IF;

    v_select:= 'SELECT w1.incentivetypeid,
                       w1.description,
                       w1.operationid, 
                       w1.needadjustauthorization, 
                       w1.sendinvoices,           
                       w1.isextinct,           
                       w1.generatecredits,        
                       w1.paymentoperation,       
                       w1.repaymentoperation,
                       w1.applydiscounts,        
                       w1.aditarincentivo,        
                       w1.percentrenovacao,        
                       w1.incentiveid,    
                       w1.contractid,       
                       w1.startdate,         
                       w1.enddate,            
                       w1.valueispercent,         
                       w1.value,                    
                       w1.supporterid,           
                       w1.agglutinate,        
                       w1.costcenterid,            
                       w1.cancellationdate,         
                       w1.incentivoaditado,         
                       w1.pagamentovalorfinanciado, 
                       w1.concedersobre,           
                       w1.prioridade,               
                       w1.opcaodeajuste,
                       w1.personid,
                       w1.name,
                       w1.invoiceid
                  FROM (SELECT a.incentivetypeid,
                               a.description,
                               a.operationid, 
                               a.needadjustauthorization, 
                               a.sendinvoices,           
                               a.isextinct,           
                               a.generatecredits,        
                               a.paymentoperation,       
                               a.repaymentoperation,
                               a.applydiscounts,        
                               a.aditarincentivo,        
                               a.percentrenovacao,        
                               b.incentiveid,    
                               b.contractid,       
                               b.startdate,         
                               b.enddate,                  
                               b.valueispercent,         
                               b.value,                    
                               b.supporterid,           
                               b.agglutinate,        
                               b.costcenterid,            
                               b.cancellationdate,         
                               b.incentivoaditado,         
                               b.pagamentovalorfinanciado, 
                               b.concedersobre,           
                               b.prioridade,               
                               b.opcaodeajuste,     
                               f.personid,
                               f.name,
                               g.invoiceid
                          FROM finloan a
                    INNER JOIN finincentive b
                            ON a.incentivetypeid = b.incentivetypeid
                    INNER JOIN acdcontract c 
                            ON (b.contractid = c.contractid)
                    INNER JOIN acdlearningperiod d
                            ON (d.courseid = c.courseid
                                AND d.courseversion = c.courseversion
                                AND d.turnid = c.turnid
                                AND d.unitid = c.unitid
                                AND OVERLAPS (b.startdate, b.enddate, d.begindate, d.enddate))
                    -- titulos alunos 
                    INNER JOIN only fininvoice e
                            ON e.personid = c.personid
                    -- titulos do financiador
                    INNER JOIN only fininvoice g
                            ON g.invoiceid = (SELECT x1.invoiceid
                                                    FROM fininvoice x1
                                              INNER JOIN finentry x2 
                                                      ON x1.invoiceid = x2.invoiceid 
                                                   WHERE x2.titulodereferencia IS NOT NULL
                                                     AND (e.invoiceid = x2.titulodereferencia) LIMIT 1)
                    INNER JOIN only basperson f 
                            ON c.personid = f.personid        
                         WHERE d.periodid = '''|| p_periodid ||'''
                                '|| v_filter_contract ||
                                    v_filter_incentivetypeid ||'
                           AND e.competencydate BETWEEN d.begindate AND d.enddate
                           AND e.iscanceled = false
                           --verifica apenas titulos que estao relacionados ao incentivo selecionado
                           AND e.invoiceid IN (SELECT x4.invoiceid
                           	                     FROM only fininvoice x4
                                           INNER JOIN finentry x5
                                                   ON x4.invoiceid = x5.invoiceid
                           	                    WHERE x5.incentivetypeid = a.incentivetypeid)) w1
                      GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30';       

    RETURN QUERY EXECUTE v_select;
END;
$BODY$
  LANGUAGE 'plpgsql';
