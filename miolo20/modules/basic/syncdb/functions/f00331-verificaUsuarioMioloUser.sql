CREATE OR REPLACE FUNCTION verificaUsuarioMioloUser()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: verificaUsuarioMioloUser
  DESCRIPTION: Verifica se existe um usuário sem vínculo com pessoa e o excluí
               e verifica se existe um usuário com vínculo com pessoa e
               avisa o usuário.

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       19/02/15   Nataniel I. da Silva  1. Trigger criada.
******************************************************************************/
DECLARE
    v_usuarioSemPessoa BOOLEAN;
    v_usuarioComPessoa BOOLEAN;
    v_maisDeUmUsuario BOOLEAN;
BEGIN
    v_usuarioSemPessoa := FALSE;
    v_usuarioComPessoa := FALSE;

    SELECT INTO v_usuarioSemPessoa COUNT(*) > 0
      FROM miolo_user A
     WHERE A.login = NEW.login
       AND A.name  = NEW.name
       AND NOT EXISTS(SELECT 1 FROM ONLY basperson WHERE miolousername like NEW.login);

    SELECT INTO v_maisDeUmUsuario COUNT(*) > 1
      FROM miolo_user A
     WHERE A.login = NEW.login;

    -- Se já existir um usuário sem vinculo com pessoa excluí
    IF v_usuarioSemPessoa IS TRUE AND v_maisDeUmUsuario IS TRUE
    THEN
        DELETE FROM miolo_user WHERE login = NEW.login;
        RAISE NOTICE 'USUARIO EXCLUÍDO SEM VINCULO COM PESSOA: %. ', NEW.login;
    END IF;

    SELECT INTO v_usuarioComPessoa COUNT(*) > 0
      FROM miolo_user A
     WHERE A.login = NEW.login
       AND A.name  = NEW.name
       AND A.iduser <> NEW.iduser
       AND EXISTS(SELECT 1 FROM ONLY basperson WHERE miolousername like NEW.login);

    -- Se já existir um usuário com vinculo com pessoa
    IF v_usuarioComPessoa IS TRUE 
    THEN
        RAISE EXCEPTION 'Existe mais de um usuário cadastrado para o login %.<br> Entre em contato com o setor responsável.', NEW.login;
    END IF;
    	
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
  COST 100;

DROP TRIGGER IF EXISTS trg_verificaUsuarioMioloUser ON miolo_user;
CREATE TRIGGER trg_verificaUsuarioMioloUser BEFORE UPDATE OR INSERT ON miolo_user
  FOR EACH ROW EXECUTE PROCEDURE verificaUsuarioMioloUser();
