CREATE OR REPLACE VIEW cr_acp_boletim AS (
    SELECT DISTINCT I.sigla,
                    I.endereco,
                    E.inscricaoid AS cod_inscricao,
                    C.ofertaComponenteCurricularId AS cod_oferta_disciplina,
                    D.ofertaTurmaId AS cod_turma,
                    J.personId AS cod_aluno,
                    J.name AS nome_aluno,
                    D.descricao,
                    K.nomeParaDocumentos,
                    K.nome AS titulacao,
                    A.nome AS disciplina,
                    K.valorMinimoAprovacao,
                    L.cargaHoraria,
                    M.frequencia,
                    (CASE WHEN O.ausencia IS NULL 
                          THEN
                               0
                          ELSE 
                               O.ausencia
                     END) AS ausencia,
                    N.nota,
                    G.notaFinal,
                    (CASE WHEN M.frequencia >= L.cargaHoraria 
                          THEN
                               ROUND( 100::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::integer) || ' %'
                          WHEN M.frequencia IS NULL 
                          THEN
                               ' '
                          ELSE
                               ROUND( ((M.frequencia * 100) / L.cargaHoraria)::NUMERIC, getParameter('BASIC', 'GRADE_ROUND_VALUE')::integer) || ' %'
                     END) AS percentual_frequencia,
                    (CASE WHEN G.notaFinal IS NULL 
                          THEN
                               'SN'
                          WHEN G.notaFinal >= K.valorMinimoAprovacao 
                          THEN
                               'APR'
                          ELSE 
                               'REP' 
                     END) AS resultado
               FROM acpComponenteCurricular A
         INNER JOIN acpComponenteCurricularMatriz B
                 ON A.componenteCurricularId = B.componenteCurricularId
         INNER JOIN acpOfertaComponenteCurricular C
                 ON B.componenteCurricularMatrizId = C.componenteCurricularMatrizId
         INNER JOIN acpOfertaTurma D
                 ON C.ofertaTurmaId = D.ofertaTurmaId
         INNER JOIN acpInscricaoTurmaGrupo E
                 ON C.ofertaTurmaId = E.ofertaTurmaId
         INNER JOIN acpComponenteCurricularDisciplina F
                 ON A.componenteCurricularId = F.componenteCurricularId
         INNER JOIN acpMatricula G
                 ON (C.ofertaComponenteCurricularId = G.ofertaComponenteCurricularId 
                AND E.inscricaoTurmaGrupoId = G.inscricaoTurmaGrupoId)
         INNER JOIN acpInscricao H
                 ON E.inscricaoId = H.inscricaoId
         INNER JOIN basPhysicalPersonStudent J
                 ON H.personId = J.personId
         INNER JOIN (SELECT MIN(H1.componenteDeAvaliacaoId),
                            A1.CursoId,
                            H1.valorMinimoAprovacao,
                            C1.ofertaCursoId,
                            A1.nomeParaDocumentos,
                            D1.nome
                       FROM acpCurso A1
                 INNER JOIN acpOcorrenciaCurso B1
                         ON A1.cursoId = B1.cursoId
                 INNER JOIN acpOfertaCurso C1
                         ON B1.ocorrenciaCursoId = C1.ocorrenciaCursoId
                 INNER JOIN acpGrauAcademico D1
                         ON A1.grauAcademicoId = D1.grauAcademicoId
                 INNER JOIN acpPerfilCurso E1
                         ON A1.perfilCursoId = E1.perfilcursoId
                 INNER JOIN acpModeloDeAvaliacao F1
                         ON E1.modeloDeAvaliacaoGeral = F1.modeloDeAvaliacaoId
                 INNER JOIN acpComponenteDeAvaliacao G1
                         ON F1.modeloDeAvaliacaoId = G1.modeloDeAvaliacaoId
                 INNER JOIN acpComponenteDeAvaliacaoNota H1
                         ON G1.componenteDeAvaliacaoId = H1.componenteDeAvaliacaoId
                   GROUP BY A1.CursoId,
                            H1.valorMinimoAprovacao,
                            C1.ofertaCursoId,
                            A1.nomeParaDocumentos,
                            D1.nome) K
                 ON H.ofertaCursoId = K.ofertaCursoId
         INNER JOIN acpComponenteCurricularDisciplina L
                 ON A.componenteCurricularId = L.componenteCurricularId
          LEFT JOIN (SELECT A1.matriculaId,
                            SUM(D1.minutosfrequencia/60::numeric)::numeric AS frequencia
                       FROM acpMatricula A1
                 INNER JOIN acpFrequencia B1
                         ON (A1.matriculaId = B1.matriculaId 
                        AND B1.frequencia = 'P')
                 INNER JOIN acpOcorrenciaHorarioOferta C1
                         ON B1.ocorrenciaHorarioOfertaId = C1.ocorrenciaHorarioOfertaId
                 INNER JOIN acpHorario D1
                         ON C1.horarioId = D1.horarioId
                   GROUP BY A1.matriculaId) M
                 ON G.matriculaId = M.matriculaId
          LEFT JOIN acpAvaliacao N
                 ON G.matriculaId = N.matriculaId
          LEFT JOIN (SELECT A1.matriculaId,
                            COUNT(B1.frequencia) AS ausencia
                       FROM acpMatricula A1
                 INNER JOIN acpFrequencia B1
                         ON (A1.matriculaId = B1.matriculaId 
                        AND B1.frequencia = 'A')
                   GROUP BY A1.matriculaId ) O
                 ON G.matriculaId = O.matriculaId
          LEFT JOIN (SELECT AA.companyId,
                            AA.name AS razao_social,
                            AA.acronym AS sigla,
                            BB.cnpj,
                            CC.name || ' ' || BB.location || ', ' || BB.number AS endereco
                       FROM basCompanyConf AA
                 INNER JOIN basLegalPerson BB
                         ON AA.personId = BB.personId
                 INNER JOIN basLocationType CC
                         ON BB.locationTypeId = CC.locationTypeId
                 INNER JOIN basCity DD
                         ON BB.cityId = DD.cityId) I
                 ON I.companyId = getParameter('BASIC', 'DEFAULT_COMPANY_CONF')::int
);