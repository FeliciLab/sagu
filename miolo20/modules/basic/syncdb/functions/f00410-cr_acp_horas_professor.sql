CREATE OR REPLACE FUNCTION cr_acp_horas_professor(p_datainicial TEXT, p_datafinal TEXT, p_tipo BOOLEAN DEFAULT TRUE)
RETURNS TABLE (
    cod_professor BIGINT,
    professor VARCHAR,
    turma VARCHAR,
    curso VARCHAR,
    periodo TEXT,
    horario TEXT,
    conteudo TEXT,
    total_minutos_aula BIGINT,
    total_horas_aula NUMERIC(10,2)
) AS
$BODY$
	BEGIN
	    RETURN QUERY (
		SELECT  C.personid,
			C.name,
			F.descricao,
			I.nome,
			CASE WHEN p_tipo THEN
			    TO_CHAR(B.dataaula, getParameter('BASIC', 'MASK_DATE')) 
			ELSE
			    'Período: '|| p_datainicial ||' e '|| p_datafinal 
			END,
			CASE WHEN p_tipo THEN
			    'de '||D.horainicio||' até '||D.horafim 
			ELSE
			    '-'
			END,           
			CASE WHEN p_tipo THEN
			    B.conteudo 
			ELSE
			    '-'
			END,
			sum(D.minutosfrequencia),
			(sum(D.minutosfrequencia)::numeric/60)::numeric(10,2) 
		       FROM acpocorrenciahorariooferta B
		 INNER JOIN ONLY basphysicalpersonprofessor C on B.professorid=C.personid
		 INNER JOIN acphorario D on B.horarioid=D.horarioid
		 INNER JOIN acpofertacomponentecurricular E on B.ofertacomponentecurricularid=E.ofertacomponentecurricularid
		 INNER JOIN acpofertaturma F on E.ofertaturmaid=F.ofertaturmaid
		 INNER JOIN acpofertacurso G on F.ofertacursoid=G.ofertacursoid
		 INNER JOIN acpocorrenciacurso H on G.ocorrenciacursoid=H.ocorrenciacursoid
		 INNER JOIN acpcurso I on H.cursoid=I.cursoid
		      WHERE B.ocorrenciahorarioofertaid in (select ocorrenciahorarioofertaid from acpfrequencia)
			AND B.dataaula BETWEEN  datetodb(p_datainicial) AND datetodb(p_datafinal)
		   GROUP BY C.personid , C.name , F.descricao , I.nome ,
			    CASE WHEN p_tipo THEN TO_CHAR(B.dataaula, getParameter('BASIC', 'MASK_DATE')) 
			    ELSE 'Período: '|| p_datainicial ||' e '|| p_datafinal END,
			    CASE WHEN p_tipo THEN 'de '||D.horainicio||' até '||D.horafim 
			    ELSE '-' END,
			    CASE WHEN p_tipo THEN B.conteudo 
			    ELSE '-' END
		   ORDER BY C.personid, C.name, 
			    CASE WHEN p_tipo THEN TO_CHAR(B.dataaula, getParameter('BASIC', 'MASK_DATE')) 
			    ELSE 'Período: '|| p_datainicial ||' e '|| p_datafinal END,
			    CASE WHEN p_tipo THEN 'de '||D.horainicio||' até '||D.horafim 
			    ELSE '-' END
			    );
	END;
$BODY$
LANGUAGE plpgsql;
