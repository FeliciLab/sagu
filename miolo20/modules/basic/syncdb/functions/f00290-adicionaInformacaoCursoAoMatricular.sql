CREATE OR REPLACE FUNCTION adicionaInformacaoCursoAoMatricular()
RETURNS TRIGGER AS
$BODY$
DECLARE    
    v_inscricaoturmagrupo acpinscricaoturmagrupo%ROWTYPE;
    v_cursoid INTEGER;

BEGIN
    IF ( NEW.situacao = 'M' ) THEN
    BEGIN
        SELECT INTO v_inscricaoturmagrupo * FROM acpinscricaoturmagrupo WHERE inscricaoturmagrupoid = NEW.inscricaoturmagrupoid;
        SELECT INTO v_cursoid C.cursoid 
          FROM acpofertaturma T 
     LEFT JOIN acpofertacurso OC ON (T.ofertacursoid = OC.ofertacursoid)
     LEFT JOIN acpocorrenciacurso OCC ON (OC.ocorrenciacursoid = OCC.ocorrenciacursoid)
     LEFT JOIN acpcurso C ON (OCC.cursoid = C.cursoid) 
         WHERE T.ofertaturmaid = v_inscricaoturmagrupo.ofertaturmaid;

        IF NOT EXISTS (SELECT cursoinscricaoid FROM acpcursoinscricao WHERE personid = NEW.personid AND cursoid = v_cursoid) THEN
        BEGIN
            INSERT INTO acpcursoinscricao (personid, cursoid, situacao) VALUES (NEW.personid, v_cursoid, NEW.situacao);
        END;
        END IF;
    END;
    END IF;

    RETURN NEW;
END;
$BODY$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_adicionaInformacaoCursoAoMatricular ON acpmatricula;
CREATE TRIGGER trg_adicionaInformacaoCursoAoMatricular
  AFTER INSERT OR UPDATE
  ON acpmatricula
  FOR EACH ROW
  EXECUTE PROCEDURE adicionaInformacaoCursoAoMatricular();
