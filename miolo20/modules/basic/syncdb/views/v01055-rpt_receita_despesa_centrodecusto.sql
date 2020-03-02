CREATE OR REPLACE VIEW rpt_receita_despesa_centrodecusto AS
    SELECT a.costcenterid,
           c.description,
           d.operationtypeid,
           a.value,
           a.entrydate,
           b.maturitydate
      FROM finentry a
INNER JOIN ONLY finreceivableinvoice b
        ON a.invoiceid = b.invoiceid
INNER JOIN acccostcenter c
        ON c.costcenterid = a.costcenterid
INNER JOIN finoperation d
        ON d.operationid = a.operationid
     WHERE b.iscanceled is false
       AND b.balance = 0;

COMMENT ON VIEW rpt_receita_despesa_centrodecusto IS '
NOME:
rpt_receita_despesa_centrodecusto
DESCRICAO:
View
Visão que pode ser utilizada para relatórios de despesas e receitas agrupado por centro de custo
REVISÕES:
1.0 - ftomasini - Visão criada
';

COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.costcenterid IS 'Código do centro de custo';
COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.description IS 'Descrição do centro de custo';
COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.operationtypeid IS 'Operação do título';
COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.value IS 'Valor do lançamento';
COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.entrydate IS 'Data do lançamento';
COMMENT ON COLUMN rpt_receita_despesa_centrodecusto.maturitydate IS 'Data de vencimento do título';

