DROP TYPE IF EXISTS TYPE_BIBLIOGRAPHY_DATA;

CREATE TYPE TYPE_BIBLIOGRAPHY_DATA AS (
    controlnumber int,
    fieldid varchar,
    subfieldid varchar,
    content varchar
);

CREATE OR REPLACE FUNCTION SEA_BIBLIOGRAPHY_DATA ( p_controlnumber int, p_content varchar, p_libraryunit int, p_tags varchar ) 
RETURNS SETOF TYPE_BIBLIOGRAPHY_DATA as $$
DECLARE
    v_line TYPE_BIBLIOGRAPHY_DATA;
    v_select text;
    
BEGIN
       
    v_select  = 'SELECT DISTINCT controlnumber
                           FROM gtcmaterial 
                         WHERE subfieldid <> ''#''';
  
 
    IF p_controlnumber IS NOT NULL
    THEN
        v_select = v_select || ' AND controlnumber = ' || p_controlnumber;
    END IF;
                                              
    IF p_content IS NOT NULL 
    THEN
        v_select = v_select || ' AND lower( unaccent( searchcontent ) ) LIKE lower( unaccent( ''%' || p_content || '%'' ) )';
    END IF;

                 
    v_select = 'SELECT controlnumber,
                       fieldid,
                       subfieldid,
                       content
                 FROM gtcmaterial
                WHERE controlnumber IN ( ' || v_select || ')';
                
    IF p_tags IS NOT NULL 
    THEN
        v_select = v_select || ' AND fieldid || ''.'' || subfieldid IN ( '''|| replace(p_tags, ',', ''',''') || ''')';
    END IF;            
               
    v_select = v_select || ' ORDER BY controlnumber, fieldid, subfieldid';        
                        
    FOR v_line IN EXECUTE v_select
    LOOP
        RETURN NEXT v_line;
    END LOOP;
    
    RETURN;
          
END;
$$ language 'plpgsql';
