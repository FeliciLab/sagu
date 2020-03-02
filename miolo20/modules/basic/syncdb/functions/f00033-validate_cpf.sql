CREATE OR REPLACE FUNCTION validate_cpf(p_cpf varchar)
RETURNS boolean AS
$BODY$
/******************************************************************************
  NAME: VALIDATE_CPF
  DESCRIPTION: FUNÇÃO responsável por validar CPF.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       14/01/2011 Arthur Lehdermann 1. FUNÇÃO criada.
  1.1       14/01/2011 Alex Smith        2. Identação e otimização.
******************************************************************************/
DECLARE
    v_digit integer;
    v_sum integer;
    v_cpf varchar;
BEGIN
    -- verificar se cpf contém caracteres inválidos para um cpf
    v_cpf := regexp_replace(p_cpf, '[^0-9.-]', '', 'g');

    IF ( v_cpf != p_cpf )
    THEN
        RETURN FALSE;
    END IF;

    -- deixar somente números
    v_cpf := regexp_replace(p_cpf, '[.-]', '', 'g');

    -- testes de sanidade
    IF (v_cpf IS NULL
        OR LENGTH(v_cpf) < 11
        OR v_cpf = '00000000000'
        OR v_cpf = '11111111111'
        OR v_cpf = '22222222222'
        OR v_cpf = '33333333333'
        OR v_cpf = '44444444444'
        OR v_cpf = '55555555555'
        OR v_cpf = '66666666666'
        OR v_cpf = '77777777777'
        OR v_cpf = '88888888888'
        OR v_cpf = '99999999999')
    THEN
        RETURN FALSE;
    END IF;

    -- cálculo do primeiro dígito
    v_digit := 0;
    v_sum := 0;

    FOR i IN 1..9
    LOOP
        v_sum := v_sum + (SUBSTR(v_cpf, i, 1))::integer * (11 - i);
    END LOOP;

    v_digit := 11 - MOD (v_sum, 11);

    IF v_digit > 9
    THEN
        v_digit := 0;
    END IF;

    -- validação do primeiro dígito
    IF v_digit != (SUBSTR (v_cpf, 10, 1))::integer
    THEN
        RETURN FALSE;
    END IF;

    -- cálculo do segundo dígito
    v_digit := 0;
    v_sum := 0;

    FOR i IN 1..10
    LOOP
        v_sum := v_sum + (SUBSTR (v_cpf, i, 1))::integer * (12 - i);
    END LOOP;

    v_digit := 11 - MOD (v_sum, 11);

    IF v_digit > 9
    THEN
        v_digit := 0;
    END IF;

    -- validação do segundo dígito
    IF v_digit != (SUBSTR (v_cpf, 11, 1))::integer
    THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END;
$BODY$
LANGUAGE 'plpgsql';
