CREATE OR REPLACE FUNCTION obterconfiguracaodematriculapelamatricula(p_enrollid integer)
RETURNS INTEGER AS
$BODY$
/******************************************************************************
  NAME: obtemconfiguracaodematricula
  DESCRIPTION: Obtém a configuração de matrícula pelo código de matrícula

  REVISIONS:
  Ver       Date       Author                Description
  --------- ---------- ------------------    ---------------------------------
  1.0       03/06/2015 ftomasini             1. Função criada.
******************************************************************************/
DECLARE

    --Código de matrícula
    v_enrollId INT;
    v_contrato RECORD;
    v_enrollConfigId INT;

BEGIN
    SELECT INTO v_contrato
                 A.courseid,
                 A.courseversion,
                 A.turnid,
                 A.unitid
                   FROM acdcontract A
             INNER JOIN acdenroll B
                     ON A.contractid = B.contractid
                  WHERE B.enrollid = p_enrollid;

        --Obter código da configuração de matrícula da ocorrência de curso do contrato
        v_enrollConfigId := (SELECT enrollConfigId
                               FROM acdEnrollConfig
                              WHERE courseId = v_contrato.courseId
                                AND courseVersion = v_contrato.courseVersion
                                AND turnId = v_contrato.turnId
                                AND unitId = v_contrato.unitId
                                AND ((NOW()::DATE >= begindate AND NOW()::DATE <= (CASE WHEN enddate is not null
                                                                                          THEN
                                                                                               enddate
                                                                                          ELSE NOW()::DATE
                                                                                     END)) OR (beginDate IS NULL AND endDate IS NULL)));

        --Caso não achar do curso, procura uma geral
        IF ( v_enrollConfigId IS NULL )
        THEN
            v_enrollConfigId := (SELECT enrollConfigId
                                   FROM acdEnrollConfig
                                  WHERE ((NOW()::DATE >= begindate AND NOW()::DATE <= (CASE WHEN enddate is not null
                                                                                              THEN
                                                                                                  enddate
                                                                                              ELSE NOW()::DATE
                                                                                         END)) OR (beginDate IS NULL AND endDate IS NULL))
                                    AND courseId IS NULL
                                    AND courseVersion IS NULL
                                    AND turnId IS NULL
                                    AND unitId IS NULL);
        END IF;

        RETURN v_enrollConfigId;


        IF ( v_enrollConfigId IS NULL )
        THEN
            RAISE EXCEPTION 'Nenhuma configuração de matrícula vigente foi encontrada. Para realizar essa tarefa, cadastre uma configuração de matrícula em Acadêmico::Configuração::Configuração de matrícula.';
        END IF;

    END;
    $BODY$
    LANGUAGE plpgsql;
