CREATE OR REPLACE VIEW cr_acp_tcc AS(
         SELECT T.tccId AS codigo_tcc,
                C.cursoId AS codigo_curso,
                C.codigo AS codigo_abreviado_curso,
                C.nome AS nome_curso,
                GA.nome AS nivel_curso,
                OCU.ocorrenciaCursoId AS codigo_ocorrencia_curso,
                OCU.turnId AS codigo_turno,
                getTurnDescription(OCU.turnId) AS turno,
                OCU.unitId AS codigo_unidade,
                getUnitDescription(OCU.unitId) AS unidade,
                OC.ofertaCursoId AS codigo_oferta_curso,
                OC.descricao AS descricao_oferta_curso,
                OT.ofertaTurmaId AS codigo_oferta_turma,
                OT.descricao AS descricao_oferta_turma,
                CC.componenteCurricularId AS codigo_componente_curricular,
                CC.codigo AS codigo_abreviado_componente_curricular,
                CC.nome AS componente_curricular,
                I.inscricaoId AS codigo_inscricao,
                PP.personId AS codigo_aluno,
                PP.name AS nome_aluno,
                T.ofertaComponenteCurricularId AS codigo_oferta_componente_curricular,
                T.matriculaId AS codigo_matricula,
                T.tema AS tema_tcc,
                TO_CHAR(T.dataEntregaTrabalho, getParameter('BASIC', 'MASK_DATE')) AS data_entrega_trabalho,
                TO_CHAR(T.dataBanca, getParameter('BASIC', 'MASK_DATE')) AS data_banca,
                TO_CHAR(T.dataDivulgacaoResultado, getParameter('BASIC', 'MASK_DATE')) AS data_divulgacao_resultado,
                TOR.orientadorId AS codigo_orientador,
                TOR.personId AS codigo_pessoa_orientador,
                getPersonName(TOR.personId) AS orientador,
                TO_CHAR(OT.datainicialoferta, getParameter('BASIC', 'MASK_DATE')) AS data_inicial_tcc,
                dataPorExtenso(OT.datainicialoferta) AS data_extenco_inicial_tcc,
                TO_CHAR(OT.datafinaloferta, getParameter('BASIC', 'MASK_DATE')) AS data_final_tcc,
                dataPorExtenso(OT.datafinaloferta) AS data_extenco_final_tcc,
                (SELECT documento
                   FROM acp_obterAtoRegulatorioVigente(OCU.ocorrenciaCursoId)) AS ato_regulatorio_vigente,
                TO_CHAR(NOW()::DATE, getParameter('BASIC', 'MASK_DATE')) AS data_hoje,
                dataPorExtenso(NOW()::DATE) AS data_hoje_por_extenco,
                COO.coordenadorCursoId AS codigo_coordenador_curso,
                COO.personId AS codigo_pessoa_coordenador_curso,
                getPersonName(COO.personId) AS coordenador_curso,
                TO_CHAR(OCC.datainicio, getParameter('BASIC', 'MASK_DATE')) AS data_inicio_oferta_componente_curricular,
		TO_CHAR(OCC.datafechamento, getParameter('BASIC', 'MASK_DATE')) AS data_fechamento_oferta_componente_curricular
           FROM acpTcc T
     INNER JOIN acpOfertaComponenteCurricular OCC
             ON OCC.ofertaComponenteCurricularId = T.ofertaComponenteCurricularId
     INNER JOIN acpComponenteCurricularMatriz CCM
             ON CCM.componenteCurricularMatrizId = OCC.componenteCurricularMatrizId
     INNER JOIN acpComponenteCurricular CC
             ON CC.componenteCurricularId = CCM.componenteCurricularId
     INNER JOIN acpOfertaTurma OT
             ON OT.ofertaTurmaId = OCC.ofertaTurmaId
     INNER JOIN acpOfertaCurso OC
             ON OC.ofertaCursoId = OT.ofertaCursoId
     INNER JOIN acpOcorrenciaCurso OCU
             ON OCU.ocorrenciaCursoId = OC.ocorrenciaCursoId
     INNER JOIN acpCurso C
             ON C.cursoid = OCU.cursoId
     INNER JOIN acpMatricula M
             ON M.matriculaId = T.matriculaId
     INNER JOIN acpInscricaoTurmaGrupo ITG
             ON ITG.inscricaoTurmaGrupoId = M.inscricaoTurmaGrupoId
     INNER JOIN acpInscricao I
             ON I.inscricaoId = ITG.inscricaoId
INNER JOIN ONLY basPhysicalPerson PP
             ON PP.personId = M.personId
     INNER JOIN acpCoordenadores COO
             ON COO.ocorrenciaCursoId = OCU.ocorrenciaCursoid
      LEFT JOIN acpTccOrientador TOR
             ON TOR.tccId = T.tccId
      LEFT JOIN acpGrauAcademico GA
             ON GA.grauAcademicoId = C.grauAcademicoId
);
