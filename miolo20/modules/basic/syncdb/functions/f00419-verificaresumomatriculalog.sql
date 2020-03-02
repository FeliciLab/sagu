CREATE OR REPLACE FUNCTION verificaresumomatriculalog(p_contractId integer, p_learningPeriodId integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: verificaresumomatriculalog
  PURPOSE: Funcao que efetua um registro de fim, caso exista um registro de log não finalizado
  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       19/06/2015 Nataniel        1. Funcao criada
**************************************************************************************/
DECLARE
    v_usuario varchar;
    v_resumomatricula record;
BEGIN
    SELECT * INTO v_resumomatricula  
      FROM finresumomatriculalog A
INNER JOIN finresumomatricula B
     USING (resumomatriculaid)
     WHERE b.learningperiodid = p_learningPeriodId
       AND b.contractid = p_contractId 
       AND A.categoria = 'RESUMO'
  ORDER BY resumomatriculalogid DESC LIMIT 1;

    IF v_resumomatricula.detalhe = 'INICIO'
    THEN
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
        
	INSERT INTO finresumomatriculalog
		    (resumomatriculaid,
		     datahora,
		     detalhe,
		     categoria,
		     usuario,
		     transacao)
	     VALUES (v_resumomatricula.resumomatriculaid,
		     NOW(),
		     'FIM',
		     'RESUMO',
		     v_usuario,
		     COALESCE(v_resumomatricula.transacao,1));
    END IF;

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
