create OR REPLACE view view_acp_frequencia as
Select
acpcomponentecurricular.nome as disciplina,
basphysicalpersonprofessor.personid as codigo_professor,
basphysicalpersonprofessor.name as professor,
acpofertaturma.ofertaturmaid as ofertaturmaid,
acpofertaturma.codigo as codigo_turma,
acpofertaturma.descricao as turma,
acpofertaturma.datainicialoferta as data_inicial,
acpofertaturma.datafinaloferta as data_final,
basphysicalpersonstudent.personid as codigo_aluno,
basphysicalpersonstudent.name as aluno,
to_char(min(acpocorrenciahorariooferta.dataaula),'dd/mm/yyyy') as data_inicial_disciplina,
to_char(max(acpocorrenciahorariooferta.dataaula),'dd/mm/yyyy') as data_final_disciplina,
ROUND(sum(case
    when acpfrequencia.frequencia='P' then acphorario.minutosfrequencia*1
    when acpfrequencia.frequencia='M' then acphorario.minutosfrequencia*0.5
    else acphorario.minutosfrequencia*0 end)/60, 2) as horas_registras_frequencia,
sum(acphorario.minutosfrequencia)/60 as horas_para_frequencia,
((((sum(case
        when acpfrequencia.frequencia='P' then acphorario.minutosfrequencia*1
        when acpfrequencia.frequencia='M' then acphorario.minutosfrequencia*0.5
        else acphorario.minutosfrequencia*0 end)::numeric/60::numeric) /
    (sum(acphorario.minutosfrequencia::numeric)/60::numeric))*10000 )/100)::numeric(10,2) as perc_freq,
acpfrequencia.datalancamento AS datafrequencia,
acpfrequencia.frequencia,
(CASE acpfrequencia.frequencia
    WHEN 'P' THEN 'PRESENÇA'
    WHEN 'A' THEN 'AUSÊNCIA'
    WHEN 'M' THEN 'MEIA PRESENÇA'
    WHEN 'J' THEN 'AUSÊNCIA JUSTIFICADA'
 END) AS descricao_frequencia,
acpfrequencia.justificativa,
C.cursoId AS codigo_curso,
C.codigo AS cod_id_curso,
C.nome AS nome_curso,
CI.cargaHorariaOferecida AS carga_horaria_oferecida,
CI.cargaHorariaCursada AS carga_horaria_cursada,
acp_obtercargahorariatotaldocurso(C.cursoId) AS carga_horaria_total_curso,
ROUND(((CI.cargaHorariaCursada * 100) / acp_obtercargahorariatotaldocurso(C.cursoId)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS frequencia_real_aluno,
ROUND(((CI.cargaHorariaCursada * 100) / CI.cargaHorariaOferecida), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS frequencia_parcial_aluno,
CI.cargaHorariaOferecida AS carga_horaria_encerrada,
CI.cargahorariafrequente AS carga_horaria_frequente,
ROUND(((CI.cargahorariafrequente * 100) / CI.cargaHorariaOferecida), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS frequencia_parcial_no_curso,
ROUND(((CI.cargahorariafrequente * 100) / acp_obtercargahorariatotaldocurso(C.cursoId)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS frequencia_real_no_curso,
CI.cargaHorariaCursada AS carga_horaria_integralizada,
acpmatricula.matriculaid AS codigo_matricula_aluno
From
       acpmatricula
         Left Join acpfrequencia on acpfrequencia.matriculaid=acpmatricula.matriculaid
         Left Join acpofertacomponentecurricular on acpmatricula.ofertacomponentecurricularid=acpofertacomponentecurricular.ofertacomponentecurricularid
         Left Join acpocorrenciahorariooferta on acpocorrenciahorariooferta.ocorrenciahorarioofertaid=acpfrequencia.ocorrenciahorarioofertaid
             Left Join only basphysicalpersonprofessor on acpocorrenciahorariooferta.professorid=basphysicalpersonprofessor.personid
           Left Join acpcomponentecurricularmatriz on acpofertacomponentecurricular.componentecurricularmatrizid=acpcomponentecurricularmatriz.componentecurricularmatrizid
             Left Join acpcomponentecurricular on acpcomponentecurricularmatriz.componentecurricularid=acpcomponentecurricular.componentecurricularid
                   Left Join acphorario on acpocorrenciahorariooferta.horarioid=acphorario.horarioid
         Left Join only basphysicalpersonstudent on acpmatricula.personid=basphysicalpersonstudent.personid
         Left Join acpinscricaoturmagrupo on acpmatricula.inscricaoturmagrupoid=acpinscricaoturmagrupo.inscricaoturmagrupoid
           Left Join acpofertaturma on acpinscricaoturmagrupo.ofertaturmaid=acpofertaturma.ofertaturmaid
         LEFT JOIN acpCursoInscricao CI
                ON CI.personId = basphysicalpersonstudent.personid
         LEFT JOIN acpCurso C
                ON C.cursoId = CI.cursoId 

group by acpcomponentecurricular.nome, 
         basphysicalpersonprofessor.personid, 
         basphysicalpersonprofessor.name, 
         basphysicalpersonstudent.personid, 
         basphysicalpersonstudent.name, acpofertaturma.codigo, 
         acpofertaturma.descricao, acpofertaturma.datainicialoferta, 
         acpofertaturma.datafinaloferta, 
         acpofertaturma.ofertaturmaid, 
         acpfrequencia.datalancamento, 
         acpfrequencia.frequencia, 
         descricao_frequencia, 
         acpfrequencia.justificativa,
         codigo_curso,
         nome_curso,
         carga_horaria_oferecida,
         carga_horaria_cursada,
         carga_horaria_total_curso,
         frequencia_real_aluno,
         frequencia_parcial_aluno,
         carga_horaria_encerrada,
         carga_horaria_frequente,
         frequencia_parcial_no_curso,
         frequencia_real_no_curso,
         carga_horaria_integralizada,
         codigo_matricula_aluno

order by acpofertaturma.codigo, 
         acpcomponentecurricular.nome, 
         basphysicalpersonprofessor.personid, 
         basphysicalpersonstudent.personid;
