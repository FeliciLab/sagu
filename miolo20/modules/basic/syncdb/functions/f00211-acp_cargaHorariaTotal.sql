CREATE OR REPLACE FUNCTION acp_cargaHorariaTotal(p_inscricaoid integer, p_ofertaturmaid integer)
RETURNS real AS
$BODY$
/*********************************************************************************************
NAME: acp_cargahorariatotal
PURPOSE: Obtém a carga horária total do curso de determinada inscrição NA TURMA
DESCRIPTION: A função percorre todas as disciplinas oferecidas para o curso da inscrição
soma as cargas horárias dos diferentes tipos de disciplinas

REVISIONS:
Ver       Date       Author            Description
--------- ---------- ----------------- ------------------------------------
1.0       12/03/2014 Jonsa Diel        1. Função criada
*********************************************************************************************/
DECLARE
v_cargahoraria NUMERIC;
v_permiteinscricaoporgrupo acpperfilcurso.permiteinscricaoporgrupo%TYPE;
BEGIN
--Verifica se permite insrições por grupo
SELECT perfilcurso.permiteinscricaoporgrupo INTO v_permiteinscricaoporgrupo 
        FROM acpofertaturma ofertaturma
    INNER JOIN acpofertacurso ofertacurso
            ON ofertacurso.ofertacursoid = ofertaturma.ofertacursoid
    INNER JOIN acpocorrenciacurso ocorrenciacurso
            ON ocorrenciacurso.ocorrenciacursoid = ofertacurso.ocorrenciacursoid
    INNER JOIN acpcurso curso
            ON curso.cursoid = ocorrenciacurso.cursoid
    INNER JOIN acpperfilcurso perfilcurso
            ON perfilcurso.perfilcursoid = curso.perfilcursoid
        WHERE ofertaturma.ofertaturmaid = p_ofertaturmaid;

IF v_permiteinscricaoporgrupo IS TRUE THEN
--Carga horária total de uma inscrição
      SELECT SUM( COALESCE(componentedisciplina.cargahoraria, 0) + COALESCE(componentetcc.cargahoraria, 0) ) INTO v_cargahoraria
    FROM acpcomponentecurricular componentecurricular
         LEFT JOIN acpcomponentecurricularmatriz componentecurricularmatriz on componentecurricularmatriz.componentecurricularid=componentecurricular.componentecurricularid
           LEFT JOIN acpmatrizcurriculargrupo matrizgrupo on componentecurricularmatriz.matrizcurriculargrupoid=matrizgrupo.matrizcurriculargrupoid
             LEFT JOIN acpmatrizcurricular matriz on matrizgrupo.matrizcurricularid=matriz.matrizcurricularid
               LEFT JOIN acpcurso curso on matriz.cursoid=curso.cursoid
             LEFT JOIN acpocorrenciacurso ocorrencia on ocorrencia.cursoid=curso.cursoid
               LEFT JOIN acpofertacurso oferta on oferta.ocorrenciacursoid=ocorrencia.ocorrenciacursoid
                 LEFT JOIN acpofertaturma turma on turma.ofertacursoid=oferta.ofertacursoid
                   LEFT JOIN acpinscricaoturmagrupo inscricaoturmagrupo on inscricaoturmagrupo.ofertaturmaid=turma.ofertaturmaid
                 LEFT JOIN acpinscricao inscricao on inscricaoturmagrupo.inscricaoid=inscricao.inscricaoid
         LEFT JOIN acpcomponentecurriculardisciplina componentedisciplina on componentedisciplina.componentecurricularid=componentecurricular.componentecurricularid
         LEFT JOIN acpcomponentecurriculartrabalhoconclusao componentetcc on componentetcc.componentecurricularid=componentecurricular.componentecurricularid
    WHERE inscricao.inscricaoid = p_inscricaoid
      AND turma.ofertaturmaid = p_ofertaturmaid;
ELSE
   --Carga horaria inscricao turma grupo
   SELECT 
       SUM( COALESCE(componentedisciplina.cargahoraria, 0) + COALESCE(componentetcc.cargahoraria, 0) ) INTO v_cargahoraria
    FROM acpcomponentecurricular componentecurricular
         LEFT JOIN acpcomponentecurricularmatriz componentecurricularmatriz on componentecurricularmatriz.componentecurricularid=componentecurricular.componentecurricularid
           LEFT JOIN acpofertacomponentecurricular ofertacomponente on ofertacomponente.componentecurricularmatrizid=componentecurricularmatriz.componentecurricularmatrizid
             LEFT JOIN acpofertaturma ofertaturma on ofertacomponente.ofertaturmaid=ofertaturma.ofertaturmaid
               LEFT JOIN acpinscricaoturmagrupo inscricaoturma on (inscricaoturma.ofertaturmaid=ofertaturma.ofertaturmaid)
         LEFT JOIN acpcomponentecurriculardisciplina componentedisciplina on componentedisciplina.componentecurricularid=componentecurricular.componentecurricularid
         LEFT JOIN acpcomponentecurriculartrabalhoconclusao componentetcc on componentetcc.componentecurricularid=componentecurricular.componentecurricularid
    WHERE inscricaoturma.inscricaoid = p_inscricaoid
      AND inscricaoturma.ofertaturmaid = p_ofertaturmaid;
END IF;

RETURN v_cargahoraria;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION acp_cargahorariatotal(integer, integer)
OWNER TO postgres;
--
