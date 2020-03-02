CREATE OR REPLACE FUNCTION obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes(p_invoiceId INT, p_operationGroupId CHAR, p_somenteAtreladosAIncentivosDeCobranca BOOLEAN DEFAULT NULL)
  RETURNS TABLE (valor numeric) AS
$BODY$
DECLARE

/**************************************************************************************
NOME: obterSaldoDosLancamentosDoTituloPeloGrupoDeOperacoes
PURPOSE: Retorna a soma de todos os lançamentos de um título, cuja operação for
         de um grupo de operações recebido por parâmetro.
REVISIONS:
Ver        Date       Author                      Description
---------- ---------- --------------------------- ----------------------------------
1.0        05/03/2015 Augusto A. Silva            Função criada.
1.1        20/04/2015 ftomasini                   Otimização
**************************************************************************************/

v_inner_incentivos varchar;
v_condicao_incentivos varchar;
v_select varchar;
BEGIN

v_inner_incentivos:='';
v_condicao_incentivos:='';
v_select:='';

IF p_somenteAtreladosAIncentivosDeCobranca IS NOT NULL
THEN
    v_inner_incentivos := 'LEFT JOIN finLoan L
                                  ON L.incentiveTypeId = E.incentiveTypeId
                                     AND L.geratitulodecobranca IS TRUE
                           LEFT JOIN finSupport S
                                  ON S.incentiveTypeId = E.incentiveTypeId
                                     AND S.geratitulodecobranca IS TRUE';

    v_condicao_incentivos := 'AND (CASE WHEN ' || p_somenteAtreladosAIncentivosDeCobranca ||' IS TRUE
                                   THEN
                                       (L.incentiveTypeId IS NOT NULL OR S.incentiveTypeId IS NOT NULL)
                                   ELSE
                                       TRUE
                                   END)';
END IF;

    v_select := 'SELECT SUM(COALESCE(X.credito, 0) - COALESCE(X.debito, 0))
                   FROM (SELECT (CASE WHEN O.operationTypeId = ''D'' THEN SUM(E.value) END) AS debito,
                                (CASE WHEN O.operationTypeId = ''C'' THEN SUM(E.value) END) AS credito
                           FROM finEntry E
                     INNER JOIN finoperation O
                             ON E.operationid = O.operationid ' 
                     || v_inner_incentivos ||
                         ' WHERE E.invoiceId = ' || p_invoiceId || '
                            AND o.operationgroupid = ''' || p_operationGroupId || '''
                            AND EXISTS (SELECT operationid
                                          FROM finoperation x
                                         WHERE x.operationgroupid = ''' || p_operationGroupId || '''
                                           AND x.operationid = E.operationid) ' 
                     || v_condicao_incentivos ||
                      ' GROUP BY O.operationTypeId) X';

    RETURN QUERY EXECUTE v_select;
END;
$BODY$
LANGUAGE plpgsql IMMUTABLE;