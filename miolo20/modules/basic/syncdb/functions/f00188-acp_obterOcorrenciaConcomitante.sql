CREATE OR REPLACE FUNCTION acp_obterOcorrenciaConcomitante(p_ofertacomponentecurricularid int, p_gradehorarioid int, p_iniciodasaulas date, p_datassemaula date[])
RETURNS TABLE (data date, horarioid INT, horarioofertacomponentecurricular INT, professorid int, physicalresourceid int, physicalresourceversion int) AS
$BODY$
/*************************************************************************************
  NAME: acp_obterOcorrenciaConcomitante
  PURPOSE: Obtém as ocorrências de aulas para uma forma de cursar Concomitante

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       10/12/2013 Jonas Diel        1. Função criada.
**************************************************************************************/
DECLARE
    v_horarioofertacomponente RECORD;
    v_gradehorario RECORD;
    v_horario RECORD;
    v_numerodeaulas INT;
    v_dias INT;
    v_count INT;
    v_data DATE;
    v_return_data date[];
    v_return_horariooferta int[];
    v_return_horario int[];
    v_return_professor int[];
    v_return_physicalresourceid int[];
    v_return_physicalresourceversion int[];
    v_countaulas INT;
    v_semana INT;
    v_count_horariocomponente INT;
BEGIN
    --Obtém as informações da grade de horário
    SELECT INTO v_gradehorario * FROM acpgradehorario WHERE gradehorarioid = p_gradehorarioid;
    
    --Percorre horario oferta do componente para obter as informações do professor, sala e número de aulas
    v_count_horariocomponente := 0;
    FOR v_horarioofertacomponente IN (SELECT o.numerodeaulas, o.horarioofertacomponentecurricularid, o.personid, o.physicalresourceid, o.physicalresourceversion, h.diasemana, h.horarioid FROM acphorarioofertacomponentecurricular o INNER JOIN acphorario h ON h.horarioid = o.horarioid WHERE o.ofertacomponentecurricularid = p_ofertacomponentecurricularid)
    LOOP
        --Número de aulas
        v_numerodeaulas := v_horarioofertacomponente.numerodeaulas;

        --Conta e percorre o número de aulas da oferta
        v_count := 0;
        v_countaulas := v_numerodeaulas;
        WHILE v_count < v_numerodeaulas LOOP

            --Obtém horario da aula
            SELECT INTO v_horario * FROM acpHorario WHERE acphorario.horarioid = v_horarioofertacomponente.horarioid;
 
               --Define a primeira data de ocorrência como data de inicio das aulas
                IF v_data IS NULL
                THEN
                    v_data := p_iniciodasaulas;
                ELSE
                    --Numero da semana
                    v_semana := EXTRACT('WEEK' FROM v_data);
                END IF;

                IF v_count_horariocomponente = 0 
                THEN
                    --Calcula o numero de dias para proximo horario de aula da grade
                    v_dias := v_horarioofertacomponente.diasemana - EXTRACT('DOW' FROM v_data);
                    IF v_dias < 0 THEN
                        v_dias := v_dias + 7;
                    END IF;
                END IF; 

                --Define a proxima data
                v_data := v_data + v_dias;

                --Se não tem aula adia para próxima ocorrência
                IF( p_datassemaula @> ARRAY[v_data] ) THEN
                    v_countaulas := v_countaulas + 1;
                ELSE
                   v_return_data := ARRAY_APPEND(v_return_data, v_data);
                    v_return_horariooferta := ARRAY_APPEND(v_return_horariooferta, v_horarioofertacomponente.horarioofertacomponentecurricularid);
                    v_return_horario := ARRAY_APPEND(v_return_horario, v_horario.horarioid);
                    v_return_professor := ARRAY_APPEND(v_return_professor, v_horarioofertacomponente.personid);
                    v_return_physicalresourceid := ARRAY_APPEND(v_return_physicalresourceid, v_horarioofertacomponente.physicalresourceid);
                    v_return_physicalresourceversion := ARRAY_APPEND(v_return_physicalresourceversion, v_horarioofertacomponente.physicalresourceversion);
                END IF;
                v_count := v_count +1;

                --Caso a periodicidade for quinzenal pula uma semana
                IF v_gradehorario.periodicidade = 'Q' THEN
                    IF v_semana != EXTRACT('WEEK' FROM v_data)
                    THEN
                        v_data := v_data+7;
                    END IF;
                END IF;

                --Caso atingiu o limite de aulas
                IF v_count >= v_countaulas THEN
                    EXIT;
                END IF;

        END LOOP;
        v_count_horariocomponente := v_count_horariocomponente +1;
    END LOOP;

    RETURN QUERY SELECT UNNEST(v_return_data), UNNEST(v_return_horario), UNNEST(v_return_horariooferta), UNNEST(v_return_professor), UNNEST(v_return_physicalresourceid), UNNEST(v_return_physicalresourceversion) order by 1,2;
END;
$BODY$
LANGUAGE 'plpgsql';
