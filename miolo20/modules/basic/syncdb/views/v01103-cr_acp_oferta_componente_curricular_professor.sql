CREATE OR REPLACE VIEW cr_acp_oferta_componente_curricular_professor AS (
    SELECT DISTINCT OCC.ofertaComponenteCurricularId AS codigo_oferta_componente_curricular,
                    OHO.professorId AS codigo_professor,
                    PPP.name AS professor,
                    PPP.miolousername AS login,
                    PPP.email AS email_professor,
                    dataPorExtenso(NOW()::DATE) as data_hoje_por_extenco,
                    (CASE PPP.posgraduacao
                          WHEN 1 THEN 'Graduado'
                          WHEN 2 THEN 'Especialização'
                          WHEN 3 THEN 'Mestrado'
                          WHEN 4 THEN 'Doutorado'
                     END) AS titulacao_professor,
                    getPersonDocument(OHO.professorId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT) AS cpf,
                    getPersonDocument(OHO.professorId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::INT) AS rg,
                    dateToUser(PPP.dateBirth) AS data_nascimento,
                    MS.description AS estado_civil,
                    LT.locationTypeId AS codigo_tipo_logradouro,
                    LT.name AS tipo_logradouro,
                    PPP.location AS logradouro,
                    PPP.number AS numero_endereco,
                    PPP.complement AS complemento_endereco,
                    PPP.neighborhood AS bairro,
                    C.cityId AS codigo_cidade,
                    C.name AS cidade,
                    C.stateId AS uf_estado,
                    CY.name AS pais_endereco,
                    (LT.name || ' ' || PPP.location || ', ' || PPP.number || ', ' ||
                        (CASE WHEN PPP.complement IS NOT NULL
                              THEN
                                   PPP.complement || ', ' || PPP.neighborhood
                              ELSE
                                   PPP.neighborhood
                         END )
                     || ', ' || C.name || ', ' || C.stateId || ' - ' || CY.name) AS endereco_completo,
                    (COALESCE(PPP.workPhone, (SELECT phone
                                                FROM basPhone
                                               WHERE personId = PPP.personId
                                                 AND type = 'PRO' 
                                               LIMIT 1))) AS telefone_trabalho,
                    (COALESCE(PPP.cellPhone, (SELECT phone
                                                FROM basPhone
                                               WHERE personId = PPP.personId
                                                 AND type = 'CEL' 
                                               LIMIT 1))) AS telefone_celular,
                    (COALESCE(PPP.residentialPhone, (SELECT phone
                                                       FROM basPhone
                                                      WHERE personId = PPP.personId
                                                        AND type = 'RES' 
                                                      LIMIT 1))) AS telefone_residencial,
                    PPP.email AS email,
                    PPP.emailalternative AS email_alternativo,
                    F.filePath || F.fileId AS caminho_da_foto,
                    OT.ofertaTurmaId AS codigo_oferta_turma,
                    OT.habilitada AS oferta_turma_habilitada,
                    OT.codigo AS codigo_abreviado_oferta_turma,
                    OC.ofertaCursoId AS codigo_oferta_curso,
                    OC.descricao AS oferta_curso,
                    OC.situacao AS codigo_situacao_oferta_curso,
                    (CASE OC.situacao
                          WHEN 'A' THEN 'ATIVA'
                          WHEN 'C' THEN 'CANCELADA'
                          WHEN 'E' THEN 'ENCERRADA'
                     END) AS situacao_oferta_curso,
                    OCR.ocorrenciaCursoId AS codigo_ocorrencia_curso,
                    CU.cursoId AS codigo_curso,
                    CU.codigo AS codigo_abreviado_curso,
                    CU.nome AS curso,
                    OCR.unitId AS codigo_unidade_ocorrencia_curso,
                    getUnitDescription(OCR.unitId) AS unidade_ocorrencia_curso,
                    OCR.turnId AS codigo_turno_ocorrencia_curso,
                    getTurnDescription(OCR.turnId) AS turno_ocorrencia_curso,
                    OCR.situacao AS codigo_situacao_ocorrencia_curso,
                    (CASE OCR.situacao
                          WHEN 'A' THEN 'ATIVA'
                          WHEN 'I' THEN 'INATIVA'
                          WHEN 'E' THEN 'EXTINTA'
                     END) AS situacao_ocorrencia_curso,
                    OT.descricao AS oferta_turma,
                    dateToUser(OT.dataInicialOferta) AS data_inicial_oferta_turma,
                    dateToUser(OT.dataFinalOferta) AS data_final_oferta_turma,
                    OT.situacao AS codigo_situacao_oferta_turma,
                    (CASE OT.situacao
                          WHEN 'A' THEN 'ABERTA'
                          WHEN 'F' THEN 'FECHADA'
                     END) AS situacao_oferta_turma,
                    dateToUser(OCC.dataInicio) AS data_inicio_oferta_componente_curricular,
                    dateToUser(OCC.dataFechamento) AS data_fechamento_oferta_componente_curricular,
                    OCC.unitId AS codigo_unidade_oferta_componente_curricular,
                    getUnitDescription(OCC.unitId) AS unidade_oferta_componente_curricular,
                    CCM.componenteCurricularMatrizId AS codigo_componente_curricular_da_matriz,
                    MCG.matrizCurricularGrupoId AS codigo_matriz_curricular_grupo,
                    MCG.descricao AS matriz_curricular_grupo,
                    CC.componenteCurricularId AS codigo_componente_curricular,
                    CC.codigo AS codigo_abreviado_componente_curricular,
                    CC.nome AS componente_curricular,
                    OCC.planoAulas AS plano_de_aula,
                    OCC.metodologia,
                    OCC.avaliacao,
                    CE.centerId AS codigo_centro_oferta_componente_curricular,
                    CE.name AS centro_oferta_componente_curricular
               FROM acpOfertaComponenteCurricular OCC
         INNER JOIN acpOfertaTurma OT
                 ON OT.ofertaTurmaId = OCC.ofertaTurmaId
         INNER JOIN acpOfertaCurso OC
                 ON OC.ofertaCursoId = OT.ofertaCursoId
          LEFT JOIN acpOcorrenciaHorarioOferta OHO
                 ON OHO.ofertaComponenteCurricularId = OCC.ofertaComponenteCurricularId
     LEFT JOIN ONLY basPhysicalPersonProfessor PPP
                 ON PPP.personId = OHO.professorId
          LEFT JOIN basLocationType LT
                 ON LT.locationTypeId = PPP.locationTypeId
          LEFT JOIN basCity C
                 ON C.cityId = PPP.cityId
          LEFT JOIN basState ST
                 ON ST.stateId = C.stateId
          LEFT JOIN basCountry CY
                 ON CY.countryId = ST.countryId
          LEFT JOIN basMaritalStatus MS
                 ON MS.maritalstatusId = PPP.maritalstatusId
          LEFT JOIN basFile F
                 ON F.fileId = PPP.photoId    
          LEFT JOIN acpComponenteCurricularMatriz CCM
                 ON CCM.componenteCurricularMatrizId = OCC.componenteCurricularMatrizId
          LEFT JOIN acpMatrizCurricularGrupo MCG
                 ON MCG.matrizCurricularGrupoId = CCM.matrizCurricularGrupoId
          LEFT JOIN acpComponenteCurricular CC
                 ON CC.componenteCurricularId = CCM.componenteCurricularId
          LEFT JOIN acdCenter CE
                 ON CE.centerId = OCC.centerId
          LEFT JOIN acpOcorrenciaCurso OCR
	         ON OCR.ocorrenciaCursoId = OC.ocorrenciaCursoId
          LEFT JOIN acpCurso CU
	         ON CU.cursoId = OCR.cursoId
);
