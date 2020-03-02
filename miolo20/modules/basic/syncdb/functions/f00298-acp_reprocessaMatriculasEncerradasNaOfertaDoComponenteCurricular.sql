CREATE OR REPLACE FUNCTION acp_reprocessaMatriculasEncerradasNaOfertaDoComponenteCurricular(p_ofertaComponenteCurricularId INT)
RETURNS SETOF INT AS
$BODY$
/******************************************************************************
  NAME: acp_cancelarmatricula
  DESCRIPTION: Reprocessa as matrículas encerradas de uma oferta do compenente curricular.
  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       28/11/14   Augusto A. Silva   Funcao criada.
******************************************************************************/
DECLARE
    v_matriculas RECORD;
    v_quantidade_matriculas_situacao_alterada INT := 0;
BEGIN
    FOR v_matriculas IN
        (SELECT A.matriculaid,
                A.situacao
	   FROM acpmatricula A
	  WHERE A.ofertacomponentecurricularid = p_ofertaComponenteCurricularId
	    AND A.situacao IN ('A', 'R', 'F'))
    LOOP
        --Reabre a matrícula
        UPDATE acpmatricula                                                  
	   SET frequencia = NULL,                                               
	       notafinal = NULL,                                                
	       conceitofinal = NULL,                                            							    
	       situacao = 'M',
	       estadodematriculaid = NULL
         WHERE matriculaid = v_matriculas.matriculaid;

        --Reprocessa e fecha a matrícula novamente.
        IF ( SELECT acp_fecharmatricula(v_matriculas.matriculaid) ) IS FALSE
        THEN
            RAISE EXCEPTION 'Erro ao reprocessar a matrícula %', v_matriculas.matriculaid;
        ELSE
            --Verifica se a situação da matrícula foi modificada.
            IF ( (SELECT situacao
                    FROM acpMatricula
                   WHERE matriculaid = v_matriculas.matriculaid) <> v_matriculas.situacao )
            THEN
                RETURN NEXT v_matriculas.matriculaid;
            END IF;
        END IF;
    END LOOP;
END;
$BODY$
LANGUAGE plpgsql;
