CREATE OR REPLACE FUNCTION link(p_url varchar, p_sagu boolean, p_target varchar, p_parameters varchar)
RETURNS varchar AS
$BODY$
/*************************************************************************************
  NAME: link
  PURPOSE: 
  Função que restorna lin

  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       22/04/2015   ftomasini         1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_parametros text[];
    v_url varchar;
    v_count integer;
    v_string_length integer;
    v_split_part varchar;
    v_url_temp varchar;
      
BEGIN
    v_parametros:= string_to_array(p_parameters, ',');
    IF v_parametros[0] IS NULL
    THEN
        v_parametros[0] = p_parameters;
    END IF;
	
    v_string_length:= length(p_url);
    v_count:=1;

    IF p_sagu IS TRUE
    THEN
    	v_url:= '<a href="index.php?';
    ELSE
    	v_url:= '<a href="';
    END IF;

    LOOP
        v_split_part:= split_part(p_url, '$', v_count);
        IF v_split_part IS NOT NULL AND v_parametros[v_count] IS NOT NULL
        THEN
            v_url_temp:= replace(v_split_part::varchar,'#',v_parametros[v_count]::text);
            v_url:= v_url || v_url_temp; 
    END IF;
        IF v_count = v_string_length OR v_split_part IS NULL
        THEN
            EXIT;  -- exit loop
        END IF;

    v_count:= v_count + 1;
    END LOOP;

    RETURN v_url || '" target="_blank">' || p_target || '</a>';
END;
$BODY$
LANGUAGE plpgsql;