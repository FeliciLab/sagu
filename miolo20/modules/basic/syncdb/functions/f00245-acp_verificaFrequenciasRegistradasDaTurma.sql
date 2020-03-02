CREATE OR REPLACE FUNCTION acp_verificafrequenciasregistradasdaturma(p_ofertaturmaid int)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: acp_verificafrequenciasregistradasdaturma
  DESCRIPTION: Verifica se todas as matriculas tiveram suas frequencias digitadas

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       21/10/14   Felipe Ferreira         Função criada.
******************************************************************************/
DECLARE
    v_ofertacomponentecurricularid int;
    v_matricula acpmatricula; --Oferta componente curricular
    v_ofertacomponentecurricular acpofertacomponentecurricular; --Oferta componente curricular
	
BEGIN
    FOR v_ofertacomponentecurricular IN SELECT * FROM acpofertacomponentecurricular WHERE ofertaturmaid = p_ofertaturmaid AND datafechamento IS NULL
    LOOP
--verifica se nao tem frequencia registrada
	IF count(*) != 0 FROM acpmatricula WHERE ofertacomponentecurricularid = v_ofertacomponentecurricular.ofertacomponentecurricularid AND frequenciasregistradas IS FALSE AND acpmatricula.situacao = 'M' 
	THEN
           RETURN TRUE;  
	END IF;
    END LOOP;
RETURN FALSE;
END;
$BODY$
LANGUAGE plpgsql;
