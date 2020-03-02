CREATE OR REPLACE FUNCTION acp_gerarOcorrenciaHorarioOferta(p_ofertacursoid int)
RETURNS BOOLEAN AS
$BODY$
/*************************************************************************************
  NAME: acp_gerarhorariosdaofertadocomponente
  PURPOSE: Gera as ocorrências de aulas dinamicamente para uma oferta de componente curricular

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       10/12/2013 Jonas Diel        1. Função criada.
**************************************************************************************/
DECLARE
    v_horarioofertacomponente RECORD;
    v_ofertacomponentecurricular RECORD;
    v_ofertaturma RECORD;
    v_ofertacurso RECORD;
    v_ocorrenciacurso RECORD;
    v_curso RECORD;
    v_matrizcurricular RECORD;
    v_perfilcurso RECORD;
    v_gradehorario RECORD;
    v_horariosOcorrencias RECORD;
    v_evento RECORD;
    v_datassemaula DATE[];
    v_datainicial DATE;
BEGIN

    SELECT INTO v_ofertacurso * FROM acpofertacurso WHERE ofertacursoid = p_ofertacursoid;
    SELECT INTO v_ocorrenciacurso * FROM acpocorrenciacurso WHERE ocorrenciacursoid = v_ofertacurso.ocorrenciacursoid;
    SELECT INTO v_curso * FROM acpcurso WHERE cursoid = v_ocorrenciacurso.cursoid;
    SELECT INTO v_matrizcurricular * FROM acpmatrizcurricular WHERE cursoid = v_curso.cursoid;
    SELECT INTO v_perfilcurso * FROM acpperfilcurso WHERE perfilcursoid = v_curso.perfilcursoid;
    SELECT INTO v_gradehorario * FROM acpgradehorario WHERE gradehorarioid = v_ofertacurso.gradehorarioid;

    --Carrega datas sem aula segundo calendário acadêmico
    FOR v_evento IN SELECT * FROM acpcalendarioacademicoevento WHERE data BETWEEN v_ofertacurso.datainicialaulas AND v_ofertacurso.datafinalaulas AND ( ocorrenciacursoid IS NULL OR ocorrenciacursoid =  v_ocorrenciacurso.ocorrenciacursoid) AND temaula IS FALSE
    LOOP
        v_datassemaula := ARRAY_APPEND(v_datassemaula,v_evento.data);
    END LOOP;

    --Seta primeira ocorrência de aula como data inicial das aulas da oferta do curso
    v_datainicial := v_ofertacurso.datainicialaulas;

    --Percorre todas ofertas de turma da oferta de curso
    FOR v_ofertaturma IN SELECT * FROM acpofertaturma WHERE ofertacursoid = v_ofertacurso.ofertacursoid
    LOOP
        --Percorre todas ofertas de componentes curriculares da oferta de turma
        FOR v_ofertacomponentecurricular IN SELECT * FROM acpofertacomponentecurricular INNER JOIN acpcomponentecurricular ON acpofertacomponentecurricular.componentecurricularid = acpcomponentecurricular.componentecurricularid WHERE ofertaturmaid = v_ofertaturma.ofertaturmaid ORDER BY acpcomponentecurricular.ordem DESC
        LOOP
            --Forma Sequencial
            IF v_perfilcurso.formacursarcomponentescurriculares = 'S'
            THEN
                FOR v_horariosOcorrencias IN SELECT * FROM acp_obterOcorrenciaSequencial(v_ofertacomponentecurricular.ofertacomponentecurricularid, v_gradehorario.gradehorarioid, v_datainicial, v_datassemaula)
                LOOP
                    INSERT INTO acpOcorrenciaHorarioOferta(horarioofertacomponentecurricularid, dataAula, possuiFrequencia, cancelada, horarioid) VALUES (v_horariosOcorrencias.horarioofertacomponentecurricular, v_horariosOcorrencias.data, false, false, v_horariosOcorrencias.horarioid);
                END LOOP;

                IF v_horariosOcorrencias.data IS NOT NULL
                THEN
                    v_datainicial := v_horariosOcorrencias.data;
                END IF;
                
            ELSE --FIXME Concomitante
                FOR v_horariosOcorrencias IN SELECT * FROM acp_obterOcorrenciaConcomitante(v_ofertacomponentecurricular.ofertacomponentecurricularid, v_gradehorario.gradehorarioid, v_datainicial, v_datassemaula)
                LOOP
                    INSERT INTO acpOcorrenciaHorarioOferta(horarioofertacomponentecurricularid, dataAula, possuiFrequencia, cancelada, horarioid) VALUES (v_horariosOcorrencias.horarioofertacomponentecurricular, v_horariosOcorrencias.data, false, false, v_horariosOcorrencias.horarioid);
                END LOOP;

                IF v_horariosOcorrencias.data IS NOT NULL
                THEN
                    v_datainicial := v_horariosOcorrencias.data;
                END IF;
                
            END IF;
            
        END LOOP;
    END LOOP;
    
    RETURN TRUE;
    
END;
$BODY$
LANGUAGE 'plpgsql';
