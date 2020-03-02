CREATE OR REPLACE VIEW view_capsolicitacaopagamento AS
      SELECT A.solicitacaoid AS solicitacaoid,
             A.personid AS cod_usuario,
             getPersonName(A.personid) AS usuario,
             A.solicitacaoestadoid AS cod_estado_solicitacao,
             B.nome AS estado_solicitacao,
             A.dadoscompra AS dados_compra,
             A.justificativa,
             A.costcenterid AS cod_centro_custos,
             C.description AS centro_custos,
             A.fornecedorid AS cod_fornecedor,
             getPersonName(A.fornecedorid) AS fornecedor,
             A.formadepagamentoid AS cod_forma_pagamento,
             D.descricao AS forma_pagamento,
             datetouser(A.datasolicitacao) AS data_solicitacao,
             A.datasolicitacao AS data_solicitacao_sem_formatacao,
             A.accountschemeid AS cod_plano_contas,
             E.description AS plano_contas,
             A.numerodanotafiscal AS nota_fiscal,
             SUM(F.valor) AS valor_total,
             COUNT(F.*) AS parcelas
        FROM capsolicitacao A
   LEFT JOIN capsolicitacaoestado B
          ON A.solicitacaoestadoid = B.solicitacaoestadoid
   LEFT JOIN acccostcenter C
          ON A.costcenterid = C.costcenterid
   LEFT JOIN capformadepagamento D
          ON A.formadepagamentoid = D.formadepagamentoid 
   LEFT JOIN accaccountscheme E
          ON A.accountschemeid = E.accountschemeid
   LEFT JOIN capsolicitacaoparcela F
          ON A.solicitacaoid = F.solicitacaoid
    GROUP BY A.solicitacaoid, A.personid, A.solicitacaoestadoid, B.nome, A.dadoscompra,
             A.justificativa, A.costcenterid, A.costcenterid, C.description, A.fornecedorid,
             D.descricao, A.formadepagamentoid, A.datasolicitacao, A.accountschemeid, E.description, A.numerodanotafiscal;
