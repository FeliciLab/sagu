/**********************************************************************
  NAME: obterDataInicialNotas
  PURPOSE: Obtem a ultima data de nota excluída.
  DESCRIPTION: Função utilizada para obter a data do último registro de nota excluída.
***********************************************************************/
CREATE OR REPLACE FUNCTION obterDataInicialNotas(p_degreeid integer, p_enrollid integer)
RETURNS timestamp AS $$
DECLARE
    v_data timestamp;
BEGIN
    
    IF ( GETPARAMETER('ACADEMIC', 'CONSIDER_HIGHER_PUNCTUATION_DEGREE') = 't' ) THEN
    BEGIN
        SELECT INTO v_data recorddate FROM acddegreeenroll WHERE degreeid = p_degreeid AND enrollid = p_enrollid AND note IS NULL ORDER BY recorddate DESC LIMIT 1;
        
        IF ( v_data IS NULL ) THEN
            SELECT INTO v_data '01-01-1900 00:00:00'::timestamp;
        END IF;
        
    END;
    ELSE
        SELECT INTO v_data '01-01-1900 00:00:00'::timestamp;
    END IF;
    
    RETURN v_data;
    
END;
$$ LANGUAGE plpgsql;
