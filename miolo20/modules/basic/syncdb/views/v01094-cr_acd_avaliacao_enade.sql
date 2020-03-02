CREATE OR REPLACE VIEW cr_acd_avaliacao_enade AS
(
        SELECT A.contractId AS codigo_contrato,
               A.testEndCourseTypeId AS codigo_tipo_avaliacao,
               B.description AS tipo_avaliacao,
               dateToUser(B.beginDate) AS data_inicial_vigencia_avaliacao,
               dateToUser(B.endDate) AS data_final_vigencia_avaliacao,
               dateToUser(A.testEndCourseDate) AS data_avaliacao,
               A.excused AS dispensado,
               A.isPresent AS se_fez_presente,
               A.centerId AS codigo_centro, 
               A.mensagemDeAvaliacaoDosAlunosId AS codigo_mensagem_de_avaliacao,
               C.mensagem AS mensagem_de_avaliacao,
               A.notaDoAluno AS nota,
               D.personId AS codigo_pessoa,
               getPersonName(D.personId) AS nome_pessoa,
               getPersonDocument(D.personId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT) AS cpf
          FROM acdTestEndCourseContract A
    INNER JOIN acdTestEndCourseType B
            ON (A.testEndCourseTypeId = B.testEndCourseTypeId)
    INNER JOIN acdContract D
            ON (D.contractId = A.contractId)
     LEFT JOIN acdMensagemDeAvaliacaoDosAlunos C
            ON (A.mensagemDeAvaliacaoDosAlunosId = C.mensagemDeAvaliacaoDosAlunosId)
);