CREATE OR REPLACE FUNCTION acp_obterModeloDaOfertaDeComponenteCurricular(p_ofertacomponentecurricularid integer)
RETURNS SETOF acpmodelodeavaliacao AS
$BODY$
/******************************************************************************
  NAME: acp_obtermodelodaofertadecomponentecurricular
  DESCRIPTION: Obtém o código do modelo de avaliação para a oferta do componente curricular

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------
  1.0       17/07/14   Jonas Diel         Função criada.
  1.1       29/12/14   Luis F. Wermann    Correcao de SELECT que buscava o modelodeavaliacaoid
******************************************************************************/
DECLARE
    v_perfilcursoid integer;
    v_perfilcurso acpperfilcurso;
    v_modelodeavaliacaoid integer;
    v_modelodeavaliacaogeralid integer;
    v_modelodeavaliacao acpmodelodeavaliacao;
BEGIN

SELECT INTO v_perfilcursoid perfilcurso.perfilcursoid
FROM   acpperfilcurso perfilcurso
       LEFT JOIN acpcurso curso on curso.perfilcursoid=perfilcurso.perfilcursoid
       LEFT JOIN acpocorrenciacurso ocorrenciacurso on ocorrenciacurso.cursoid=curso.cursoid
       LEFT JOIN acpofertacurso ofertacurso on ofertacurso.ocorrenciacursoid=ocorrenciacurso.ocorrenciacursoid
       LEFT JOIN acpofertaturma ofertaturma on ofertaturma.ofertacursoid=ofertacurso.ofertacursoid
       LEFT JOIN acpofertacomponentecurricular ofertacomponentecurricular on ofertacomponentecurricular.ofertaturmaid=ofertaturma.ofertaturmaid
       WHERE ofertacomponentecurricular.ofertacomponentecurricularid = p_ofertacomponentecurricularid;

--Perfil de curso
SELECT INTO v_perfilcurso * FROM acpperfilcurso WHERE perfilcursoid = v_perfilcursoid;

--Modelo de avaliação específico do tipo da disciplina
SELECT INTO v_modelodeavaliacaoid  A.modelodeavaliacaoid
       FROM acpPerfilCursoComponenteCurricular A
      WHERE A.perfilCursoId = v_perfilcursoid
        AND A.tipoComponenteCurricularId = ( SELECT T.tipoComponenteCurricularId
                                                 FROM acpTipoComponenteCurricular T
                                           INNER JOIN acpComponenteCurricular C
                                                USING (tipoComponenteCurricularId)
                                           INNER JOIN acpComponenteCurricularMatriz M
                                                USING (componenteCurricularId)
                                           INNER JOIN acpOfertaComponenteCurricular O
                                                USING (componenteCurricularMatrizId)
                                                WHERE O.ofertaComponenteCurricularId =  p_ofertacomponentecurricularid);

IF v_modelodeavaliacaoid IS NOT NULL
THEN 
    --Modelo específico do tipo da disciplina
    SELECT INTO v_modelodeavaliacao * FROM acpmodelodeavaliacao WHERE modelodeavaliacaoid = v_modelodeavaliacaoid;
ELSE
    --Modelo de avaliação geral
    SELECT INTO v_modelodeavaliacao * FROM acpmodelodeavaliacao WHERE modelodeavaliacaoid = v_perfilcurso.modelodeavaliacaogeral;
END IF;

RETURN NEXT v_modelodeavaliacao;

END;
$BODY$
  LANGUAGE plpgsql ;
