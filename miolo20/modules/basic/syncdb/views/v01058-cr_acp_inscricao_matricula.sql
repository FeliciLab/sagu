CREATE OR REPLACE VIEW cr_acp_inscricao_matricula AS (
        SELECT COALESCE(acpinscricao.inscricaoid,0) AS acpinscricao_inscricaoid,
               COALESCE(acpinscricao.personid,0) AS acpinscricao_personid,
               COALESCE(acpinscricao.situacao,'') AS acpinscricao_situacao,
               dateToUser(acpinscricao.datasituacao::DATE) AS  acpinscricao_datasituacao,
               COALESCE(acpinscricao.origem,'') AS  acpinscricao_origem,
               COALESCE(acpinscricao.PrecoCondicaoInscricaoId,0) AS  acpinscricao_precocursoid,
               COALESCE(acpinscricao.diadevencimentoid,0) AS  acpinscricao_diadevencimentoid,
               COALESCE(acpinscricao.precocondicaoinscricaoid,0) AS  acpinscricao_precocondicaoinscricaoid,
               COALESCE(acpinscricao.unitid,0) AS acpinscricao_unitid,
               COALESCE(acpinscricao.ofertacursoid,0) AS  acpinscricao_ofertacursoid,
               COALESCE(acpinscricao.notafinal,0) AS  acpinscricao_notafinal,
               COALESCE(acpinscricao.conceitofinal,'') AS  acpinscricao_conceitofinal,
               COALESCE(acpinscricao.parecerfinal,'') AS  acpinscricao_parecerfinal,
               COALESCE(acpinscricao.convenantid,0) AS  acpinscricao_convenantid,
               COALESCE(acpinscricaoturmagrupo.notafinal,0) AS acpinscricaoturmagrupo_notafinal,
               COALESCE(acpinscricaoturmagrupo.conceitofinal,'') AS acpinscricaoturmagrupo_conceitofinal,
               COALESCE(acpinscricaoturmagrupo.parecerfinal,'') AS acpinscricaoturmagrupo_parecerfinal,
               COALESCE(acpcomponentecurricular.codigo,'') AS acpcomponentecurricular_codigo,
               COALESCE(acpcomponentecurricular.componentecurricularid,0) AS acpcomponentecurricular_componentecurricularid,
               COALESCE(acpcomponentecurricular.nome,'') AS acpcomponentecurricular_nome,
               COALESCE(acpcomponentecurricular.descricao,'') AS acpcomponentecurricular_descricao,
               COALESCE(acpcomponentecurriculardisciplina.cargahoraria,0) AS acpcomponentecurriculardisciplina_cargahoraria,
               COALESCE(acpcontroledefrequencia.limitedefrequencia,0) AS acpcontroledefrequencia_limitedefrequencia,
               COALESCE(acpcurso.cursoid,0)  AS acpcurso_cursoid,
               COALESCE(acpcurso.codigo,'')  AS acpcurso_codigo,
               COALESCE(acpcurso.nome,'') AS acpcurso_nome,
               COALESCE(acpcurso.nomeparadocumentos,'') AS acpcurso_nomeparadocumentos,
               acpfrequencia.frequencia AS acpfrequencia_frequencia,
               COALESCE(acphorario.diasemana,0) AS acphorario_diasemana,
               acphorario.horafim AS acphorario_horafim,
               acphorario.horainicio AS acphorario_horainicio,
               COALESCE(basphysicalperson.personid,0) AS acphorarioofertacomponentecurricular_personid,
               COALESCE(basphysicalperson.name, '') AS basphysicalpersonstudent_name,
               COALESCE(basphysicalperson.personid,0) AS basphysicalpersonstudent_personid,
               COALESCE(basphysicalperson.specialnecessityid,0) AS basphysicalpersonstudent_specialnecessityid,
               COALESCE(basphysicalperson.email,'') AS basphysicalpersonstudent_email,
               acpocorrenciahorariooferta.dataaula AS acpocorrenciahorariooferta_dataaula,
               COALESCE(acpofertacomponentecurricular.ofertacomponentecurricularid,0) AS acpofertacomponentecurricular_ofertacomponentecurricularid,
               acpofertaturma.datafinalaulas AS acpofertacurso_datafinalaulas,
               acpofertaturma.datafinaloferta AS acpofertacurso_datafinaloferta,
               acpofertaturma.datainicialaulas AS acpofertacurso_datainicialaulas,
               acpofertaturma.datafinalaulas AS acpofertaturma_datafinalaulas,
               acpofertaturma.datafinaloferta AS acpofertaturma_datafinaloferta,
               acpofertaturma.datainicialaulas AS acpofertaturma_datainicialaulas,
               acpofertaturma.datainicialoferta AS acpofertacurso_datainicialoferta,
               COALESCE(acpofertaturma.descricao,'') AS acpofertaturma_descricao,
               COALESCE((SELECT basdocument.content 
                           FROM basdocument 
                          WHERE personid=acpinscricao.personid 
                            AND documenttypeid = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int),'') AS basdocument_content,
               getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int AS basdocumenttype_documenttypeid,
               COALESCE(basphysicalpersonprofessor.name,'') AS basphysicalpersonprofessor_name,
               COALESCE(basphysicalpersonprofessor.personid,0) AS basphysicalpersonprofessor_personid,
               COALESCE(basunit.description,'') AS basunit_description,
               COALESCE(insphysicalresource.room,'') AS insphysicalresource_room,
               COALESCE(acpmatricula.matriculaid,0) AS acpmatricula_matriculaid,
               COALESCE(acpmatricula.situacao,'') AS acpmatricula_situacao,
               COALESCE(acpmatricula.estadodematriculaid,0) AS acpmatricula_estadodematriculaid,
               COALESCE(acpmatricula.notafinal,0) AS acpmatricula_notafinal,
               COALESCE(acpmatricula.faltas,0) AS acpmatricula_faltas,
               COALESCE(acpmatricula.inscricaoturmagrupoid,0) AS acpmatricula_inscricaoturmagrupoid,
               COALESCE(acpofertaturma.ofertaturmaid,0) AS acpofertaturma_ofertaturmaid,
               (SELECT min(horainicio) 
                  FROM acphorario 
                 WHERE acpgradehorario.gradehorarioid = gradehorarioid) AS horario_inicial_grade_turma,
               (SELECT max(horafim) 
                  FROM acphorario 
                 WHERE acpgradehorario.gradehorarioid = gradehorarioid) AS horario_final_grade_turma,
               COALESCE(acpmatricula.conceitofinal, NULL) AS acpmatricula_conceitofinal,
               (CASE acpinscricao.situacao 
                   WHEN 'I' 
                   THEN 
                       (CASE WHEN acptransferenciadeturma.transferenciadeturmaid IS NOT NULL
                           THEN 
                               'Transferido de turma' 
                           ELSE 
                               'Inscrito' 
                        END) 
                   WHEN 'C' 
                   THEN 
                       'Cancelado' 
                   ELSE 
                       'Pendente' 
                END) AS descricao_situacao,
               (CASE 
                   WHEN acptransferenciadeturma.transferenciadeturmaid IS NOT NULL
                   THEN 
                       'Transferencia de turma' 
                   WHEN acpinscricao.origem = 'S' 
                   THEN 
                       'Site' 
                   WHEN acpinscricao.origem = 'A' 
                   THEN 
                       'Administrativo' 
                   WHEN acpinscricao.origem = 'P' 
                   THEN 
                       'Processo seletivo' 
                   ELSE 
                       '' 
                END) AS descricao_origem,
               COALESCE((SELECT basdocument.content 
                           FROM basdocument 
                          WHERE personid=acpinscricao.personid 
                            AND documenttypeid = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::int),'') AS basdocument_content_rg,
               COALESCE(basphysicalperson.workPhone, PP.phone) AS telefone_trabalho,
               COALESCE(basphysicalperson.residentialPhone, PR.phone) AS telefone_residencial,
               COALESCE(basphysicalperson.cellPhone, PC.phone) AS telefone_celular,
               basphysicalperson.location AS logradouro,
               basphysicalperson.number AS numero,
               basphysicalperson.complement AS complemento,
               basphysicalperson.neighborhood AS bairro,
               basphysicalperson.cityId AS codigo_cidade,
               CP.name AS cidade,
               basphysicalperson.zipcode AS cep,
               acpofertaturma.codigo AS codigo_oferta_turma,
               getPersonName(acpinscricao.personid) AS nome_aluno,
               getPersonDocument(acpinscricao.personid, getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int) AS basdocument_content_cpf,
               (CASE WHEN age(basphysicalperson.datebirth) < '18 years' THEN 'Sim' ELSE 'Não' END) AS menordeidade,
               COALESCE((SELECT basdocument.organ
                           FROM basdocument 
                          WHERE personid = acpinscricao.personid 
                            AND documenttypeid = getParameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::int),'') AS basdocument_organ_rg,
               TO_CHAR(NOW()::date, getParameter('BASIC', 'MASK_DATE')) AS data_hoje,
               dataPorExtenso(NOW()::date) AS data_hoje_por_extenso,
               (SELECT CT.name
                  FROM basLocation LT
            INNER JOIN basCity CT
                    ON CT.cityId = LT.cityId
                 WHERE LT.locationId = acpofertaturma.localId) AS cidade_local_curso,
               COALESCE(COALESCE(basphysicalperson.workPhone, PP.phone) || ', ', '') || COALESCE(COALESCE(basphysicalperson.residentialPhone, PR.phone) || ', ', '') || COALESCE(COALESCE(basphysicalperson.cellPhone, PC.phone) || ', ', '') AS todos_telefones,
               (LT.name || ' ' || basphysicalperson.location || ', ' || basphysicalperson.number || ', ' ||
                   (CASE WHEN basphysicalperson.complement IS NOT NULL
                         THEN
                              basphysicalperson.complement || ', ' || basphysicalperson.neighborhood
                         ELSE
                              basphysicalperson.neighborhood
                    END) || ', ' || CP.name || ', ' || STA.stateId || ' - ' || CY.name) AS endereco_completo,
               basphysicalperson.workfunction AS profissao,
               acpOfertaCurso.descricao AS oferta_curso,
               (CASE WHEN (SELECT COUNT(*) > 0
                             FROM finConvenant
			    WHERE personId = basphysicalperson.personId)
                     THEN
			  'SIM'
		     ELSE
                          'NÃO'
		END) AS possui_beneficios_financeiros,
               dateToUser(basphysicalperson.datebirth) AS data_nascimento,
               MS.description AS estado_civil,
               basphysicalperson.workEmployerName AS nome_empregador,
               COALESCE(basphysicalperson.emailalternative, '') AS email_alternativo,
               FI.filepath || FI.fileId AS caminho_da_foto,
               acp_obterCreditosCursados(acpcurso.cursoid, basphysicalperson.personId) AS total_creditos_cursados,
               dateToUser(acpofertaturma.datafinalaulas::DATE) AS acpofertacurso_datafinalaulas_formatada,
               dateToUser(acpofertaturma.datafinaloferta::DATE) AS acpofertacurso_datafinaloferta_formatada,
               dateToUser(acpofertaturma.datainicialaulas::DATE) AS acpofertacurso_datainicialaulas_formatada,
               dateToUser(acpofertaturma.datafinalaulas::DATE) AS acpofertaturma_datafinalaulas_formatada,
               dateToUser(acpofertaturma.datafinaloferta::DATE) AS acpofertaturma_datafinaloferta_formatada,
               dateToUser(acpofertaturma.datainicialaulas::DATE) AS acpofertaturma_datainicialaulas_formatada,
               dateToUser(acpofertaturma.datainicialoferta::DATE) AS acpofertacurso_datainicialoferta_formatada,
               dateToUser(acpocorrenciahorariooferta.dataaula::DATE) AS acpocorrenciahorariooferta_dataaula_formatada
          FROM acpinscricaoturmagrupo
     LEFT JOIN acpmatricula
            ON acpmatricula.inscricaoturmagrupoid = acpinscricaoturmagrupo.inscricaoturmagrupoid
     LEFT JOIN acpofertacomponentecurricular 
            ON acpmatricula.ofertacomponentecurricularid = acpofertacomponentecurricular.ofertacomponentecurricularid
     LEFT JOIN acpocorrenciahorariooferta 
            ON acpocorrenciahorariooferta.ofertacomponentecurricularid=acpofertacomponentecurricular.ofertacomponentecurricularid
     LEFT JOIN acphorario 
            ON acpocorrenciahorariooferta.horarioid = acphorario.horarioid
     LEFT JOIN basphysicalpersonprofessor 
            ON acpocorrenciahorariooferta.professorid = basphysicalpersonprofessor.personid
     LEFT JOIN insphysicalresource 
            ON (acpocorrenciahorariooferta.physicalresourceid = insphysicalresource.physicalresourceid) 
           AND (acpocorrenciahorariooferta.physicalresourceversion = insphysicalresource.physicalresourceversion)
     LEFT JOIN acpcomponentecurricularmatriz 
            ON acpofertacomponentecurricular.componentecurricularmatrizid = acpcomponentecurricularmatriz.componentecurricularmatrizid
     LEFT JOIN acpcomponentecurricular 
            ON acpcomponentecurricularmatriz.componentecurricularid = acpcomponentecurricular.componentecurricularid
     LEFT JOIN acpcomponentecurriculardisciplina 
            ON acpcomponentecurriculardisciplina.componentecurricularid = acpcomponentecurricular.componentecurricularid
     LEFT JOIN acpofertaturma 
            ON COALESCE(acpofertacomponentecurricular.ofertaturmaid, acpinscricaoturmagrupo.ofertaturmaid) = acpofertaturma.ofertaturmaid
     LEFT JOIN acpgradehorario 
            ON acpofertaturma.gradehorarioid = acpgradehorario.gradehorarioid
     LEFT JOIN acpofertacurso 
            ON acpofertaturma.ofertacursoid = acpofertacurso.ofertacursoid
     LEFT JOIN acpocorrenciacurso 
            ON acpofertacurso.ocorrenciacursoid = acpocorrenciacurso.ocorrenciacursoid
     LEFT JOIN acpcurso 
            ON acpocorrenciacurso.cursoid = acpcurso.cursoid
     LEFT JOIN acpperfilcurso 
            ON acpcurso.perfilcursoid = acpperfilcurso.perfilcursoid
     LEFT JOIN acpmodelodeavaliacao 
            ON acpperfilcurso.modelodeavaliacaogeral = acpmodelodeavaliacao.modelodeavaliacaoid
     LEFT JOIN acpcontroledefrequencia 
            ON acpcontroledefrequencia.modelodeavaliacaoid = acpmodelodeavaliacao.modelodeavaliacaoid
     LEFT JOIN acphorarioofertacomponentecurricular 
            ON acphorarioofertacomponentecurricular.ofertacomponentecurricularid = acpofertacomponentecurricular.ofertacomponentecurricularid
     LEFT JOIN acpinscricao 
            ON acpinscricaoturmagrupo.inscricaoid = acpinscricao.inscricaoid
LEFT JOIN ONLY basphysicalperson 
            ON acpinscricao.personid = basphysicalperson.personid
     LEFT JOIN basCity CP
            ON basphysicalperson.cityId = CP.cityId
     LEFT JOIN basState STA
            ON STA.stateId = CP.stateId
     LEFT JOIN basCountry CY
            ON CY.countryId = STA.countryId
LEFT JOIN ONLY basperson 
            ON basphysicalperson.personid = basperson.personid           
     LEFT JOIN acpfrequencia 
            ON acpfrequencia.matriculaid = acpmatricula.matriculaid 
           AND acpfrequencia.ocorrenciahorarioofertaid = acpocorrenciahorariooferta.ocorrenciahorarioofertaid         
     LEFT JOIN basunit 
            ON acpmatricula.unitid = basunit.unitid
     LEFT JOIN basPhone PP
            ON PP.personId = basphysicalperson.personId
           AND PP.type = 'PRO'
     LEFT JOIN basPhone PR
            ON PP.personId = basphysicalperson.personId
           AND PP.type = 'RES'
     LEFT JOIN basPhone PC
            ON PP.personId = basphysicalperson.personId
           AND PP.type = 'CEL'
     LEFT JOIN basMaritalStatus MS
            ON MS.maritalStatusId = basphysicalperson.maritalStatusId
     LEFT JOIN basLocationType LT
            ON LT.locationTypeId = basphysicalperson.locationTypeId
     LEFT JOIN basFile FI
            ON FI.fileId = basphysicalperson.photoId
     LEFT JOIN acptransferenciadeturma
            ON acptransferenciadeturma.personid = basphysicalperson.personid
           AND acptransferenciadeturma.ofertaturmadestino = acpinscricaoturmagrupo.ofertaTurmaId, bascompanyconf       
     LEFT JOIN baslegalperson 
            ON bascompanyconf.personid = baslegalperson.personid
     LEFT JOIN bascity 
            ON baslegalperson.cityid = bascity.cityid
);
