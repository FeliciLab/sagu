CREATE OR REPLACE FUNCTION checkBloqueiaCursos()
  RETURNS trigger AS
$BODY$
/*************************************************************************************
  NAME: checkBloqueiaCursos
  PURPOSE: Trigger disparada ao inserir um novo contrato, caso o curso esteja 
           bloqueado para novas matrículas exibe uma mensagem de erro.
 REVISIONS:
  Ver       Date       Author               Description
  --------- ---------- -----------------    ------------------------------------
  1.0       28/08/2014 ftomasini            1. Função criada.
**************************************************************************************/
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF ( EXISTS (SELECT courseid 
                       FROM acdcourseoccurrence 
                      WHERE courseid = NEW.courseid
                        AND courseVersion = NEW.courseversion 
                        AND turnid = NEW.turnid
                        AND unitid = NEW.unitid
                        AND cursoBloqueado = TRUE))
                        -- O código abaixo libera insercao de novos contratos para usuarios que estiverem
                        -- no grupo admin
                        --AND NOT EXISTS ( SELECT login 
                        --                   FROM miolo_groupuser A 
                        --             INNER JOIN miolo_user B USING (iduser) 
                        --                  WHERE B.login = NEW.username 
                        --                    AND A.idgroup = 1 ) 
        THEN
            RAISE EXCEPTION 'Este curso está bloqueado para matrícula. Favor entrar em contato com a pró-reitoria acadêmica';
            RETURN NULL;
        ELSE
            RETURN NEW;
        END IF;
    END IF;
END;
$BODY$
  LANGUAGE plpgsql;

 DROP TRIGGER IF EXISTS checkBloqueiaCursos ON acdContract;
 CREATE TRIGGER checkBloqueiaCursos
  BEFORE INSERT
  ON acdContract
  FOR EACH ROW
  EXECUTE PROCEDURE checkBloqueiaCursos(); 
