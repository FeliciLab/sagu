CREATE OR REPLACE VIEW view_censo_aluno AS 
 SELECT DISTINCT a.personid AS id_aluno, upper(unaccent(a.name)) AS nome, 
        replace(replace(b.content, '.'::text, ''::text), '-'::text, ''::text) AS cpf, 
        to_char(a.datebirth::timestamp with time zone, 'ddmmyyyy'::text) AS data_nascimento, 
        CASE
            WHEN upper(a.sex::text) = 'M'::text THEN '0'::text
            ELSE '1'::text
        END AS sexo, 
        '0'::text AS origemetnica, 	
        COALESCE(unaccent(
        CASE
            WHEN a.mothername IS NOT NULL THEN a.mothername
            ELSE COALESCE(( SELECT basperson.name
               FROM ONLY basperson
              WHERE basperson.personid = c.relativepersonid), ''::character varying)
        END::text), ''::text) AS nome_da_mae, 
        CASE
            WHEN a.countryidbirth::text <> 'BRA'::text AND a.countryidbirth IS NOT NULL THEN '3'::text
            ELSE '1'::text
        END AS nacionalidade, 
        CASE
            WHEN a.countryidbirth IS NULL THEN 'BRA'::character varying::text
            ELSE a.countryidbirth::text
        END AS pais_de_origem, 

        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '1'::text
            ELSE '0'::text
        END AS tem_deficiencia, 

	CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 5 THEN '1'::text 
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_cegueira, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 5 THEN '1'::text 
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_visao, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 12 THEN '1'::text 
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_surdez,
         
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 1 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text            
        END AS deficiencia_auditiva, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 2 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_fisica, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 13 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_surdocegueira, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 3 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_multipla, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN 
            CASE
                WHEN a.specialnecessityid = 4 THEN '1'::text
                ELSE '0'::text
            END
            ELSE ''::text
        END AS deficiencia_intelectual, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '0'::text
            ELSE ''::text
        END AS deficiencia_autismo, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '0'::text
            ELSE ''::text
        END AS deficiencia_sindrome_asperger, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '0'::text
            ELSE ''::text
        END AS deficiencia_sindrome_rett, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '0'::text
            ELSE ''::text
        END AS deficiencia_transtorno_desintegrativo, 
        
        CASE
            WHEN a.specialnecessityid IS NOT NULL AND a.specialnecessityid <> 0 THEN '0'::text
            ELSE ''::text
        END AS deficiencia_superdotado
   FROM ONLY basphysicalpersonstudent a
   JOIN view_censo_aluno_cursos d ON d.id_aluno = a.personid
   LEFT JOIN basdocument b ON b.personid = a.personid AND b.documenttypeid = getparameter('BASIC'::character varying, 'DEFAULT_DOCUMENT_TYPE_ID_CPF'::character varying)::integer
   LEFT JOIN basphysicalpersonkinship c ON c.personid = a.personid AND c.kinshipid = getparameter('BASIC'::character varying, 'MOTHER_KINSHIP_ID'::character varying)::integer
  WHERE a.countryidbirth::text = 'BRA'::text AND replace(replace(b.content, '.'::text, ''::text), '-'::text, ''::text) <> ''::text AND to_char(a.datebirth::timestamp with time zone, 'ddmmyyyy'::text) <> ''::text AND a.name::text <> ''::text
  ORDER BY a.personid;
