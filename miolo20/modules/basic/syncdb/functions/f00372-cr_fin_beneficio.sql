CREATE OR REPLACE FUNCTION cr_fin_beneficio(p_personId BIGINT, p_vigente BOOLEAN, p_periodoId VARCHAR)
RETURNS TABLE (personId BIGINT,
               personName VARCHAR,
               cpf VARCHAR,
               courseName VARCHAR,
               periodo VARCHAR,
               dataInicial TEXT,
               dataFinal TEXT,
               beneficio TEXT,
               data_pagamento_valor_financiado DATE) AS
$BODY$
DECLARE

    v_sql TEXT;
BEGIN

   v_sql := '
   SELECT COALESCE(A.codigo_pessoa, B.codigo_pessoa) AS personid,
          COALESCE(A.nome_pessoa, B.pessoa) AS personname,
          COALESCE(A.cpf_pessoa, B.cpf) AS cpf,
          COALESCE(A.nome_curso, B.curso) AS coursename,
          COALESCE(A.periodo_academico, B.codigo_periodo) AS periodo,
          COALESCE(A.data_inicial, B.data_inicio_incentivo) AS dataincial,
          COALESCE(A.data_final, B.data_fim_incentivo) AS datafinal,
          COALESCE(A.descricao_convenio, B.tipo_incentivo) AS beneficio,
          data_pagamento_valor_financiado
     FROM cr_fin_convenio A
LEFT JOIN cr_fin_incentivo B
       ON (B.codigo_pessoa = A.codigo_pessoa)
    WHERE (CASE WHEN char_length(''' || p_periodoId || ''') > 0
                THEN
                    (COALESCE(B.codigo_periodo, A.periodo_academico) = ''' || p_periodoId || ''')
                ELSE
                    A.codigo_pessoa = ' || p_personId || '
           END)
      AND (CASE WHEN ' || p_vigente || '
                THEN
                (CASE WHEN B.codigo_contrato IS NOT NULL
                      THEN
                          B.incentivo_vigente
                      WHEN A.codigo_contrato IS NOT NULL
                      THEN
                          A.vigente
                 END)
                ELSE
                    A.codigo_pessoa = ' || p_personId || '
            END)
      AND A.codigo_pessoa = ' || p_personId || ';';

      RETURN QUERY EXECUTE v_sql;
END;

$BODY$
LANGUAGE plpgsql;