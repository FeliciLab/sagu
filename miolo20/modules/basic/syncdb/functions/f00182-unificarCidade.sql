CREATE OR REPLACE FUNCTION unificarCidade(p_deCidade integer, p_paraCidade integer)
  RETURNS boolean AS
$BODY$
/*************************************************************************************
  NAME: unificarCidade
  PURPOSE: Processo de unificação de cidades.

  REVISIONS:
  Ver       Date       Author              Description
  --------- ---------- ------------------  ----------------------------------
  1.0       17/12/13   Nataniel I. Silva   1. FUNÇÃO criada.
**************************************************************************************/
DECLARE
    v_deCidadeDados RECORD;
    v_paraCidadeDados RECORD;
    v_tabela TEXT;

BEGIN
    v_tabela := 'bascity';
    
    SELECT INTO v_deCidadeDados
                *
           FROM basCity
          WHERE cityId = p_deCidade;

    SELECT INTO v_paraCidadeDados
                *
           FROM basCity
          WHERE cityId = p_paraCidade;
    
    --Verifica se as cidades são do mesmo estado.
    IF v_deCidadeDados.stateid = v_paraCidadeDados.stateid THEN
	-- Executa o processo de unificação das cidades.
	EXECUTE 'SELECT testar_valor_vazio(''' || v_tabela || ''', ''' || p_deCidade || ''', ''' || p_paraCidade || ''')';
	EXECUTE 'SELECT uniao_de_registros(''' || v_tabela || ''', ''' || p_deCidade || ''', ''' || p_paraCidade || ''')';	
    ELSE
        RAISE EXCEPTION 'As cidades devem pertencerem ao mesmo estado para serem unificadas.';
    END IF; 

    RETURN TRUE;
END;
$BODY$
  LANGUAGE plpgsql;

