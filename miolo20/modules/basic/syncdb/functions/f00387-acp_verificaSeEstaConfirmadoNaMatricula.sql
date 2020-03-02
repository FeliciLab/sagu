CREATE OR REPLACE FUNCTION acp_verificaSeEstaConfirmadoNaMatricula(p_inscricaoId INT)
RETURNS BOOLEAN AS
$BODY$
BEGIN
    RETURN (
        SELECT (CASE RMPC.formaDeConfirmacaoMatricula
		     WHEN 'N' --Nenhum
		     THEN
		          acp_verificaSeEstaConfirmadoNaInscricao(p_inscricaoId)
		     WHEN 'X' --Pagamento primeira parcela
		     THEN
		          acp_verificaSePrimeiraParcelaFoiPaga(I.inscricaoId)
		     WHEN 'M' --Manual
		     THEN
		          (COALESCE((SELECT COUNT(*) > 1
				       FROM acpMatricula M
                                 INNER JOIN acpInscricaoTurmaGrupo ITG
                                         ON ITG.inscricaoTurmaGrupoId = M.inscricaoTurmaGrupoId
				      WHERE ITG.inscricaoId = I.inscricaoId
                                        AND ((M.situacao <> 'I') 
				        AND (M.situacao <> 'C'))), FALSE))
		END)
	  FROM acpInscricao I
    INNER JOIN acpOfertaCurso B
            ON (I.ofertacursoid = B.ofertacursoid)
    INNER JOIN acpOcorrenciaCurso O
            ON (O.ocorrenciacursoid = B.ocorrenciacursoid)
    INNER JOIN acpCurso A
            ON (A.cursoid = O.cursoid)
    INNER JOIN acpPerfilCurso PC
	    ON PC.perfilCursoId = A.perfilCursoId
     LEFT JOIN acpRegrasMatriculaPerfilCurso RMPC
	    ON RMPC.perfilCursoId = PC.perfilCursoId
	 WHERE I.inscricaoId = p_inscricaoId
    );
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;
