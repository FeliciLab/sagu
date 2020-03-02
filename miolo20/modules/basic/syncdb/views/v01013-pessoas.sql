CREATE OR REPLACE VIEW pessoas AS
 SELECT a.personid AS id, NULL::UNKNOWN AS identificacao, NULL::UNKNOWN AS titulo_academico, a.name AS nome, a.location AS rua, a.complement AS complemento, a.neighborhood AS bairro, a.zipcode AS cep, a.cityid AS ref_cidade,
        CASE
            WHEN b.personid IS NOT NULL THEN b.phone
            ELSE c.residentialphone
        END AS fone_particular, c.workphone AS fone_profissional, c.messagecontact AS fone_celular, c.cellphone AS fone_recado, a.email, a.emailalternative AS email_alt, c.maritalstatusid AS estado_civil, a.datein AS dt_cadastro,
        CASE
            WHEN b.personid IS NOT NULL THEN 'j'::text
            ELSE 'f'::text
        END AS tipo_pessoa, NULL::UNKNOWN AS obs, c.datebirth AS dt_nascimento, c.sex AS sexo, NULL::UNKNOWN AS credo, b.fakename AS nome_fantasia, b.stateregistration AS cod_inscricao_estadual, getpersondocument(a.personid, 1) AS rg_numero, getpersondocumentcity(a.personid, 1) AS rg_cidade, getpersondocumentdateexpedition(a.personid, 1) AS rg_data, a.personid AS ref_filiacao, NULL::UNKNOWN AS ref_cobranca, NULL::UNKNOWN AS ref_assistmed, c.cityidbirth AS ref_naturalidade, c.countryidbirth AS ref_nacionalidade,
        CASE
            WHEN b.personid IS NOT NULL THEN b.personid
            ELSE c.responsablelegalid
        END AS ref_segurado,
        CASE
            WHEN b.personid IS NOT NULL THEN b.cnpj
            ELSE getpersondocument(a.personid, 2)
        END AS cod_cpf_cgc, getpersondocument(a.personid, 4) AS titulo_eleitor, NULL::UNKNOWN AS conta_laboratorio, NULL::UNKNOWN AS conta_provedor, NULL::UNKNOWN AS regc_livro, NULL::UNKNOWN AS regc_folha, NULL::UNKNOWN AS regc_local, NULL::UNKNOWN AS regc_nasc_casam, NULL::UNKNOWN AS ano_1g, NULL::UNKNOWN AS cidade_1g, NULL::UNKNOWN AS ref_curso_1g, NULL::UNKNOWN AS escola_1g, d.yearhs AS ano_2g, d.cityidhs AS cidade_2g, d.externalcourseidhs AS ref_curso_2g, d.institutionidhs AS escola_2g, NULL::UNKNOWN AS graduacao, d.passive AS cod_passivo, m.m_password AS senha, NULL::UNKNOWN AS fl_dbfolha, NULL::UNKNOWN AS ref_pessoa_folha, d.isinsured AS fl_segurado, a.name AS nome2, NULL::UNKNOWN AS fl_cartao, c.specialnecessityid AS deficiencia, NULL::UNKNOWN AS cidade, NULL::UNKNOWN AS nacionalidade, NULL::UNKNOWN AS in_sagu, e.externalid AS cod_externo, c.specialnecessitydescription AS deficiencia_desc, NULL::UNKNOWN AS dt_responsavel, getpersondocumentorgan(a.personid, 1) AS rg_orgao, c.carplate AS placa_carro, a.isallowpersonaldata AS fl_dados_pessoais, NULL::UNKNOWN AS seguro_meses, REPLACE(a.name::text, ' '::text, '0'::text) AS nome3,
        CASE
            WHEN c.datedeath IS NULL THEN 'f'::text
            ELSE 't'::text
        END AS fl_obito, c.ethnicoriginid AS raca
   FROM ONLY basperson a
   LEFT JOIN miolo_user m ON UPPER(m.login::text) = UPPER(a.miolousername::text)
   LEFT JOIN ONLY baslegalperson b USING (personid)
   LEFT JOIN ONLY basphysicalperson c USING (personid)
   LEFT JOIN ONLY basphysicalpersonstudent d USING (personid)
   LEFT JOIN ONLY basemployee e USING (personid);

CREATE VIEW cmn_pessoas AS
    SELECT pessoas.id, pessoas.identificacao, pessoas.titulo_academico, pessoas.nome, pessoas.rua, pessoas.complemento, pessoas.bairro, pessoas.cep, pessoas.ref_cidade, pessoas.fone_particular, pessoas.fone_profissional, pessoas.fone_celular, pessoas.fone_recado, pessoas.email, pessoas.email_alt, pessoas.estado_civil, pessoas.dt_cadastro, pessoas.tipo_pessoa, pessoas.obs, pessoas.dt_nascimento, pessoas.sexo, pessoas.credo, pessoas.nome_fantasia, pessoas.cod_inscricao_estadual, pessoas.rg_numero, pessoas.rg_cidade, pessoas.rg_data, pessoas.ref_filiacao, pessoas.ref_cobranca, pessoas.ref_assistmed, pessoas.ref_naturalidade, pessoas.ref_nacionalidade, pessoas.ref_segurado, pessoas.cod_cpf_cgc, pessoas.titulo_eleitor, pessoas.conta_laboratorio, pessoas.conta_provedor, pessoas.regc_livro, pessoas.regc_folha, pessoas.regc_local, pessoas.regc_nasc_casam, pessoas.ano_1g, pessoas.cidade_1g, pessoas.ref_curso_1g, pessoas.escola_1g, pessoas.ano_2g, pessoas.cidade_2g, pessoas.ref_curso_2g, pessoas.escola_2g, pessoas.graduacao, pessoas.cod_passivo, pessoas.senha, pessoas.fl_dbfolha, pessoas.ref_pessoa_folha, pessoas.fl_segurado, pessoas.nome2, pessoas.fl_cartao, pessoas.deficiencia, pessoas.cidade, pessoas.nacionalidade, pessoas.in_sagu, pessoas.cod_externo, pessoas.deficiencia_desc, pessoas.dt_responsavel, pessoas.rg_orgao, pessoas.placa_carro, pessoas.fl_dados_pessoais, pessoas.seguro_meses, pessoas.nome3, pessoas.fl_obito, pessoas.raca, NULL AS profissao, NULL AS escola, NULL AS local_trabalho, NULL AS endereco_trabalho, NULL AS filiacao FROM pessoas;
