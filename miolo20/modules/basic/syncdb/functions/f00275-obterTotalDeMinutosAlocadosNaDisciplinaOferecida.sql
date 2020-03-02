CREATE OR REPLACE FUNCTION obterTotalDeMinutosAlocadosNaDisciplinaOferecida(p_groupId INT)
RETURNS INTEGER AS
$BODY$
/*************************************************************************************
  NAME: obterTotalDeMinutosAlocadasNaDisciplinaOferecida
  PURPOSE: Calcula e retorna o total de minutos alocados na disciplina oferecida.
           Para converter em horas, dividir o resultado da função por 60.
           Para obter a sobra de minutos por alocação em horas, % 60.
  DESCRIPTION: vide "PURPOSE".

  REVISIONS:
  Ver       Date         Author            Description
  --------- ----------   ----------------- ------------------------------------
  1.0       06/10/2014   Augusto A. Silva  1. Função criada.                                      
**************************************************************************************/
DECLARE
    v_totalDeMinutosAlocados INT := 0;
BEGIN
    -- Obtém a soma do total de minutos de todos os scheduleids da disciplina oferecidas.
    SELECT INTO v_totalDeMinutosAlocados SUM((Y.somaTimeIds * quantDatas)) AS result

    -- Obtém a soma do total de minutos de todos os timeIds da schedule.
    FROM ( SELECT SUM(Z.minutosportimeid) AS somaTimeIds,
                  Z.quantDatas

             -- Obtém total de minutos por timeId
             FROM ( SELECT split_part((EXTRACT(EPOCH FROM A.numberMinutes) * INTERVAL '1 minute')::TEXT, ':', 1)::INT AS minutosPorTimeId,
                           X.total AS quantDatas
                      FROM acdTime A

                -- Obtém a quantidade total de datas por schedule da oferecida.
                INNER JOIN ( SELECT UNNEST(timeIds) AS timeId,
                                    SUM(array_length(occurrenceDates, 1)) AS total
                               FROM acdSchedule
                              WHERE groupId = p_groupId
                           GROUP BY 1
                           ORDER BY 1 ) X
                        ON X.timeId = A.timeId ) Z
         GROUP BY 2 ) Y;

    RETURN v_totalDeMinutosAlocados;
END;
$BODY$
LANGUAGE plpgsql;
