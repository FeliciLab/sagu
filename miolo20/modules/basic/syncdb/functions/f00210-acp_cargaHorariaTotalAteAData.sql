CREATE OR REPLACE FUNCTION acp_cargahorariatotalateadata(p_inscricaoid integer, p_ofertaturmaid integer, p_data date)
  RETURNS real AS
$BODY$
/*********************************************************************************************
  NAME: acp_cargahorariatotalateadata
  PURPOSE: Obtém a carga horária total no curso de determinada matricula que deveria ser curdada até a data
  DESCRIPTION: A função percorre todas as disciplinas oferecidas para o curso da inscrição
  soma as cargas horárias das disciplinas oferecidas até determinada data

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       13/03/2014 Jonsa Diel        1. Função criada
*********************************************************************************************/
DECLARE
    v_cargahoraria NUMERIC;
BEGIN

    SELECT SUM( COALESCE(N.minutosfrequencia, 0) )/60 INTO v_cargahoraria
           FROM acpComponenteCurricular A
           LEFT JOIN acpComponenteCurricularDisciplina B ON (A.componentecurricularid = B.componentecurricularid)
           LEFT JOIN acpComponenteCurricularTrabalhoConclusao C ON (A.componentecurricularid = C.componentecurricularid)
           INNER JOIN acpComponenteCurricularMatriz D ON (D.componentecurricularid = A.componentecurricularid) 
           INNER JOIN acpMatrizCurricularGrupo E ON (E.matrizcurriculargrupoid = D.matrizcurriculargrupoid)
           INNER JOIN acpMatrizCurricular F ON (F.matrizcurricularid = E.matrizcurricularid)
           INNER JOIN acpCurso G ON (G.cursoid = F.cursoid)
           INNER JOIN acpOcorrenciaCurso H ON (H.cursoid = G.cursoid)
           INNER JOIN acpOfertaCurso I ON (I.ocorrenciacursoid = H.ocorrenciacursoid)
           INNER JOIN acpOfertaTurma J ON (J.ofertacursoid = I.ofertacursoid)
           INNER JOIN acpInscricaoTurmaGrupo K ON (K.ofertaturmaid = J.ofertaturmaid)
           INNER JOIN acpOfertaComponenteCurricular L ON (L.componentecurricularmatrizid = D.componentecurricularmatrizid AND L.ofertaturmaid = J.ofertaturmaid)
           INNER JOIN acpOcorrenciaHorarioOferta M ON (M.ofertacomponentecurricularid = L.ofertacomponentecurricularid)   
           INNER JOIN acpHorario N ON N.horarioid = M.horarioid

           WHERE K.inscricaoid = p_inscricaoid
                   AND M.dataaula <= COALESCE(p_data, NOW()::date)
                   AND J.ofertaturmaid = p_ofertaturmaid;

           RETURN v_cargahoraria;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION acp_cargahorariatotalateadata(integer, integer, date)
  OWNER TO postgres;
  --
