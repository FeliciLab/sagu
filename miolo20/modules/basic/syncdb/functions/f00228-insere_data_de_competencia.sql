/**
 * Função que insere a data de competência na tabela fininvoice
 *
 */
CREATE OR REPLACE FUNCTION insere_data_de_competencia()
RETURNS trigger AS
$$
DECLARE
    
BEGIN
    IF NEW.competencydate IS NOT NULL THEN
        RETURN NEW;
    ELSE
        UPDATE fininvoice 
           SET competencydate = NEW.referencematuritydate 
         WHERE invoiceid = NEW.invoiceid;

        RETURN NEW;
    END IF;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS insere_data_competencia_invoice ON fininvoice;
CREATE TRIGGER insere_data_competencia_invoice AFTER INSERT ON fininvoice
    FOR EACH ROW EXECUTE PROCEDURE insere_data_de_competencia();
