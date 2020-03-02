CREATE OR REPLACE FUNCTION cr_acd_disciplina_oferecida_matricula(p_periodid VARCHAR DEFAULT NULL, p_contractId INT DEFAULT NULL)
RETURNS TABLE(
    codigo_aluno BIGINT,
    nome_aluno VARCHAR,
    codigo_contrato INT,
    codigo_curso VARCHAR,
    versao_curso INT,
    curso TEXT,
    codigo_turno INT,
    turno VARCHAR,
    codigo_unidade INT,
    unidade TEXT,
    codigo_matricula INT,
    codigo_oferecida INT,
    codigo_disciplina VARCHAR,
    versao_disciplina INT,
    periodo VARCHAR,
    codigo_status_aluno INT,
    status_aluno TEXT,
    frequencia_horas VARCHAR,
    frequencia_percentual VARCHAR,
    nota_final FLOAT,
    esta_fechada BOOLEAN,
    cod_tipo_de_disciplina INT,
    descricao_tipo_de_disciplina TEXT,
    e_disciplina_de_tipo_tcc BOOLEAN,
    data_de_emissao VARCHAR,
    codigo_professor BIGINT,
    professor VARCHAR,
    semestre_aluno_curso INT,
    cpf TEXT,
    area TEXT,
    carga_horaria NUMERIC,
    semestre_disciplina INT,
    nome_disciplina TEXT,
    codigo_curriculo INT,
    codigo_curriculo_oferecida INT,
    status_matricula_abreviado VARCHAR,
    codigo_disciplina_matriz VARCHAR,
    versao_disciplina_matriz  INT,
    nome_disciplina_matriz TEXT,
    codigo_tipo_disciplina_matriz INT,
    carga_horaria_disciplina_matriz NUMERIC,
    creditos_disciplina_matriz NUMERIC,
    nota_ou_conceito_final VARCHAR
) AS
$BODY$
BEGIN
    RETURN QUERY (
        SELECT DISTINCT personid::BIGINT AS codigo_aluno,
                        getPersonName(personid) AS nome_aluno,
                        contractid AS codigo_contrato,
                        courseid AS codigo_curso,
                        courseversion AS versao_curso,
                        coursename AS curso,
                        turnid AS codigo_turno,
                        turn AS turno,
                        unitid AS codigo_unidade,
                        unit AS unidade,
                        enrollid AS codigo_matricula,
                        groupid AS codigo_oferecida,
                        curricularcomponentid AS codigo_disciplina,
                        curricularcomponentversion AS versao_disciplina,
                        periodid AS periodo,
                        enrollstatusid AS codigo_status_aluno,
                        enroll_status AS status_aluno,
                        ROUND((obterMinutosCursadosPeloAlunoNaOferecida(enrollid) / 60), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)::VARCHAR AS frequencia_horas,
                        rptDisciplinaAluno.frequencia_percentual,
                        finalnote::FLOAT AS nota_final,
                        isclosed AS esta_fechada,
                        codigo_tipo_de_disciplina AS cod_tipo_de_disciplina,
                        tipo_de_disciplina AS descricao_tipo_de_disciplina,
                        e_disciplina_de_tcc AS e_disciplina_de_tipo_tcc,
                        dataporextenso(now()::Date) AS data_de_emissao,
                        profpersonid AS codigo_professor,
                        getPersonName(profpersonid) AS professor,
                        get_semester_contract(contractId) AS semestre_aluno_curso,
                        (SELECT content
                           FROM basDocument
                          WHERE personId = rptDisciplinaAluno.personid
                            AND documentTypeId = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT
                          LIMIT 1) AS cpf,
                        (SELECT description
                           FROM acdeducationarea
                          WHERE educationareaid = (SELECT educationareaid
                                                     FROM acdCourse
                                                    WHERE courseId = rptDisciplinaAluno.courseid)) AS area,
                        cargahoraria::NUMERIC AS carga_horaria,
                        semestre_disciplina_matriz_aluno AS semestre_disciplina,
                        disciplina AS nome_disciplina,
                        curriculumId_disciplina_matriz_aluno AS codigo_curriculo,
                        curriculumid AS codigo_curriculo_oferecida,
                        abbreviation AS status_matricula_abreviado,
                        codigo_disciplina_matriz_aluno AS codigo_disciplina_matriz,
                        versao_disciplina_matriz_aluno AS versao_disciplina_matriz,
                        nome_disciplina_matriz_aluno AS nome_disciplina_matriz,
                        codigo_tipo_disciplina_matriz_aluno AS codigo_tipo_disciplina_matriz,
                        carga_horaria_disciplina_matriz_aluno::NUMERIC AS carga_horaria_disciplina_matriz,
                        creditos_disciplina_matriz_aluno::NUMERIC AS creditos_disciplina_matriz,
                        obterNotaOuConceitoFinal(enrollid) AS nota_ou_conceito_final
                   FROM rptDisciplinaAluno
                  WHERE (CASE WHEN p_periodid IS NULL
                              THEN
                                   TRUE
                              ELSE
                                   (periodid = p_periodid)
                         END)
                    AND (CASE WHEN p_contractId IS NULL
                              THEN
                                   TRUE
                              ELSE
                                   (contractid = p_contractId)
                         END)
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
