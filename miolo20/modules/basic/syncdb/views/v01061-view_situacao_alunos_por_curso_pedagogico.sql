CREATE OR REPLACE VIEW view_situacao_alunos_por_curso_pedagogico AS

    SELECT DISTINCT A.cursoid AS cursoid,
                    A.nome AS nomecurso,
                    T.descricao AS turma,
                    acp_obterSituacaoPedagogicaDaInscricao(I.inscricaoId) AS situacao,
                    U.description AS unidade,
                    D.personid AS codigopessoa,
                    D.name AS nomepessoa,
                    D.email AS emailpessoa,
                    D.residentialphone AS fonerespessoa,
                    D.workphone AS fonetrabpessoa,
                    (SELECT MAX(TO_CHAR(M.datamatricula, 'dd/mm/yyyy')) 
                       FROM acpmatricula M 
                      WHERE M.inscricaoTurmaGrupoId = G.inscricaoTurmaGrupoId) AS datamatricula,
                    T.ofertaturmaid AS ofertaturmaid,
                    I.inscricaoId AS codigo_inscricao,
                    RMPC.formadeconfirmacaoinscricao AS codigo_forma_confirmacao_inscricao_configurada,
                    (CASE RMPC.formadeconfirmacaoinscricao
                          WHEN 'N'
                          THEN
                               'Nenhum'
                          WHEN 'T'
                          THEN
                               'Pagamento da taxa de inscricao'
                          WHEN 'M'
                          THEN
                               'Manual'
                     END) AS forma_confirmacao_inscricao_configurada,
                    acp_verificaSeEstaConfirmadoNaInscricao(I.inscricaoId) AS esta_confirmado_na_inscricao,
                    RMPC.formaDeConfirmacaoMatricula AS codigo_forma_confirmacao_matricula_configurada,
                    (CASE RMPC.formaDeConfirmacaoMatricula
                          WHEN 'N'
                          THEN
                               'Nenhum'
                          WHEN 'X'
                          THEN
                               'Pagamento primeira parcela'
                          WHEN 'P'
                          THEN
                               'Presença na primeira aula'
                          WHEN 'M'
                          THEN
                               'Manual'
                     END) AS forma_confirmacao_matricula_configurada,
                    acp_verificaSeEstaConfirmadoNaMatricula(I.inscricaoId) AS esta_confirmado_na_matricula,
                    (RMPC.formadeconfirmacaoinscricao = 'T') AS curso_possui_taxa_de_inscricao,
                    ROUND(PI.valorAVista, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS preco_avista_taxa_de_inscricao_configurado,
                    acp_verificaSeTaxaDeInscricaoFoiPaga(I.inscricaoId) AS pagou_taxa_de_inscricao,
                    ROUND(PM.valorAVista, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS preco_avista_mensalidade_configurado,
                    ROUND(PM.valorAPrazo, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS preco_aprazo_mensalidade_configurado,
                    (SELECT ROUND(RI.value, getParameter('BASIC', 'REAL_ROUND_VALUE')::INT)
                       FROM prcTituloInscricao TI
            INNER JOIN ONLY finReceivableInvoice RI
                         ON RI.invoiceId = TI.invoiceId
                      WHERE TI.inscricaoId = I.inscricaoId
                        AND RI.isCanceled IS FALSE
                        AND TI.tipo = 'M'
                        AND RI.parcelNumber = 1) AS valor_primeira_parcela,
                    acp_verificaSePrimeiraParcelaFoiPaga(I.inscricaoId) AS pagou_primeira_parcela,
                    PCI.precoCondicaoId AS codigo_preco_condicao_inscricao,
                    PI.precoCursoId AS codigo_preco_curso_inscricao,
                    PCM.precoCondicaoId AS codigo_preco_condicao_matricula,
                    PM.precoCursoId AS codigo_preco_curso_matricula,
                    T.dataInicialOferta AS data_inicial_oferta,
                    T.dataFinalOferta AS data_final_oferta,
                    B.ofertacursoid
               FROM acpInscricao I
         INNER JOIN acpOfertaCurso B
                 ON (I.ofertacursoid = B.ofertacursoid)
         INNER JOIN acpOcorrenciaCurso O
                 ON (O.ocorrenciacursoid = B.ocorrenciacursoid)
         INNER JOIN acpCurso A
                 ON (A.cursoid = O.cursoid)
         INNER JOIN acpPerfilCurso PC
                 ON PC.perfilCursoId = A.perfilCursoId
        INNER JOIN ONLY basPhysicalPerson D
                 ON (I.personid = D.personid)
         INNER JOIN acpInscricaoTurmaGrupo G
                 ON (I.inscricaoid = G.inscricaoid)       
         INNER JOIN acpOfertaTurma T
                 ON (G.ofertaturmaid = T.ofertaturmaid)
          LEFT JOIN acpRegrasMatriculaPerfilCurso RMPC
                 ON RMPC.perfilCursoId = PC.perfilCursoId
          LEFT JOIN basUnit U
                 ON (U.unitid = O.unitid)
          LEFT JOIN prcPrecoCondicao PCI
                 ON PCI.precoCondicaoId = I.precoCondicaoInscricaoId
          LEFT JOIN prcPrecoCurso PI
                 ON PI.precoCursoId = PCI.precoCursoId
          LEFT JOIN prcPrecoCondicao PCM
                 ON PCM.precoCondicaoId = G.precoCondicaoMatriculaId
          LEFT JOIN prcPrecoCurso PM
                 ON PM.precoCursoId = PCM.precoCursoId;
