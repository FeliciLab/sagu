CREATE OR REPLACE FUNCTION escreveNumeroPorExtenso(num char(3)) returns text as $$
/*************************************************************************************
  NAME: escreveNumeroPorExtenso
  PURPOSE: É chamada pela função numeroPorExtenso para concatenar os números.
  AUTOR: Nataniel
**************************************************************************************/
DECLARE
    w_cen integer ;
    w_dez integer ;
    w_dez2 integer ;
    w_uni integer ;
    w_tcen text ;
    w_tdez text ;
    w_tuni text ;
    w_ext text ;
    m_cen text[] := array['','cento','duzentos','trezentos','quatrocentos','quinhentos','seiscentos','setecentos','oitocentos','novecentos'];
    m_dez text[] := array['','dez','vinte','trinta','quarenta','cinquenta','sessenta','setenta','oitenta','noventa'] ;
    m_uni text[] := array['','um','dois','três','quatro','cinco','seis','sete','oito','nove','dez','onze','doze','treze','quatorze','quinze','dezesseis','dezessete','dezoito','dezenove'] ;
BEGIN
    w_cen := cast(substr(num,1,1) as integer) ;
    w_dez := cast(substr(num,2,1) as integer) ;
    w_dez2 := cast(substr(num,2,2) as integer) ;
    w_uni := cast(substr(num,3,1) as integer) ;
    if w_cen = 1 and w_dez2 = 0 then
        w_tcen := 'Cem' ;
        w_tdez := '' ;
        w_tuni := '' ;
        else
        if w_dez2 < 20 then 
            w_tcen := m_cen[w_cen + 1] ;
            w_tdez := m_uni[w_dez2 + 1] ; 
            w_tuni := '' ;
        else
            w_tcen := m_cen[w_cen + 1] ;
            w_tdez := m_dez[w_dez + 1] ; 
            w_tuni := m_uni[w_uni + 1] ;
        end if ;    
    end if ; 
    w_ext := w_tcen ;
    if w_tdez <> '' then  
        if w_ext = '' then 
            w_ext := w_tdez ;
        else
            w_ext := w_ext || ' e ' || w_tdez ;
        end if ;      
    end if ;   
    if w_tuni <> '' then  
        if w_ext = '' then 
            w_ext := w_tuni ;
        else
            w_ext := w_ext || ' e ' || w_tuni ;
        end if ;
    end if ;
    return w_ext ;  
end ;
$$ LANGUAGE plpgsql 
   IMMUTABLE 
   RETURNS NULL ON NULL INPUT ;
--
