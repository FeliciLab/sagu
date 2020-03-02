CREATE OR REPLACE FUNCTION verificarUnidade()
RETURNS TRIGGER AS
$BODY$
/******************************************************************************
 * Adiciona a unidade atual logada na INSERCAO de tabelas multiunidade
******************************************************************************/
BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE'
    THEN
        IF NEW.unitid IS NULL AND TG_OP = 'INSERT'
        THEN
            NEW.unitid := obterunidadelogada();
        END IF;

        RETURN NEW;
    END IF;

    RETURN OLD;
END;
$BODY$
LANGUAGE plpgsql;
