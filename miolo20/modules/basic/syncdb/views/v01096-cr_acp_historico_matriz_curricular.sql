CREATE OR REPLACE VIEW cr_acp_historico_matriz_curricular AS (
        SELECT I.personId AS codigo_pessoa,
	       I.inscricaoId AS codigo_inscricao,
	       OCU.ofertaCursoId AS codigo_oferta_curso,
	       OCU.descricao AS oferta_curso,
	       OC.ocorrenciaCursoId AS codigo_ocorrencia_curso,
	       CC.componenteCurricularId AS codigo_componente_curricular,
	       CC.codigo AS codigo_abreviado_componente_curricular,
	       CC.nome AS componente_curricular,
	       M.matriculaId AS codigo_matricula,
	       ROUND(M.notaFinal, getParameter('BASIC', 'GRADE_ROUND_VALUE')::int) AS nota_final,
	       M.conceitoFinal AS conceito_final,
	       M.frequencia AS frequencia,
	       OC.cursoId AS codigo_curso,
	       CCM.matrizCurricularGrupoId AS codigo_matriz_curricular_grupo,
	       MCG.descricao AS matriz_curricular_grupo,
	       CCD.cargahoraria AS carga_horaria_componente_curricular,
	       ROUND(ITG.notaFinal, getParameter('BASIC', 'GRADE_ROUND_VALUE')::int) AS nota_final_turma_grupo,
	       ITG.conceitofinal AS conceito_final_turma_grupo,
	       (dense_rank() OVER (PARTITION BY I.inscricaoId ORDER BY CCM.matrizCurricularGrupoId)) AS ordem_modulo,
               ROUND((((SELECT cargahorariafrequente
                          FROM acpCursoInscricao
                         WHERE personId = I.personId
                           AND cursoId = OC.cursoId) * 100) / acp_obtercargahorariatotaldocurso(OC.cursoId)), getParameter('BASIC', 'REAL_ROUND_VALUE')::INT) AS frequencia_real_no_curso
	  FROM acpInscricao I
    INNER JOIN acpOfertaCurso OCU
	    ON OCU.ofertaCursoId = I.ofertaCursoId
    INNER JOIN acpOcorrenciaCurso OC
	    ON OC.ocorrenciaCursoId = OCU.ocorrenciaCursoId
    INNER JOIN acpMatrizCurricular MC
	    ON MC.cursoId = OC.cursoId
    INNER JOIN acpMatrizCurricularGrupo MCG
	    ON MCG.matrizCurricularId = MC.matrizCurricularId
    INNER JOIN acpComponenteCurricularMatriz CCM
	    ON CCM.matrizCurricularGrupoId = MCG.matrizCurricularGrupoId
    INNER JOIN acpComponenteCurricular CC
	    ON CC.componenteCurricularId = CCM.componenteCurricularId
    INNER JOIN acpComponenteCurricularDisciplina CCD
	    ON CCD.componenteCurricularId = CC.componenteCurricularId
     LEFT JOIN acpInscricaoTurmaGrupo ITG
	    ON ITG.inscricaoId = I.inscricaoId
	   AND ITG.matrizCurricularGrupoId = MCG.matrizCurricularGrupoId
     LEFT JOIN (SELECT M.matriculaId,
		       ITG.inscricaoId,
		       CC.componenteCurricularId,
		       M.notaFinal,
		       M.frequencia,
		       M.conceitoFinal,
		       M.situacao
		  FROM acpMatricula M
	    INNER JOIN acpInscricaoTurmaGrupo ITG
		    ON ITG.inscricaoTurmaGrupoId = M.inscricaoTurmaGrupoId
	    INNER JOIN acpOfertaComponenteCurricular OCC
		    ON OCC.ofertaComponenteCurricularId = M.ofertaComponenteCurricularId
	    INNER JOIN acpComponenteCurricularMatriz CCM
		    ON CCM.componenteCurricularMatrizId = OCC.componenteCurricularMatrizId
	    INNER JOIN acpComponenteCurricular CC
		    ON CC.componenteCurricularId = CCM.componenteCurricularId) M
	    ON M.inscricaoId = I.inscricaoId
	   AND M.componenteCurricularId = CC.componenteCurricularId
	 ORDER BY CC.componenteCurricularId
);