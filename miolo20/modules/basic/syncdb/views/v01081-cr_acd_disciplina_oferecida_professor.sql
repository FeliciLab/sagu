CREATE OR REPLACE VIEW cr_acd_disciplina_oferecida_professor AS (   
    SELECT DISTINCT G.groupId AS codigo_oferecida,
                    CO.name as nome_disciplina,
		    SP.professorId AS codigo_professor,
		    PPP.name AS professor,
		    PPP.miolousername AS login,
		    PPP.email AS email_professor,
                    LP.begindate AS data_inicial_periodo,
                    LP.enddate AS data_final_periodo,
                    CL.name AS nome_turma,
                    LP.periodid AS periodo,
                    LP.unitid AS codigo_unidade,
                    UT.description AS unidade,
                    CR.name AS nome_curso,
                    LP.courseversion AS versao_curso,
                    TUR.description AS turno,
                    (SELECT TO_CHAR(MIN(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
                       FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
                               FROM acdSchedule Z
                              WHERE Z.groupId = G.groupId) X) AS data_inicial_das_aulas,
                    (SELECT TO_CHAR(MAX(X.occurrenceDate), getParameter('BASIC', 'MASK_DATE'))
                       FROM (SELECT UNNEST(Z.occurrenceDates) AS occurrenceDate
                               FROM acdSchedule Z
                              WHERE Z.groupId = G.groupId) X) AS data_final_das_aulas,
                    dataPorExtenso(NOW()::DATE) as data_hoje_por_extenco,
                    (CASE PPP.posgraduacao
                          WHEN 1 THEN 'Graduado'
                          WHEN 2 THEN 'Especialização'
                          WHEN 3 THEN 'Mestrado'
                          WHEN 4 THEN 'Doutorado'
                     END) AS titulacao_professor,
                    CR.courseId AS codigo_curso,
                    getPersonDocument(SP.professorId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::INT) AS cpf,
                    getPersonDocument(SP.professorId, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::INT) AS rg,
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
                    TUR.turnId AS codigo_turno
	       FROM acdGroup G
         INNER JOIN acdlearningperiod LP
                 ON LP.learningperiodid = G.learningperiodid
	 INNER JOIN acdSchedule S
	      USING (groupId)
         INNER JOIN acdcurriculum CU
                 ON CU.curriculumid = G.curriculumid
         INNER JOIN acdcurricularcomponent CO
                 ON (CO.curricularcomponentid = CU.curricularcomponentid
                AND CO.curricularcomponentversion = CU.curricularcomponentversion)
         INNER JOIN basunit UT
                 ON UT.unitid = LP.unitid
         INNER JOIN acdcourse CR
                 ON CR.courseid = LP.courseid
         INNER JOIN basturn TUR
                 ON TUR.turnid = LP.turnid
	  LEFT JOIN acdScheduleProfessor SP
                 ON SP.scheduleid = S.scheduleid
     LEFT JOIN ONLY basPhysicalPersonProfessor PPP
    		 ON PPP.personId = SP.professorId
          LEFT JOIN basLocationType LT
                 ON LT.locationTypeId = PPP.locationTypeId
          LEFT JOIN basCity C
                 ON C.cityId = PPP.cityId
          LEFT JOIN basState ST
                 ON ST.stateId = C.stateId
          LEFT JOIN basCountry CY
                 ON CY.countryId = ST.countryId
          LEFT JOIN acdclass CL
                 ON CL.classid = G.classid
          LEFT JOIN basMaritalStatus MS
                 ON MS.maritalstatusId = PPP.maritalstatusId
          LEFT JOIN basFile F
                 ON F.fileId = PPP.photoId
);
