CREATE OR REPLACE FUNCTION acp_media_geral_da_inscricao_nos_componentes_curriculares_do_grupo_da_matriz(p_inscricaoId INT, p_matrizCurricularGrupoId INT)
RETURNS NUMERIC AS
$BODY$
BEGIN
    RETURN (
        SELECT X.modulo,
	       X.codigo_inscricao,
	       SUM(X.nota_final) AS soma_de_todas_notas_no_modulo,
	       SUM(X.quantidade) AS quantidade_componentes_curriculares_modulo,
	       ROUND((SUM(X.nota_final) / SUM(X.quantidade)), getParameter('BASIC', 'GRADE_ROUND_VALUE')::int) AS media_geral_componentes_curriculares_modulo
	  FROM (SELECT codigo_matriz_curricular_grupo AS modulo,
		       codigo_inscricao,
		       nota_final,
		       (CASE WHEN nota_final IS NOT NULL 
			     THEN 
				  COUNT(codigo_matriz_curricular_grupo)
			     ELSE
				  0
			END) AS quantidade
		  FROM cr_acp_historico_matriz_curricular 
	      GROUP BY codigo_matriz_curricular_grupo,
		       codigo_inscricao,
		       nota_final) X
      GROUP BY X.modulo,
	       X.codigo_inscricao
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;