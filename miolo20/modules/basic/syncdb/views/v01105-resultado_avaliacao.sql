CREATE OR REPLACE VIEW resultado_avaliacao AS (
 SELECT A.id_respostas, E.ref_avaliacao, G.nome as nome_avaliacao, E.id_formulario, E.nome as nome_formulario, E.ref_perfil, H.descricao as nome_perfil, D.id_bloco, D.nome as nome_bloco, F.id_granularidade, F.descricao as nome_granularidade, 
F.tipo_granularidade, C.id_questoes, C.descricao as nome_questoes, C.tipo, A.valor as resposta, I.descricao as nome_resposta, I.legenda as legenda_resposta, J.*
      FROM ava_respostas A
INNER JOIN ava_bloco_questoes B
        ON (A.ref_bloco_questoes = B.id_bloco_questoes)
INNER JOIN ava_questoes C
        ON (B.ref_questao = C.id_questoes)
INNER JOIN ava_bloco D
        ON (B.ref_bloco = D.id_bloco)
INNER JOIN ava_formulario E
        ON (D.ref_formulario = E.id_formulario)
INNER JOIN ava_granularidade F
        ON (F.id_granularidade = D.ref_granularidade)
INNER JOIN ava_avaliacao G
        ON (E.ref_avaliacao = G.id_avaliacao)
INNER JOIN ava_perfil H
        ON (E.ref_perfil = H.id_perfil)
LEFT JOIN ava_questoes_opcoes I
        ON (C.id_questoes = I.ref_questoes AND A.valor = I.codigo)
LEFT JOIN (
SELECT *
  FROM crosstab('SELECT ref_resposta, 
                        chave,
                        valor
                   FROM ava_atributos', 
               'SELECT unnest(ARRAY[''course_name'', ''ref_curso'', ''ref_unit'', ''ref_course'', ''person_maritalstatus_description'', ''groupid'', ''curricularcomponentversion'', ''ref_sector'', ''formation_level'', ''ref_group'', ''ref_curricular_component'', ''person_cityid'', ''person_employee_description'', ''ref_curriculum'', ''course'', ''turn'', ''course_centerid'', ''person_sex'', ''professor_name'', ''person_employee_typeid'', ''person_stateid'', ''person_state_description'', ''person_maritalstatusid'', ''totalenrolled'', ''person_weeklyhours'', ''person_city_description'', ''sector'', ''ref_turn'', ''curricular_component'', ''professorid'', ''ref_formation_level'', ''ref_curriculum_version'', ''course_center_description'', ''person_datebirth'', ''course_version'', ''person_specialnecessityid'', ''ref_unidade'', ''person_regimetrabalho'', ''unit'', ''ref_curricular_component_version'', ''person_specialnecessity_description'', ''ref_turno'', ''courseverion''])')
AS ct (ref_resposta int, course_name text, 
      ref_curso text, ref_unit text, ref_course text, 
      person_maritalstatus_description text, groupid text, 
      curricularcomponentversion text, ref_sector text, 
      formation_level text, ref_group text, ref_curricular_component text, 
      person_cityid text, person_employee_description text, ref_curriculum text, 
      course text, turn text, course_centerid text, person_sex text, professor_name text, 
      person_employee_typeid text, person_stateid text, person_state_description text, 
      person_maritalstatusid text, totalenrolled text, person_weeklyhours text, 
      person_city_description text, sector text, ref_turn text, 
      curricular_component text, professorid text, ref_formation_level text, 
      ref_curriculum_version text, course_center_description text, 
      person_datebirth text, course_version text, person_specialnecessityid text, 
      ref_unidade text, person_regimetrabalho text, unit text, ref_curricular_component_version text, 
      person_specialnecessity_description text, ref_turno text, courseverion text)) J

ON (A.id_respostas = J.ref_resposta));
