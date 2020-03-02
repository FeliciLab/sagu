CREATE OR REPLACE FUNCTION getNextOurNumber( p_bankAccountId finbankaccount.bankaccountid%TYPE )
RETURNS text AS $BODY$
DECLARE
    v_ourNumber text;
    v_last_ourNumber text;
    v_query varchar;
BEGIN
/**
* FUNÇÃO chamada na geração do título
* Gera o nosso número com a utitlização de uma sequência
**/
    --Obtém o nosso numero definido na conta bancária
    v_last_ourNumber := ourNumber FROM finbankaccount WHERE bankaccountid = p_bankAccountId;

    IF v_last_ourNumber IS NULL 
    THEN
        RAISE EXCEPTION 'Deve ser registrado ''Nosso número'' para a conta bancária %', p_bankAccountId;
    END IF;
    
    -- Verifica se existe sequencia e pega o nextval, caso nao exista ainda, cria
    BEGIN
        v_ourNumber := nextval('seq_ourNumber_bankAccountId_' || p_bankAccountId)::text;
    EXCEPTION
        WHEN OTHERS THEN
            -- cai no erro se a sequencia ainda nao existir e cria a sequencia
            v_query := 'CREATE SEQUENCE seq_ourNumber_bankAccountId_' || p_bankAccountId || ' START ' || v_last_ourNumber::numeric + 1;
            EXECUTE v_query;
            v_ourNumber := nextval('seq_ourNumber_bankAccountId_' || p_bankAccountId)::text;
    END;

    -- Corrige a sequencia caso esteja incorreta.
    WHILE ( SELECT ( SELECT COUNT(ourNumber) FROM finBankInvoiceInfo WHERE ourNumber = lpad(v_ourNumber, length(v_last_ourNumber), '0') AND finBankInvoiceInfo.bankAccountId = p_bankAccountId ) > 0 )
    LOOP 
	v_ourNumber := v_ourNumber::INTEGER + 1;
	PERFORM setval('seq_ourNumber_bankAccountId_' || p_bankAccountId, v_ourNumber::INTEGER);
    END LOOP;

    --Obtém o nosso numero original e converte o novo nosso numero para o mesmo numero de caracteres    
    v_ourNumber := lpad(v_ourNumber, length(v_last_ourNumber), '0');

    RAISE NOTICE 'v_ourNumber %', v_ourNumber;


    --Atualiza o nosso numero
    UPDATE finbankaccount SET ourNumber = v_ourNumber WHERE bankaccountid = p_bankAccountId;

    RETURN v_ourNumber;
END;
$BODY$ language plpgsql;
