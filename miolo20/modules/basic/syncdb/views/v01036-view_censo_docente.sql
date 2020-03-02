CREATE OR REPLACE VIEW view_censo_docente AS
select a.personid::text as id_professor,
       a.name::text as nome,
       REPLACE(REPLACE(b.content, '.', ''), '-', '') AS cpf,
       to_char(a.datebirth, 'ddmmyyyy')::text as data_nascimento,
       case when upper(a.sex) = 'M' then '0' else '1' end as sexo,
       verificaOrigemEtnicaPessoaCenso(a.ethnicoriginid)::text AS origemEtnica,
       UNACCENT(CASE WHEN a.mothername IS NOT NULL THEN a.mothername
                     ELSE COALESCE(( SELECT name
                              FROM ONLY basPerson
                             WHERE personId = c.relativepersonid ), '')
                END)::text AS nome_da_mae,
       (CASE WHEN ( a.countryidbirth <> 'BRA' AND a.countryidbirth IS NOT NULL ) THEN
            (CASE WHEN b.content IS NOT NULL
            THEN
                '3'::TEXT
            ELSE
                ''::TEXT
            END)
       ELSE '1'::TEXT
       END) AS nacionalidade,
       CASE WHEN a.countryidbirth IS NULL THEN 'BRA' ELSE a.countryidbirth::text END AS pais_de_origem,
       CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN '1' ELSE '0' END AS tem_deficiencia,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN '0'::text ELSE ''::text END ) AS deficiencia_cegueira,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN ( CASE WHEN ( a.specialnecessityid = 5 ) THEN '1' ELSE '0' END ) ELSE ''::text END ) AS deficiencia_visao,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN '0'::text ELSE ''::text END ) AS deficiencia_surdez,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN ( CASE WHEN ( a.specialnecessityid = 1 ) THEN '1' ELSE '0' END ) ELSE ''::text END ) AS deficiencia_auditiva,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN ( CASE WHEN ( a.specialnecessityid = 2 ) THEN '1' ELSE '0' END ) ELSE ''::text END ) AS deficiencia_fisica,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN '0' ELSE ''::text END ) AS deficiencia_surdocegueira,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN ( CASE WHEN ( a.specialnecessityid = 3 ) THEN '1' ELSE '0' END ) ELSE ''::text END ) AS deficiencia_multipla,
       ( CASE WHEN ( a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 ) THEN ( CASE WHEN ( a.specialnecessityid = 4 ) THEN '1' ELSE '0' END ) ELSE ''::text END ) AS deficiencia_intelectual,
       (CASE WHEN a.escolaridade IS NOT NULL THEN
            (CASE WHEN (a.escolaridade = 2 AND a.posgraduacao IS NULL) THEN
            ''::TEXT
            ELSE
            a.escolaridade::text
            END)
       ELSE '1' END) AS escolaridade,
       CASE WHEN a.escolaridade = 2 THEN (a.posgraduacao - 1)::text ELSE ''::text END AS pos_graduacao,
       CASE WHEN a.situacao IS NOT NULL THEN a.situacao::text ELSE '' END AS situacao_ies,

       (  case when a.situacao = 1
          then
               (case when exists   (select distinct
                                    aa.professorid as id_professor,
                                    dd.courseid as curso
                               from acdscheduleprofessor aa
                              inner join acdschedule bb using (scheduleid)
                              inner join acdgroup cc using (groupid)
                              inner join acdlearningperiod dd using (learningperiodid)
                              where dd.periodid = '2014/2'
                                and aa.professorid = a.personid)
                        then '1' else '0' end )
          else
            ''
          end)::text AS esteve_em_exercicio,
       CASE WHEN a.situacao = 1 THEN COALESCE(a.regimeTrabalho::text, '') ELSE '' END AS regime_trabalho,
       CASE WHEN a.situacao = 1 THEN (CASE WHEN a.substituto IS TRUE THEN '1' ELSE '0' END) ELSE '' END AS substituto,
       CASE WHEN a.situacao = 1 THEN (CASE WHEN a.visitante IS TRUE THEN '1' ELSE '0' END) ELSE '' END AS visitante,
       CASE WHEN a.situacao = 1 AND a.visitante IS TRUE THEN COALESCE(a.tipoVinculo::text, '') ELSE '' END AS tipo_vinculo_ies,

       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10001) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_sequencial_formacao_especifica,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10002) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_graduacao_presencial,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10003) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_graduacao_distancia,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10004) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_stricto_sensu_presencial,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10005) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_stricto_sensu_distancia,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10006) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_pesquisa,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10007) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_extensao,
       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10008) THEN '1' ELSE '0' END ) ELSE '' END AS atuacao_gestao_planejamento_avaliacao,

       CASE WHEN a.situacao = 1 THEN ( CASE WHEN EXISTS(SELECT 1 FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid AND AP.tipoatuacaoprofessorid = 10006) THEN '1' ELSE '0' END ) ELSE '' END AS bolsa_pesquisa,

       case when exists (select distinct
                                aa.professorid as id_professor,
                                dd.courseid as curso
                           from acdscheduleprofessor aa
                          inner join acdschedule bb using (scheduleid)
                          inner join acdgroup cc using (groupid)
                          inner join acdlearningperiod dd using (learningperiodid)
                          where dd.periodid = '2014/2'
                            and aa.professorid = a.personid) then '1' else '0' end as esta_ativo,

       ARRAY_TO_STRING(ARRAY[
           (CASE WHEN a.situacao = 1 AND (SELECT COUNT(*) FROM basAtuacaoProfessor AP WHERE AP.personid = a.personid) = 0 THEN 'A situacao do docente na IES esta como `Em exercicio`, porem, nao foi definido nenhuma atuacao (cadastro no passo 5 do Professor)' ELSE NULL END),
           (CASE WHEN 1=2 THEN 'Erro 2' ELSE NULL END),
           (CASE WHEN 1=1 THEN 'Erro 3' ELSE NULL END)
       ], ', ') AS erros

        from basphysicalpersonprofessor a
        left join basdocument b on (b.personid = a.personid and b.documenttypeid = getparameter('BASIC', 'DEFAULT_DOCUMENT_TYPE_ID_CPF')::integer)
        left join basPhysicalPersonKinship c on (c.personId = a.personId and c.kinshipId = getparameter('BASIC', 'MOTHER_KINSHIP_ID')::integer)
	WHERE a.name <> '' 
	AND b.content <> '' 
	AND to_char(a.datebirth, 'ddmmyyyy')::text <> ''
	AND ((a.escolaridade IS NOT NULL AND (a.escolaridade <> 2 OR a.posgraduacao IS NOT NULL)) OR (a.escolaridade IS NULL))
	AND a.situacao IS NOT NULL;