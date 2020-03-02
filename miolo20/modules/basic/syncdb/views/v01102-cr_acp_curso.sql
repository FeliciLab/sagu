CREATE OR REPLACE VIEW cr_acp_curso AS (
    SELECT C.cursoId AS codigo_curso,
           C.cursoRepresentanteId AS codigo_curso_representante,
           CR.nome AS curso_representante,
           C.grauAcademicoId AS codigo_grau_academico,
           GA.nome AS grau_academico,
           C.perfilCursoId AS codigo_perfil_curso,
           C.codigo AS codigo_abreviado_curso,
           C.nome AS curso,
           C.nomeparadocumentos AS nome_curso_documentos,
           C.descricao AS descricao_curso,
           C.modalidade,
           C.disciplinasadistancia AS disciplinas_a_distancia,
           C.percentualcargahorariadistancia AS percentual_carga_horaria_a_distancia,
           C.titulacao,
           C.numeroformalvagas AS numero_formal_de_vagas,
           C.situacao AS situacao_curso,
           dateToUser(C.datainicio) AS data_inicio,
           dateToUser(C.datafim) AS data_fim,
           C.gratuito AS curso_e_gratuito,
           C.percentualmultadesistencia AS percentual_multa_desistencia,
           C.centerid AS codigo_centro,
           CE.name AS centro,
           C.centeridold AS codigo_centro_velho,
           O.unitId AS codigo_unidade,
           getUnitDescription(C.unitId) AS unidade,
           C.lancarvalordecancelamento AS lancar_valor_de_cancelamento
      FROM acpCurso C
INNER JOIN acpOcorrenciaCurso O
        ON C.cursoid = O.cursoid
 LEFT JOIN acpCurso CR
        ON CR.cursoId = C.cursoRepresentanteId
 LEFT JOIN acpGrauAcademico GA
        ON GA.grauAcademicoId = C.grauAcademicoId
 LEFT JOIN acdCenter CE
        ON CE.centerId = C.centerId
);
