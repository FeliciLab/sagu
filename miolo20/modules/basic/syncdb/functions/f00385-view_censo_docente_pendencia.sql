DROP TYPE IF EXISTS tabela_professor;
CREATE TYPE tabela_professor AS (id_professor int, nome text, cpf text, data_nascimento text, escolaridade int, pos int, situacao_ies int  );
CREATE OR REPLACE FUNCTION view_censo_docente_pendencia()
returns table(id_pessoa int, nome text, pendencia text) AS
$BODY$
/******************************************************************************
  NAME: view_censo_docente_pendencia
  DESCRIPTION: Verifica as pendencias do censo_docente

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       8/04/15   Felipe Ferreira         Função criada.
******************************************************************************/
DECLARE
    v_view_docente tabela_professor; --view docente
BEGIN

    FOR v_view_docente IN SELECT DISTINCT a.personid,a.name, replace(replace(b.content, '.'::text, ''::text), '-'::text, ''::text)::text, to_char(a.datebirth, 'ddmmyyyy')::text, a.escolaridade, a.posgraduacao, a.situacao  FROM basphysicalpersonprofessor a
        LEFT JOIN basdocument b on b.personid = a.personid  and b.documenttypeid = getparameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::integer ORDER BY a.name
    LOOP
        --Cpf
        IF v_view_docente.cpf IS NULL THEN
           RETURN QUERY SELECT v_view_docente.id_professor::integer, v_view_docente.nome::text, 'Não informado CPF.'::text;
        END IF;
	IF v_view_docente.data_nascimento = '' THEN
           RETURN QUERY SELECT v_view_docente.id_professor::integer, v_view_docente.nome::text, 'Não informado data de nascimento.'::text;
        END IF;
        IF v_view_docente.nome = '' THEN
           RETURN QUERY SELECT v_view_docente.id_professor::integer, v_view_docente.nome::text, 'Não informado nome.'::text;
        END IF;
         IF v_view_docente.escolaridade = 2 AND v_view_docente.pos IS NULL THEN
           RETURN QUERY SELECT v_view_docente.id_professor::integer, v_view_docente.nome::text, 'Não informado escolaridade.'::text;
        END IF;
        IF v_view_docente.situacao_ies IS NULL THEN
           RETURN QUERY SELECT v_view_docente.id_professor::integer, v_view_docente.nome::text, 'Não informado situação do docente na IES.'::text;
        END IF;
    END LOOP;  
END;
$BODY$
  LANGUAGE plpgsql;
