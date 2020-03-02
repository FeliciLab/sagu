CREATE OR REPLACE FUNCTION finresumomatriculalog(p_contractId integer, p_learningPeriodId integer, p_valor text, p_categoria varchar)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: finResumoMatriculaLog
  PURPOSE: Funcao que efetua logs de operacoes financeiras das matriculas
  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       29/07/2014 ftomasini        1. Funcao criada
**************************************************************************************/
DECLARE
    v_resumomatriculaid integer;
    result boolean;
    v_usuario varchar;
    v_resumomatricula record;
    v_numerotransacao integer;
BEGIN
    -- Ultima transacao do tipo resumo
    SELECT * INTO v_resumomatricula  
      FROM finresumomatriculalog A
INNER JOIN finresumomatricula B
     USING(resumomatriculaid)
     WHERE b.learningperiodid = p_learningPeriodId
       AND b.contractid = p_contractId 
       AND A.categoria = 'RESUMO'
  ORDER BY resumomatriculalogid DESC LIMIT 1;

    
    -- Numero da transacao atual
    SELECT count(*) INTO v_numerotransacao  
      FROM finresumomatriculalog A
INNER JOIN finresumomatricula B
     USING(resumomatriculaid)
     WHERE b.learningperiodid = p_learningPeriodId
       AND b.contractid = p_contractId 
       AND A.categoria = 'RESUMO'
       AND a.detalhe = 'INICIO';

    IF p_valor = 'INICIO'
    THEN 
        v_numerotransacao:= v_numerotransacao + 1;
    END IF;

    IF (((v_resumomatricula.detalhe IS NULL OR v_resumomatricula.detalhe ='FIM') AND p_categoria = 'RESUMO' AND p_valor = 'INICIO') --insere inicio
        OR (p_categoria != 'RESUMO' AND v_resumomatricula.detalhe = 'INICIO') --exceção
        OR (v_resumomatricula.detalhe = 'INICIO' AND p_categoria = 'RESUMO' AND p_valor = 'FIM')) --insere fim
    THEN
        -- obtem agrupador de log de matricula para um determinado periodo letivo e contrato
        SELECT INTO v_resumomatriculaid resumomatriculaid
               FROM finresumomatricula
              WHERE contractid = p_contractId
                AND learningperiodid = p_learningPeriodId;
        --obtem o usuário da ultima matricula feita para o cotrato e periodo letivo informado
        SELECT INTO v_usuario
                    a.username
               FROM acdenroll a
         INNER JOIN acdgroup b 
                 ON a.groupid = b.groupid
              WHERE a.contractid = p_contractId
                AND b.learningperiodid = p_learningPeriodId
           ORDER BY enrollid DESC LIMIT 1;

        IF v_usuario IS NULL
        THEN
            v_usuario = 'nao-identificado';
        END IF;

        --cria agrupador de logs de matricula caso nao exita
        IF v_resumomatriculaid IS NULL
        THEN
            v_resumomatriculaid:= nextval('finresumomatricula_resumomatriculaid_seq'::regclass);
            INSERT INTO finresumomatricula(resumomatriculaid, contractid, learningperiodid)VALUES(v_resumomatriculaid, p_contractId, p_learningPeriodId);
        END IF;
        
        IF ( COALESCE((SELECT FALSE FROM finresumomatriculalog A INNER JOIN finresumomatricula B USING(resumomatriculaid) WHERE A.detalhe = p_valor AND A.categoria = p_categoria AND A.transacao = v_numerotransacao AND B.contractId = p_contractId AND B.learningperiodid = p_learningPeriodId), TRUE))
        THEN
            INSERT INTO finresumomatriculalog (resumomatriculaid, datahora, detalhe, categoria, usuario,transacao) VALUES (v_resumomatriculaid, now(), p_valor, p_categoria, v_usuario,v_numerotransacao );
        END IF;
    END IF;
    RETURN TRUE;
 END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
