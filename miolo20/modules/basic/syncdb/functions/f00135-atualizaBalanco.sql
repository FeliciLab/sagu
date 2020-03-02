CREATE OR REPLACE FUNCTION atualizabalanco()
RETURNS TRIGGER AS $$
/*************************************************************************************
  NAME: atualizaBalanco
  PURPOSE: Atualiza o estado do titulo (valor aberto) de acordo com os lancamentos.
**************************************************************************************/
DECLARE
    v_tituloid integer;
BEGIN
    IF TG_OP = 'UPDATE' OR TG_OP = 'INSERT'
    THEN
        v_tituloid := NEW.tituloid;
    END IF;

    IF TG_OP = 'DELETE'
    THEN
        v_tituloid := OLD.tituloid;
    END IF;

    -- Atualiza o valor aberto (saldo devedor)
    UPDATE capTitulo SET valoraberto = (
        SELECT SUM( (CASE WHEN tipolancamento = 'C' THEN valor ELSE (valor * -1) END) )
        FROM capLancamento
        WHERE tituloId = v_tituloid
    )
    WHERE tituloId = v_tituloid;

    -- Atualiza flag de titulo aberto (baseando-se no valor aberto)
    UPDATE capTitulo SET tituloaberto = ( valoraberto > 0 ) WHERE tituloId = v_tituloid;

    RETURN NEW;
END;
$$ language plpgsql;
--

DROP TRIGGER IF EXISTS trg_atualizabalanco ON caplancamento;
CREATE TRIGGER trg_atualizabalanco AFTER UPDATE OR INSERT OR DELETE ON caplancamento FOR EACH ROW EXECUTE PROCEDURE atualizabalanco();
--
