CREATE OR REPLACE FUNCTION obtemPoliticaDoPreco(p_contractid integer, p_parcelnumber integer, p_learningperiodid integer)
  RETURNS integer AS
$BODY$
/*************************************************************************************
  NAME: obtemPolíticaPreco
  PURPOSE: Retorna o código da política vigente para o período/semestre em que o aluno
  se encontra.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       11/02/2013 Samuel Koch       1. FUNÇÃO criada.
  1.1       18/07/2013 Samuel Koch       1. Alteração onde foi trocado a função 
                                            que obtem o semestre do aluno.
  1.2       13/01/2014 Samuel Koch       1. Alteração da função inserindo 
  1.3       13/01/2014 ftomasini         1. Alteração na lógica que pega a politica
                                            do preço
  1.4       24/02/2014 Samuel Koch       1. Alterado função que obtem o semestre.
**************************************************************************************/
DECLARE
    v_classId   varchar; --Armazena a turma do aluno
    v_semester  integer; --Armazena o semestre que o aluno se encontra no curso
    v_policyId  integer; --Código da política a ser aplicada no boleto
    v_controle  integer; --Variável de controle
    v_contrato  record; --Armazena as informaçÃµes de contrato
    v_select    text; --Armazena a consulta para verificar a política
    v_beginDate date; --Data base para pesquisar o preço.
    v_date      date; --Data para pesquisar o preço.
BEGIN

    --Obtem o semestre da turma
    v_semester :=  get_semester_contract(p_contractid);

    --Obtem a data inicial do período letivo para filtrar o preço
    SELECT A.beginDate INTO v_beginDate
      FROM acdLearningPeriod A
     WHERE A.learningPeriodId = p_learningPeriodId;

    --Obtem os dados do contrato do aluno
    SELECT * INTO v_contrato
      FROM acdContract
     WHERE contractId = p_contractId;

    --Obtem a data inicial do preço
    SELECT B.startDate INTO v_date
      FROM finPrice B
     WHERE B.courseId = v_contrato.courseId
       AND B.courseVersion = v_contrato.courseVersion
       AND B.turnId = v_contrato.turnId
       AND B.unitId = v_contrato.unitId
       AND v_beginDate BETWEEN B.startDate AND B.endDate;

    IF v_date IS NULL THEN
        RAISE EXCEPTION 'Não foi possível obter a data inicial cadastrada no preço desta disciplina. Verifique as datas iniciais do período letivo e do preço, cadastrados para está disciplina.';
    END IF;

       v_select := ' SELECT A.policyId
                        FROM finPricePolicy A
                       WHERE A.startDate = TO_DATE(''' || v_date || ''',''yyyy-mm-dd'')
                         AND A.courseId = ''' || v_contrato.courseId || '''
                         AND A.courseVersion = ' || v_contrato.courseVersion || '
                         AND A.turnId = ' || v_contrato.turnId || '
                         AND A.unitId = ' || v_contrato.unitId || ' ';


       --Verifica as possibilidades de configuração de preço.
       --Semestre nao nulo e parcela nao nula
       IF (v_semester IS NOT NULL AND p_parcelNumber IS NOT NULL) 
       THEN
           EXECUTE v_select || ' AND A.parcelNumber = ' || p_parcelNumber || ' AND A.semester = ' || v_semester INTO v_policyId;           
           --Semestre nao nulo e parcela nula
           IF ( v_policyId IS NULL ) 
           THEN
               EXECUTE v_select || ' AND A.semester = ' || v_semester || ' AND A.parcelNumber IS NULL ' INTO v_policyId;
           END IF;
           --Semestre nulo e parcela nao nula
           IF ( v_policyId IS NULL )
           THEN
	       EXECUTE v_select || ' AND A.parcelNumber = ' || p_parcelNumber || ' AND A.semester IS NULL ' INTO v_policyId;
           END IF;
           --Semestre e parcela nulos
           IF ( v_policyId IS NULL )
           THEN 
               EXECUTE v_select || ' AND A.parcelNumber IS NULL AND A.semester IS NULL ' INTO v_policyId;
           END IF;
       END IF;

       IF ( v_policyId IS NULL )
       THEN
           RAISE EXCEPTION 'Defina uma política para o preço do curso';
       ELSE
           RETURN v_policyId;
       END IF;
  END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION obtempoliticadopreco(integer, integer, integer)
  OWNER TO postgres;
--
