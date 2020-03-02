CREATE OR REPLACE VIEW rptPessoa AS
               (SELECT A.personid,
                       A.name,
                       A.name AS personname,
                       A.personId || ' - ' || A.name AS nomecod,
                       A.cityid,
                       B.name AS cityname,
                       A.zipcode,
                       A.location,
                       A.number,
                       COALESCE(A.complement, '-') AS complement,
                       A.neighborhood,
                       A.email,
                       A.sex,
                       A.maritalstatusid,
                       (COALESCE(A.residentialPhone, (SELECT phone
                                                        FROM basPhone
                                                       WHERE personId = A.personId
                                                         AND type = 'RES' 
                                                       LIMIT 1))) AS residentialphone,
                       (COALESCE(A.cellPhone, (SELECT phone
                                                 FROM basPhone
                                                WHERE personId = A.personId
                                                  AND type = 'CEL' 
                                                LIMIT 1))) AS cellphone,
                       A.datebirth,
                       C.description,
                       C.description AS necessidadeespecial,
                       D.content AS cpf,
                       E.content AS rg,
                       E.organ AS organ,
                       COALESCE(DOCTITULO.content, '-') AS tituloeleitor,
                       COALESCE(obterCidadeEstado(E.cityId), '-') AS rgcity,
                       A.miolousername,
                       dataporextenso(now()::date) AS dataporextenso,
                       datetouser(now()::date) AS datahoje,
                       timestamptouser(now()::timestamp) AS datahorahoje,
                       COALESCE(A.workfunction, '-') AS workfunction,
                       datetouser(A.datebirth) AS datanascimento,
                       (CASE WHEN pai.name IS NOT NULL OR mae.name IS NOT NULL
                        THEN
                            ARRAY_TO_STRING(ARRAY(SELECT DISTINCT UNNEST(ARRAY[pai.name, mae.name])), ' e ')
                        ELSE
                            '-'
                        END) AS filiacao,
                       COALESCE(mae.name, A.mothername, '-') AS nomemae,
                       COALESCE(pai.name, A.fathername, '-') AS nomepai,
                       COALESCE(DOCMIL.content, '-') AS certificadomilitar,
                       COALESCE(A.location, '') || ', NO. ' || COALESCE(A.number, '') || ', BAIRRO ' || COALESCE(A.neighborhood, '') AS endereco,
                       obterCidadeEstado(A.cityId) AS cidadeestado,
                       obterCidadeEstado(A.cityIdBirth) AS naturalidade,
                       S.institutionidhs,
                       getpersonname(S.institutionidhs) AS ensmedioescola,
                       S.cityidhs,
                       obtercidadeestado(S.cityidhs) AS ensmediocidade,
                       S.yearhs,
                       COALESCE(ms.description, '-') AS estadocivil,
                       A.neighborhood AS bairro,
                       A.location AS rua,
                       A.countryIdBirth,
--                       COALESCE(CNT.name, '-') AS nacionalidade, --Mauricio: 07/10/2014
                       COALESCE(CNT.nationality, '-') AS nacionalidade,
                       A.mothername,
                       A.fathername,
                       (CASE WHEN A.fathername IS NOT NULL OR A.mothername IS NOT NULL
                        THEN
                            ARRAY_TO_STRING(ARRAY(SELECT DISTINCT UNNEST(ARRAY[A.mothername, A.fathername])), ' e ')
                        ELSE
                            '-'
                        END) AS filiation,
                        (CASE WHEN A.sex = 'F' THEN 'FEMININO' ELSE 'MASCULINO' END) AS sexo,
                       A.locationTypeId AS codigo_logradouro,
                       LT.name AS logradouro,
                       STA.stateId AS uf_estado,
                       STA.name AS nome_estado,
                       A.cityIdWork AS codigo_cidade_trabalho,
                       (SELECT name
                          FROM basCity
                         WHERE cityId = A.cityIdWork) AS nome_cidade_trabalho,
                       A.locationTypeIdWork AS codigo_logradouro_trabalho,
                       (SELECT name
                          FROM basLocationType
                         WHERE locationTypeId = A.locationTypeIdWork) AS logradouro_trabalho,
                       A.workEmployerName AS nome_empregador,
                       A.locationWork AS endereco_trabalho,
                       A.neighborhoodWork AS bairro_trabalho,
                       (SELECT stateId
                          FROM basCity
                         WHERE cityId = A.cityIdWork) AS uf_estado_trabalho,
                       (SELECT name
                          FROM basState
                         WHERE (stateId, countryId) = (SELECT stateId, countryId
                                                         FROM basCity
                                                        WHERE cityId = A.cityIdWork)) AS nome_estado_trabalho,
                       A.zipCodeWork AS cep_trabalho,
                       (COALESCE(A.workPhone, (SELECT phone
                                                 FROM basPhone
                                                WHERE personId = A.personId
                                                  AND type = 'PRO' 
                                                LIMIT 1))) AS telefone_trabalho,
                       A.responsableLegalId AS codigo_pessoa_responsavel,
                       getPhysicalPersonAge(A.personId) AS idade,
                       getPersonName(A.responsableLegalId) AS nome_pessoa_responsavel,
                       (LT.name || ' ' || A.location || ', ' || A.number || ', ' ||
                           (CASE WHEN A.complement IS NOT NULL
			         THEN
			              A.complement || ', ' || A.neighborhood
                                 ELSE
                                      A.neighborhood
                            END) || ', ' || B.name || ', ' || STA.stateId || ' - ' || CY.name) AS endereco_completo,
                       A.emailalternative AS email_alternativo,
                       FI.filepath || FI.fileId AS caminho_da_foto,
                       (SELECT name
                          FROM basCity
                         WHERE cityId = A.cityIdBirth) AS nome_cidade_naturalidade,
                       (SELECT name
                          FROM basCity
                         WHERE cityId = S.cityidhs) AS nome_cidade_ensmedio,
                       S.externalcourseidhs AS codigo_curso_externo,
                       (SELECT name
                          FROM acdexternalcourse
                         WHERE externalcourseid = S.externalcourseidhs) AS curso_externo,
                       S.institutionidhs AS codigo_instituicao_curso_externo,
                       (SELECT name
                          FROM basLegalPerson
                         WHERE personId = S.institutionidhs) AS instituicao_curso_externo,
                       A.carPlate AS placa_do_carro_da_pessoa_responsavel
             FROM ONLY basphysicalperson A
        LEFT JOIN ONLY basPhysicalPersonStudent S
                    ON A.personId = S.personId
        LEFT JOIN ONLY baslegalperson LP
                    ON LP.personid = S.institutionidhs
             LEFT JOIN basLocationType LT
                    ON LT.locationTypeId = A.locationTypeId
             LEFT JOIN bascity B
                    ON A.cityid = B.cityid
             LEFT JOIN basspecialnecessity C
                    ON A.specialnecessityid = C.specialnecessityid
             LEFT JOIN basdocument D
                    ON A.personid = D.personid
                   AND D.documenttypeid = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::int
             LEFT JOIN basdocument E
                    ON A.personid = E.personid
                   AND E.documenttypeid = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_RG')::int
             LEFT JOIN basDocument DOCMIL
                    ON DOCMIL.personId = A.personId
                   AND DOCMIL.documentTypeId = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_MILITARY')::int
             LEFT JOIN basDocument DOCTITULO
                    ON DOCTITULO.personId = A.personId
                   AND DOCTITULO.documentTypeId = GETPARAMETER('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_ELECTION_TITLE')::int
             LEFT JOIN basPhysicalPersonKinship pkmae
                    on pkmae.personid = A.personid
                   and pkmae.kinshipid = GETPARAMETER('BASIC', 'MOTHER_KINSHIP_ID')::int
             LEFT JOIN basPhysicalPersonKinship pkpai
                    on pkpai.personid = A.personid
                   and pkpai.kinshipid = GETPARAMETER('BASIC', 'FATHER_KINSHIP_ID')::int
        LEFT JOIN ONLY basperson mae
                    ON mae.personId = pkmae.relativePersonId
        LEFT JOIN ONLY basperson pai
                    ON pai.personId = pkpai.relativePersonId
             LEFT JOIN basmaritalstatus ms
                    ON ms.maritalstatusid = a.maritalstatusid
             LEFT JOIN basCountry CNT
                    ON A.countryIdBirth = CNT.countryId
             LEFT JOIN basState STA
                    ON (B.stateId = STA.stateId)
                   AND (B.countryId = STA.countryId)
             LEFT JOIN basCountry CY
                    ON CY.countryId = STA.countryId
             LEFT JOIN basFile FI
                    ON FI.fileId = A.photoId
        );

COMMENT ON VIEW rptPessoa IS 'Pessoas';
COMMENT ON COLUMN rptPessoa.personid IS 'Código da pessoa';
COMMENT ON COLUMN rptPessoa.name IS 'Nome';
COMMENT ON COLUMN rptPessoa.cityid IS 'Código da cidade';
COMMENT ON COLUMN rptPessoa.cityname IS 'Cidade';
COMMENT ON COLUMN rptPessoa.zipcode IS 'CEP';
COMMENT ON COLUMN rptPessoa.location IS 'Estado';
COMMENT ON COLUMN rptPessoa.number IS 'Número';
COMMENT ON COLUMN rptPessoa.complement IS 'Complemento';
COMMENT ON COLUMN rptPessoa.neighborhood IS 'Bairro';
COMMENT ON COLUMN rptPessoa.email IS 'Email';
COMMENT ON COLUMN rptPessoa.sex IS 'Sexo';
COMMENT ON COLUMN rptPessoa.maritalstatusid IS 'Estado civil';
COMMENT ON COLUMN rptPessoa.residentialphone IS 'Telefone residencial';
COMMENT ON COLUMN rptPessoa.cellphone IS 'Telefone celular';
COMMENT ON COLUMN rptPessoa.datebirth IS 'Data de nascimento';
COMMENT ON COLUMN rptPessoa.description IS 'Nescecidade especial';
COMMENT ON COLUMN rptPessoa.cpf IS 'CPF';
COMMENT ON COLUMN rptPessoa.rg IS 'RG';
COMMENT ON COLUMN rptPessoa.miolousername IS 'Login miolo';
