CREATE OR REPLACE FUNCTION obterDatasDeProvaDaDisciplinaOferecida(p_groupId INT)
RETURNS TEXT AS
$BODY$
DECLARE

    --Avaliações marcadas na tabela acdEvaluation
    v_avaliacoes RECORD;

    --Datas de prova/avaliação (são todas concatenadas para poderem entrar uma coluna de vie)
    v_datas TEXT;

BEGIN

    FOR v_avaliacoes IN
       (SELECT dateforecast
          FROM acdEvaluation
         WHERE groupId = p_groupId
      ORDER BY dateforecast)
    LOOP
        IF ( v_avaliacoes.dateforecast IS NOT NULL )
        THEN
            IF ( v_datas IS NULL )
            THEN
                v_datas := dateToUser(v_avaliacoes.dateforecast);
            ELSE
                v_datas := v_datas || ', ' || dateToUser(v_avaliacoes.dateforecast);
            END IF;
        END IF;
     
    END LOOP;

    RETURN v_datas;
END;
$BODY$
LANGUAGE plpgsql;