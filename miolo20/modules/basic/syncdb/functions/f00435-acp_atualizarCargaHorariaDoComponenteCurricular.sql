REATE OR REPLACE FUNCTION acp_atualizarCargaHorariaDoComponenteCurricularDisciplina()
RETURNS TRIGGER AS
$BODY$
/******************************************************************************
  NAME: acp_atualizarCargaHorariaDoComponenteCurricularDisciplina
  DESCRIPTION: Soma as cargas horárias presenciais, extra-classe e à distância,
  e salva na coluna de carga horária (que é a coluna totalizadora).

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       31/07/2015 Luís F. Wermann      1. Função criada.
******************************************************************************/
DECLARE
    -- Carga horária somada
    v_cargaHoraria NUMERIC;
BEGIN

    v_cargaHoraria := ( COALESCE(NEW.cargaHorariaPresencial, 0) + COALESCE(NEW.cargaHorariaExtraClasse, 0) + COALESCE(NEW.cargaHorariaEAD, 0) );

    IF ( TG_OP = 'UPDATE' )
    THEN
        IF ( v_cargaHoraria <> OLD.cargaHoraria )
        THEN   
            UPDATE acpComponenteCurricularDisciplina 
               SET cargaHoraria = v_cargaHoraria
             WHERE componenteCurricularId = NEW.componenteCurricularId;
        END IF;
    ELSIF ( TG_OP = 'INSERT' )
    THEN
        UPDATE acpComponenteCurricularDisciplina
           SET cargaHoraria = v_cargaHoraria
         WHERE componenteCurricularId = NEW.componenteCurricularId;
    END IF;

    RETURN NEW;

END;
$BODY$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_acp_atualizarCargaHorariaDoComponenteCurricularDisciplina ON acpComponenteCurricularDisciplina;
CREATE TRIGGER trg_acp_atualizarCargaHorariaDoComponenteCurricularDisciplina
AFTER INSERT OR UPDATE
ON acpComponenteCurricularDisciplina
FOR EACH ROW
EXECUTE PROCEDURE acp_atualizarCargaHorariaDoComponenteCurricularDisciplina();
