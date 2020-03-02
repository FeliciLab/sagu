-- Grupos do avinst para o miolo25
SELECT setval('miolo_group_idgroup_seq', 9);
INSERT INTO miolo_group (m_group) VALUES ('AVINST_ROOT');
INSERT INTO miolo_group (m_group) VALUES ('AVINST_ADMINISTRADOR');

-- Transações do avinst para o miolo25
INSERT INTO miolo_module VALUES ('avinst','Avaliação Institucional','Avaliação Institucional');
SELECT setval('miolo_transaction_idtransaction_seq', 12);
INSERT INTO miolo_transaction (m_transaction,idmodule) VALUES ('AVINST_ROOT','avinst');
INSERT INTO miolo_transaction (m_transaction,idmodule) VALUES ('AVINST_ADMINISTRADOR','avinst');

INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ROOT') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ROOT') as idgroup, 1 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ROOT') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ROOT') as idgroup, 2 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ROOT') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ROOT') as idgroup, 3 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ROOT') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ROOT') as idgroup, 4 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ROOT') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ROOT') as idgroup, 6 as rights, null as validatefunction;

INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ADMINISTRADOR') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ADMINISTRADOR') as idgroup, 1 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ADMINISTRADOR') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ADMINISTRADOR') as idgroup, 2 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ADMINISTRADOR') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ADMINISTRADOR') as idgroup, 3 as rights, null as validatefunction;
INSERT INTO miolo_access SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'AVINST_ADMINISTRADOR') as idtransaction, (SELECT idgroup FROM miolo_group WHERE m_group = 'AVINST_ADMINISTRADOR') as idgroup, 5 as rights, null as validatefunction;

