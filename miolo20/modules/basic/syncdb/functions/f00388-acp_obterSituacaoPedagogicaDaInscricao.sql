CREATE OR REPLACE FUNCTION acp_obterSituacaoPedagogicaDaInscricao(p_inscricaoId INT)
RETURNS TEXT AS
$BODY$
BEGIN
   
   /*
    * PENDENTE: Quando pelo menos uma matrícula estiver com a situação 'I' (inscrito)
    * CANCELADO: Quando a inscrição do aluno estiver com a situação 'C' (cancelada)
    * APROVADO: Quando todas as matrículas do aluno estiverem com situação 'A' (aprovada)
    * REPROVADO: Quando pelo menos uma matrícula do aluno estiver com a situação 'R' ou 'F' (reprovado/reprovado por faltas)
    * DEMAIS SITUAÇÕES: Caso não se encaixar em nenhuma acima. Olha para a situação da inscrição.
    */     

    RETURN (
        SELECT COALESCE((SELECT DISTINCT (CASE WHEN M.situacao = 'I' 
					       THEN 
					            'Pendente' 
					       ELSE 
						    (CASE WHEN I.situacao = 'C' 
						          THEN 
							       'Cancelado' 
							  ELSE 
							       (CASE WHEN (SELECT COUNT(*) FROM acpMatricula WHERE inscricaoTurmaGrupoId = ITG.inscricaoTurmaGrupoId AND situacao IN ('F', 'R')) > 0
							             THEN
									 'Reprovado'
								     WHEN (SELECT COUNT(*) FROM acpMatricula WHERE inscricaoTurmaGrupoId = ITG.inscricaoTurmaGrupoId) = (SELECT COUNT(*) FROM acpMatricula WHERE inscricaoTurmaGrupoId = ITG.inscricaoTurmaGrupoId AND situacao IN ('A'))
								     THEN
								         'Aprovado'
								     ELSE
									 'Matriculado'
							        END)
						     END) 
					  END)
				    FROM acpMatricula M
		              INNER JOIN AcpInscricaoTurmaGrupo ITG
				      ON ITG.inscricaoTurmaGrupoId = M.inscricaoTurmaGrupoId
				   WHERE ITG.inscricaoId = I.inscricaoId
                                GROUP BY M.situacao, I.situacao, ITG.inscricaoTurmaGrupoId, I.inscricaoId), 
			 (CASE I.situacao
			       WHEN 'I'
			       THEN
				    'Inscrito'
			       WHEN 'E'
			       THEN
				    'Esperando'
			       WHEN 'T'
			       THEN
				    'Trancado'
			       WHEN 'C'
			       THEN
				    'Cancelado'
			       WHEN 'P'
			       THEN
				    'Pendente'
			  END))::TEXT
	  FROM acpInscricao I
	 WHERE inscricaoId = p_inscricaoId
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;