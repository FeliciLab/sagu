CREATE OR REPLACE FUNCTION verifica_matricula_todas_disciplinas(p_contractid integer, p_learningperiodid integer)
  RETURNS INTEGER AS
$BODY$
/*************************************************************************************
  NAME: verifica_matricula_todas_disciplinas
  
  PURPOSE: Verifica se o aluno está cursando todas disciplinas de um semestre do curso,
           caso esteja retorna o número do semestre onde o aluno está cursando todas as
           disciplinas, caso contrário retorna 0
           
  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       15/09/2014 ftomasini         1. FUNÇÃO criada.
**************************************************************************************/

DECLARE
    --Variavel record que irá percorrer todos os semestres da matriz do curso
    v_linha record;
    --Variavel que irá armazenar o número de créditos que o alunos está matriculado para cada semestre da matriz
    v_creditos_matriculado INTEGER;
    --Variável que irá armazenar o número de créditos do semestre na matriz do curso do aluno
    v_creditos_matriz INTEGER;
    --Contrato do aluno
    v_contract acdContract;
    --Semestre corrente que está sendo analisado
    v_semester INTEGER;
BEGIN
    --Inicializa variáveis
    v_creditos_matriculado := 0;
    v_creditos_matriz := 0;
    v_semester := 0;

    --Obtém o contrato
    SELECT INTO v_contract * FROM acdContract WHERE contractId = p_contractid;

    --Percorre semestres da matriz do curso do aluno trazendo o número de créditos da matríz e o numero de créditos
    --que o aluno está matriculado em cada um deles
    FOR v_linha IN SELECT C.semester AS semestre,
                          --Obtem o numero de creditos da matriz do curso do aluno para o periodo
                          (SELECT sum(academiccredits) as creditos
                             FROM acdcurriculum AA
                       INNER JOIN acdcurricularcomponent BB 
                               ON (BB.curricularcomponentid = AA.curricularcomponentid 
                                   AND BB.curricularcomponentversion = AA.curricularcomponentversion)
                            WHERE AA.courseid = C.courseid
                              AND AA.courseversion = C.courseversion
                              AND AA.unitid = C.unitid
                              AND AA.turnid = C.turnid
                              AND AA.semester = C.semester
                              AND AA.curriculumtypeid = 5) AS creditos_matriz,
                              --Obtem o numero de creditos matriculados para o periodo 
                              sum(D.academiccredits) AS creditos_matricula
                     FROM acdenroll A
               INNER JOIN acdgroup B 
                       ON (B.groupid = A.groupid)
               INNER JOIN acdcurriculum C 
                       ON (A.curriculumid = C.curriculumid)
               INNER JOIN acdcurricularcomponent D 
                       ON (D.curricularcomponentid = C.curricularcomponentid 
                           AND D.curricularcomponentversion = C.curricularcomponentversion)
               INNER JOIN acdlearningperiod E 
                       ON (E.learningperiodid = B.learningperiodid)
                    WHERE A.contractid = p_contractid
                      -- status de matricula diferente de CANCELADO,DESISTENTE E TRANSFRENCIA 
                      AND A.statusid NOT IN (GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_CANCELLED')::int, 
                                             GETPARAMETER('ACADEMIC', 'ENROLL_STATUS_DESISTING')::int, 
                                             GETPARAMETER('ACADEMIC', 'EXTERNAL_EXPLOITATION_DEFAULT_ENROLL_STATUS')::int)
                      AND E.periodid IN (SELECT periodid 
                                           FROM acdlearningperiod 
                                          WHERE learningperiodid = p_learningperiodid)
                 GROUP BY C.courseid, C.courseversion, C.turnid, C.unitid, C.semester 
    LOOP
        --Verifica se o numero de creditos matriculado no periodo é igual ao numero de creditos da matriz do curso
        --e o número de créditos matriculado é maior que o número já definido(o desconto será concedido no semestre
        --onde o aluno tem o maior número de créditos matriculado)
        IF ((v_linha.creditos_matricula >= v_linha.creditos_matriz) AND 
           (v_linha.creditos_matricula > v_creditos_matriculado))
        THEN
            v_creditos_matriculado:= v_linha.creditos_matricula;   
            v_creditos_matriz:= v_linha.creditos_matriz;
            v_semester := v_linha.semestre;
        END IF;
    END LOOP;
     RETURN v_semester;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
