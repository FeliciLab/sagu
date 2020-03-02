CREATE OR REPLACE VIEW cr_acd_disciplina AS (
    SELECT CC.curricularComponentId AS codigo_disciplina,
           CC.curricularComponentVersion AS versao_disciplina,
           CC.centerId AS codigo_centro,
           C.name AS centro,
           CC.name AS disciplina,
           CC.shortname AS abreviatura,
           CC.summary AS ementa,
           CC.academiccredits AS creditos_academicos,
           CC.lessoncredits AS creditos_aula,
           CC.lessonnumberhours AS horas_aula,
           CC.begindate AS data_inicial,
           CC.enddate AS data_final,
           CC.educationareaid AS codigo_area_de_ensino,
           EA.description AS area_de_ensino
      FROM acdCurricularComponent CC
 LEFT JOIN acdCenter C
     USING (centerId)
 LEFT JOIN acdEducationArea EA
     USING (educationareaid)
);
