CREATE OR REPLACE function cr_acp_gera_calendario_aulas(IN data_inicial VARCHAR, IN data_final VARCHAR, IN ofertaturmaid int)  
RETURNS TABLE(data DATE, dom VARCHAR, seg VARCHAR, ter VARCHAR, qua VARCHAR, qui VARCHAR, sex VARCHAR, sab VARCHAR )
AS
 $BODY$ 
DECLARE
v_sql TEXT;
BEGIN
    v_sql := '
        select * from crosstab(''Select 
        B.dataaula-(to_char(B.dataaula,\'''D\''')::int-1) as semana, 
        to_char(B.dataaula,\'''D\''') as dia, 
        D.horainicio ||\'''-\'''|| D.horafim ||\''' - \'''|| L.descricao ||\''' - \'''|| K.descricao ||\''' - \'''|| F.descricao ||\''' - \'''|| I.nome ||\''' - \'''|| C.name 
        From acpocorrenciahorariooferta B 
        Inner Join only basphysicalpersonprofessor C on B.professorid=C.personid 
        Inner Join acphorario D on B.horarioid=D.horarioid 
        Inner Join acpofertacomponentecurricular E on B.ofertacomponentecurricularid=E.ofertacomponentecurricularid 
        Inner Join acpcomponentecurricularmatriz J on E.componentecurricularmatrizid=J.componentecurricularmatrizid 
        Inner Join acpmatrizcurriculargrupo K on J.matrizcurriculargrupoid=K.matrizcurriculargrupoid 
        Inner Join acpcomponentecurricular L on J.componentecurricularid=L.componentecurricularid 
        Inner Join acpofertaturma F on E.ofertaturmaid=F.ofertaturmaid 
        Inner Join acpofertacurso G on F.ofertacursoid=G.ofertacursoid 
        Inner Join acpocorrenciacurso H on G.ocorrenciacursoid=H.ocorrenciacursoid 
        Inner Join acpcurso I on H.cursoid=I.cursoid 
        WHERE B.dataaula BETWEEN datetodb('''''||$1||''''') AND datetodb('''''||$2||''''')
          AND F.ofertaturmaid='''''||$3||'''''
        order by B.dataaula'',
        ''select m from generate_series(1,7) m''
        ) as (
        dia date,
        "Dom" varchar,
        "Seg" varchar,
        "Ter" varchar,
        "Qua" varchar,
        "Qui" varchar,
        "Sex" varchar,
        "Sab" varchar
        )';

    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql';
