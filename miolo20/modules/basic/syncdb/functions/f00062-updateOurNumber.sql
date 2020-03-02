CREATE OR REPLACE FUNCTION updateOurNumber()
RETURNS TRIGGER AS $$
DECLARE
    v_ourNumber bigint;    
    v_query varchar;

    v_validournumber CHARACTER VARYING;
    v_maxournumber CHARACTER VARYING;
    v_update BOOLEAN;
BEGIN
/**
* FUNÇÃO chamada ao atualizar a conta bancéria
* Atualiza a sequéncia do nosso numero
**/
    v_ourNumber := new.bankaccountId::bigint; --Converte o nosso numero para numerico

    --Somente executa se o campo ourNumber foi alterado
    IF old.ourNumber != new.ourNumber
    THEN
        v_update := FALSE;
        SELECT INTO v_maxournumber MAX(ournumber) FROM finbankinvoiceinfo WHERE bankAccountId = NEW.bankAccountId;

        --Caso o numero informado seja menor ou igual a um nosso numero jé gerado incrementa
        IF NEW.ournumber::bigint < v_maxournumber::bigint
        THEN
            v_validournumber := LPAD((v_maxournumber::bigint + 1)::varchar, length(NEW.ournumber), '0');
            v_update := TRUE;
        ELSE
            v_validournumber := NEW.ournumber;
        END IF;

        -- Verifica se existe sequencia e seta o novo valor, caso nao exista ainda, cria
        BEGIN
            v_ourNumber := setval('seq_ourNumber_bankAccountId_' || new.bankAccountId, v_validournumber::bigint);
        EXCEPTION
            WHEN OTHERS THEN
                -- cai no erro se a sequencia ainda nao existir e cria a sequencia
                v_query := 'CREATE SEQUENCE seq_ourNumber_bankAccountId_' || new.bankAccountId || ' START ' || v_validournumber::bigint ;
                EXECUTE v_query;
        END;
        
        --Caso o numero for invalido incrementa e atualiza novamente
        IF v_update IS TRUE 
        THEN
            NEW.ournumber := v_validournumber;
        END IF;
        
    END IF;

    RETURN NEW;
END;
$$ language plpgsql;

/**
* Cria a trigger que atualiza a sequéncia do nosso numero sempre que uma conta bancéria for criada ou atualizada.
**/
DROP TRIGGER IF EXISTS trg_updateOurNumber ON finbankaccount;
CREATE TRIGGER trg_updateOurNumber 
        BEFORE UPDATE
        ON finbankaccount 
        FOR EACH ROW 
        EXECUTE PROCEDURE updateOurNumber();
--
