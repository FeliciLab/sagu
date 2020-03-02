--
-- Controla o numero de vagas de alunos matriculados.
--
CREATE OR REPLACE FUNCTION setTotalEnrolled()
  RETURNS trigger AS
$BODY$
/******************************************************************************
  NAME: setTotalEnrolled
  DESCRIPTION: Atualiza o valor de total de alunos matriculados
******************************************************************************/
DECLARE
    v_totalenrolled INTEGER;
    v_subscriptionTotalVacancies INTEGER;
    v_vacant INTEGER;
    v_checkStatus boolean;
BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN

        IF TG_OP = 'UPDATE' THEN
            IF OLD.statusid = NEW.statusid THEN
                RETURN NEW;
            END IF;
        END IF;

        SELECT INTO v_totalenrolled COUNT(*) FROM acdenroll WHERE groupid = NEW.groupid AND statusid <> 5
            -- Trata regra de pre-matricula (consumir vaga)
            AND ( CASE WHEN statusId = GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_PRE_ENROLLED')::int
              THEN
                NEW.preEnrollConsumeVacant IS NOT FALSE
              ELSE
                1=1
              END
            );

        -- Obtem o total de vagas
        SELECT INTO v_vacant vacant FROM acdGroup WHERE groupid = NEW.groupid;

        -- Caso estiver matriculado em uma disciplina cheia e estiver fazendo uma edicao de matricula, nao exibir o erro
        IF TG_OP = 'UPDATE'
        THEN
            v_checkStatus := NEW.statusid != OLD.statusid;
        ELSE
            v_checkStatus := TRUE;
        END IF;

        IF ( (v_subscriptionTotalVacancies >= v_vacant) AND ( NEW.statusid != 5 ) AND v_checkStatus IS TRUE )
        THEN
            RAISE EXCEPTION 'O número de inscritos não pode exceder o total de vagas. O total de vagas para esta disciplina é (%)', v_vacant;
            RETURN NULL;
        END IF;

        UPDATE acdgroup SET totalenrolled = v_totalenrolled WHERE groupid = NEW.groupid;
        RETURN NEW;
    END IF;
    IF TG_OP = 'DELETE' THEN
        SELECT INTO v_totalenrolled COUNT(*) FROM acdenroll WHERE groupid = OLD.groupid AND statusid <> 5;
        UPDATE acdgroup SET totalenrolled = v_totalenrolled WHERE groupid = OLD.groupid;
        RETURN OLD;
    END IF;
END;
$BODY$
    LANGUAGE plpgsql;
--
