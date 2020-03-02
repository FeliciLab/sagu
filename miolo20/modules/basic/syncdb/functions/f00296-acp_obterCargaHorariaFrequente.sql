REATE OR REPLACE FUNCTION acp_obterCargaHorariaFrequente (p_cursoid INTEGER, p_personid INTEGER)
RETURNS NUMERIC(10,2) AS
$BODY$
DECLARE

    -- Carga horária registrada
    v_cargahorariafrequente NUMERIC(10,2);

    -- Carga horária de comp. curr. sem controle de frequência
    v_cargahorariasemcontrole NUMERIC(10,2);
BEGIN

    -- Obtém a carga horária que possui registros
    SELECT INTO v_cargahorariafrequente 
                SUM(COALESCE(H.minutosfrequencia * (CASE WHEN F.frequencia IN ('P','J') 
                                                         THEN 
                                                             1 
                                                         WHEN F.frequencia = 'M' 
                                                         THEN 
                                                             0.5 
                                                         ELSE 
                                                             0 
                                                    END), 0) / 60)::NUMERIC(10,2)
      FROM acpcurso C
 LEFT JOIN acpocorrenciacurso OC 
        ON (C.cursoid = OC.cursoid) 
 LEFT JOIN acpofertacurso OFC 
        ON (OFC.ocorrenciacursoid = OC.ocorrenciacursoid)
 LEFT JOIN acpofertaturma OFT 
        ON (OFC.ofertacursoid = OFT.ofertacursoid)
 LEFT JOIN acpofertacomponentecurricular OCC 
        ON (OFT.ofertaturmaid = OCC.ofertaturmaid)
 LEFT JOIN acpmatricula M 
        ON (OCC.ofertacomponentecurricularid = M.ofertacomponentecurricularid)
 LEFT JOIN acpOcorrenciaHorarioOferta OCO 
        ON (OCO.ofertacomponentecurricularid = OCC.ofertacomponentecurricularid)
 LEFT JOIN acpFrequencia F 
        ON (F.ocorrenciahorarioofertaid = OCO.ocorrenciahorarioofertaid AND 
            F.matriculaId = M.matriculaid)
 LEFT JOIN acphorario H 
        ON (H.horarioId = OCO.horarioId)
     WHERE C.cursoid = p_cursoid  
       AND M.personid = p_personid 
       AND M.situacao IN ('A', 'P', 'E') 
       AND OCO.cancelada IS FALSE;

    -- Obtem a carga horária das disciplinas que não possuem controle de frequência
    -- Ticket #40670
    SELECT INTO v_cargahorariasemcontrole 
           COALESCE(SUM(acpComponenteCurricularDisciplina.cargaHoraria), 0)::NUMERIC(10,2)
      FROM acpCurso
 LEFT JOIN acpOcorrenciaCurso
        ON (acpOcorrenciaCurso.cursoId = acpCurso.cursoId)
 LEFT JOIN acpOfertaCurso
        ON (acpOfertaCurso.ocorrenciaCursoId = acpOcorrenciaCurso.ocorrenciaCursoId)
 LEFT JOIN acpOfertaTurma
        ON (acpOfertaTurma.ofertaCursoId = acpOfertaCurso.ofertaCursoId)
 LEFT JOIN acpOfertaComponenteCurricular
        ON (acpOfertaComponenteCurricular.ofertaTurmaId = acpOfertaTurma.ofertaTurmaId)
 LEFT JOIN acpMatricula
        ON (acpMatricula.ofertaComponenteCurricularId = acpOfertaComponenteCurricular.ofertaComponenteCurricularId)
 LEFT JOIN acpComponenteCurricularMatriz
        ON (acpOfertaComponenteCurricular.componenteCurricularMatrizId = acpComponenteCurricularMatriz.componenteCurricularMatrizId)
 LEFT JOIN acpComponenteCurricularDisciplina
        ON (acpComponenteCurricularDisciplina.componenteCurricularId = acpComponenteCurricularMatriz.componenteCurricularId)
     WHERE acpCurso.cursoId = p_cursoid
       AND acpMatricula.personId = p_personid
       AND (SELECT habilitaControleDeFrequencia
              FROM acp_obterModeloDaOfertaDeComponenteCurricular(acpOfertaComponenteCurricular.ofertaComponenteCurricularId)) IS FALSE;

     RETURN (v_cargahorariafrequente + v_cargahorariasemcontrole);

END;
$BODY$ LANGUAGE plpgsql;

