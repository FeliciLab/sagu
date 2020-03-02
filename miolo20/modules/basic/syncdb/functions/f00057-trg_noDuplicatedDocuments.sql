CREATE OR REPLACE FUNCTION trg_noDuplicatedDocuments()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: trg_professorCommitment
  DESCRIPTION: não permite que um mesmo documento seja cadastrado para mais de
  uma pessoa.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       01/03/2011 Alex Smith        1. FUNÇÃO criada.
******************************************************************************/
DECLARE
    v_count integer;
    v_documentTypeDescription varchar;
BEGIN
    --Verificação para não permitir que mais pessoas utilizem o mesmo documento
    IF NEW.content IS NOT NULL
    THEN
        SELECT COUNT(*) INTO v_count
          FROM basDocument
    INNER JOIN basDocumentType
         USING (documenttypeid)
         WHERE documentTypeId = NEW.documentTypeId
           AND content = NEW.content
           AND documentTypeId != getParameter('basic', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::integer
           AND validaconteudo = TRUE
           AND CASE WHEN TG_OP = 'UPDATE'
                   THEN personId != NEW.personId
               ELSE
                   TRUE
               END;

        IF v_count != 0 THEN
            -- obtém descrição do documento para mensagem mais informativa
            SELECT name INTO v_documentTypeDescription
              FROM basDocumentType
             WHERE documentTypeId = NEW.documentTypeId;
             
            RAISE EXCEPTION 'O % % jé esté cadastrado para outra pessoa.', v_documentTypeDescription, NEW.content;
        END IF;
    END IF;

    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;
