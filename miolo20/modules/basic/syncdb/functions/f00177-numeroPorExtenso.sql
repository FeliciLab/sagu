CREATE OR REPLACE FUNCTION numeroPorExtenso(num numeric(20,2) , moeda text , moedas text) returns text as $$
/*************************************************************************************
  NAME: numeroPorExtenso
  PURPOSE: Obtém o número por extenso, e a moeda, caso seja passado.
  PARAMETERS: 
            num -> numero a ser convertido em extenso
            moeda -> nome da moeda no singular
            moedas -> nome da moeda no plural
  **CASO QUEIRA APENAS O NÚMERO POR EXTENSO, PASSAR OS ÚLTIMOS DOIS PARÂMETROS VAZIOS.
**************************************************************************************/
DECLARE
    w_int char(21) ;
    x integer ;
    v integer ;
    w_ret text ;
    w_ext text ;
    w_apoio text ;
    m_cen text[] := array['quatrilhão','quatrilhões','trilhão','trilhões','bilhão','bilhões','milhão','milhões','mil','mil'] ;
BEGIN
    w_ret := '' ;
    w_int := to_char(num * 100 , 'fm000000000000000000 00') ;
    for x in 1..5 loop
        v := cast(substr(w_int,(x-1)*3 + 1,3) as integer) ;    
        if v > 0 then
            if v > 1 then
                w_ext := m_cen[(x-1)*2+2] ;
            else
                w_ext := m_cen[(x-1)*2+1] ;
            end if ;   
            w_ret := w_ret || escreveNumeroPorExtenso(substr(w_int,(x-1)*3 + 1,3)) || ' ' || w_ext ||', ' ;
        end if ;  
    end loop ;
    v := cast(substr(w_int,16,3) as integer) ;    
    if v > 0 then
        if v > 1 then
            w_ext := moedas ;
        else
            if w_ret = '' then 
            w_ext := moeda ;
            else
            w_ext := moedas ;
            end if ;   
        end if ; 
        w_apoio := escreveNumeroPorExtenso(substr(w_int,16,3)) || ' ' || w_ext ;
        if w_ret = '' then 
            w_ret := w_apoio ;
        else 
            if v > 100 then 
            if w_ret = '' then 
                w_ret := w_apoio ;
                else
                w_ret := w_ret || w_apoio ;
            end if ;   
            else
            w_ret := btrim(w_ret,', ') || ' e ' || w_apoio ;
            end if ;   
        end if ;   
        else 
        if w_ret <> '' then  
            if substr(w_int,13,6) = '000000' then 
            w_ret := btrim(w_ret,', ') || ' de ' || moedas ;
            else 
            w_ret := btrim(w_ret,', ') || ' ' || moedas ;
            end if ;    
        end if ;  
    end if ;    
    v := cast(substr(w_int,20,2) as integer) ;    
    if v > 0 then
        if v > 1 then
            w_ext := 'centavos' ;
        else
            w_ext := 'centavo' ;
        end if ;   
        w_apoio := escreveNumeroPorExtenso('0'||substr(w_int,20,2)) || ' ' || w_ext ;
        if w_ret = '' then 
            w_ret := w_apoio  || ' de ' || moeda;
        else 
            w_ret := w_ret || ' e ' || w_apoio ;
        end if ;   
    end if ;    
    return w_ret ;  
end ;
$$ LANGUAGE plpgsql 
   IMMUTABLE 
   RETURNS NULL ON NULL INPUT ;
--
