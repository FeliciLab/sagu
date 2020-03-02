CREATE OR REPLACE VIEW acdViewMoodleSubscription AS
    SELECT DISTINCT M.login,
                    --Verifica o módulo do registro, e obtém os dados referentes.
                (CASE M.idmodule
                  WHEN 'academic'
                  THEN
                       (SELECT U.name || ' - REF' || G.groupid::VARCHAR
                          FROM acdGroup G
                INNER JOIN acdCurriculum R
                        ON (R.curriculumId = G.curriculumId)
                INNER JOIN acdCurricularComponent U
                    ON (U.curricularComponentId,
                        U.curricularComponentVersion) = (R.curricularComponentId,
                                         R.curricularComponentVersion)
                     WHERE G.groupId = M.groupId)
                  WHEN 'pedagogico'
                  THEN
                       (SELECT CC.nome || ' - REF' || OCC.ofertaComponenteCurricularId::VARCHAR
                          FROM acpOfertaComponenteCurricular OCC
                    INNER JOIN acpComponenteCurricularMatriz CCM
                         USING (componenteCurricularMatrizId)
                    INNER JOIN acpComponenteCurricular CC
                         USING (componenteCurricularId)
                         WHERE OCC.ofertaComponenteCurricularId = M.groupId)

                  WHEN 'resmedica'
                  THEN
                      (SELECT B.descricao || ' - REF' || A.ofertaDeUnidadeTematicaId
                         FROM med.ofertaDeUnidadeTematica A
                   INNER JOIN med.unidadeTematica B
                           ON (A.unidadeTematicaId = B.unidadeTematicaId)
                        WHERE A.ofertaDeUnidadeTematicaId = M.groupId)
                 WHEN 'residency'
                  THEN
                      (SELECT B.descricao || ' - REF' || A.ofertaDeUnidadeTematicaId
                         FROM res.ofertaDeUnidadeTematica A
                   INNER JOIN res.unidadeTematica B
                           ON (A.unidadeTematicaId = B.unidadeTematicaId)
                        WHERE A.ofertaDeUnidadeTematicaId = M.groupId)
              END) AS course,
            (CASE WHEN M.isteacher
                  THEN
                       'editingteacher'
                  ELSE
                       'student'
             END) AS perfil
           FROM acdMoodleSubscription M
              WHERE (M.processed IS FALSE OR M.processed IS NULL)
                AND M.login IS NOT NULL;
