CREATE OR REPLACE FUNCTION obtemcreditoscategorizados(p_contractid integer, p_learningperiodid integer, p_semester integer, p_operador character varying)
  RETURNS integer AS
$BODY$
/**************************************************************************************************
  NAME: obterCreditosCategorizados
  PURPOSE: Retorna o numero de creditos do semestre que o aluno esta fazendo cheio e 
           o numero de creditos em outros semestres

  REVISIONS:
  Ver       Date       Author             Description
  --------- ---------- ------------------ ------------------------------------

  1.5       24/06/14   ftomasini          1. Retorna o numero de creditos conforme o operador
                                             passado por parametro se passado p_operado(=)
                                             retorna o numero de creditos referente ao semestre
                                             cheio
                                             Se passado p_operador(!=) retorna o numero de creditos
                                             em outros semestres.
***************************************************************************************************/
DECLARE
v_sql varchar;
v_return integer;
BEGIN
    v_sql:='select sum(academiccredits) from(	
                   SELECT distinct a.enrollid, a.statusid, d.name, D.academiccredits
              FROM acdEnroll A
        INNER JOIN acdGroup B
                ON (A.groupId = B.groupId)
        INNER JOIN acdCurriculum C
                ON (A.curriculumId = C.curriculumId)
        INNER JOIN acdCurricularComponent D
                ON (C.curricularComponentId = D.curricularComponentId AND
                    C.curricularComponentVersion = D.curricularComponentVersion)
         LEFT JOIN acdCurricularComponentUnblock F
                       ON (F.curriculumid = C.curriculumid 
                           AND F.contractid = A.contractid 
                           AND F.learningPeriodId = B.learningperiodid)
        INNER JOIN acdLearningPeriod E
                ON (E.learningPeriodId = B.learningPeriodId)
             WHERE E.periodId IN (SELECT periodId FROM acdLearningPeriod WHERE learningPeriodId = '''||p_learningPeriodId||''')
               AND A.contractId = '''||p_contractId||'''
               AND A.statusid <> GETPARAMETER(''ACADEMIC'', ''ENROLL_STATUS_CANCELLED'')::integer
               AND B.regimenId <> 3
               AND C.semester '||p_operador|| p_semester ||' ) x';

  EXECUTE v_sql 
     INTO v_return;
IF v_return IS null
THEN
  v_return:=0;
END IF;

RETURN v_return;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
