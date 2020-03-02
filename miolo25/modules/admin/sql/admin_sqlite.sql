-- MIOLO Admin schema
-- Tested with Mysql, PostgreSql and SQLite

CREATE TABLE miolo_module (
  idmodule VARCHAR(40) NOT NULL,
  name VARCHAR(100) NULL,
  description TEXT NULL,
  PRIMARY KEY(idmodule)
);

CREATE TABLE miolo_sequence (
  sequence VARCHAR(30) NOT NULL,
  value INTEGER NOT NULL,
  PRIMARY KEY(sequence)
);

CREATE TABLE miolo_user (
  iduser SERIAL NOT NULL,
  login VARCHAR(25) NOT NULL,
  name VARCHAR(100) NULL,
  nickname VARCHAR(25) NULL,
  m_password VARCHAR(40) NULL,
  confirm_hash VARCHAR(40) NULL,
  theme VARCHAR(20) NULL,
  PRIMARY KEY(iduser)
);

-- Without Foreign Keys to be possible to put this table in other database
-- (if the admin database uses SQLite)

CREATE TABLE miolo_log (
  idlog SERIAL NOT NULL,
  m_timestamp TIMESTAMP NOT NULL,
  description TEXT NULL,
  module VARCHAR(40) NOT NULL,
  class VARCHAR(25) NULL,
  iduser INTEGER NOT NULL,
  idtransaction INTEGER NULL,
  remoteaddr VARCHAR(15) NOT NULL,
  PRIMARY KEY(idlog)
);

CREATE TABLE miolo_group (
  idgroup SERIAL NOT NULL,
  m_group VARCHAR(50) NOT NULL,
  PRIMARY KEY(idgroup)
);

CREATE TABLE miolo_session (
  idsession SERIAL NOT NULL,
  iduser INTEGER NOT NULL,
  tsin VARCHAR(15) NULL,
  tsout VARCHAR(15) NULL,
  name VARCHAR(50) NULL,
  sid VARCHAR(40) NULL,
  forced CHAR NULL,
  remoteaddr VARCHAR(15) NULL,
  PRIMARY KEY(idsession),
  FOREIGN KEY(iduser)
    REFERENCES miolo_user(iduser)
);

CREATE TABLE miolo_transaction (
  idtransaction SERIAL NOT NULL,
  m_transaction VARCHAR(40) NOT NULL,
  idmodule VARCHAR(40) NULL,
  PRIMARY KEY(idtransaction),
  FOREIGN KEY(idmodule)
    REFERENCES miolo_module(idmodule)
);

CREATE TABLE miolo_schedule (
  idschedule SERIAL NOT NULL,
  idmodule VARCHAR(40) NOT NULL,
  action TEXT NOT NULL,
  parameters TEXT NULL,
  beginTime TIMESTAMP NULL,
  completed BOOL NOT NULL DEFAULT FALSE,
  running BOOL NOT NULL DEFAULT FALSE,
  PRIMARY KEY(idschedule),
  FOREIGN KEY(idmodule)
    REFERENCES miolo_module(idmodule)
);

CREATE TABLE miolo_access (
  idtransaction INTEGER NOT NULL,
  idgroup INTEGER NOT NULL,
  rights INTEGER NULL,
  validatefunction TEXT NULL,
  FOREIGN KEY(idtransaction)
    REFERENCES miolo_transaction(idtransaction)
);

CREATE TABLE miolo_groupuser (
  iduser INTEGER NOT NULL,
  idgroup INTEGER NOT NULL,
  PRIMARY KEY(iduser, idgroup),
  FOREIGN KEY(iduser)
    REFERENCES miolo_user(iduser)
      ,
  FOREIGN KEY(idgroup)
    REFERENCES miolo_group(idgroup)
);



insert into miolo_sequence values('seq_miolo_user',0);
insert into miolo_sequence values('seq_miolo_transaction',0);
insert into miolo_sequence values('seq_miolo_group',0);
insert into miolo_sequence values('seq_miolo_session',0);
insert into miolo_sequence values('seq_miolo_log',0);

update miolo_sequence set value = 2 where sequence = 'seq_miolo_user';
insert into miolo_user (iduser,login,name,nickname,m_password,confirm_hash,theme)
   values (1,'admin','Miolo Administrator','admin','admin','','miolo');
insert into miolo_user (iduser,login,name,nickname,m_password,confirm_hash,theme)
   values (2,'guest','Guest User','guest','guest','','miolo');

update miolo_sequence set value = 5 where sequence = 'seq_miolo_transaction';
insert into miolo_transaction (idtransaction, m_transaction) values (1,'ADMIN');
insert into miolo_transaction (idtransaction, m_transaction) values (2,'USER');
insert into miolo_transaction (idtransaction, m_transaction) values (3,'GROUP');
insert into miolo_transaction (idtransaction, m_transaction) values (4,'LOG');
insert into miolo_transaction (idtransaction, m_transaction) values (5,'TRANSACTION');
insert into miolo_transaction (idtransaction, m_transaction) values (6,'SESSION');

update miolo_sequence set value = 3 where sequence = 'seq_miolo_group';
insert into miolo_group (idgroup, m_group) values (1,'ADMIN');
insert into miolo_group (idgroup, m_group) values (2,'MAIN_RO');
insert into miolo_group (idgroup, m_group) values (3,'MAIN_RW');

insert into miolo_access (idgroup, idtransaction, rights) values (2,1,1);
insert into miolo_access (idgroup, idtransaction, rights) values (2,2,1);
insert into miolo_access (idgroup, idtransaction, rights) values (2,3,1);
insert into miolo_access (idgroup, idtransaction, rights) values (2,4,1);
insert into miolo_access (idgroup, idtransaction, rights) values (2,5,1);
insert into miolo_access (idgroup, idtransaction, rights) values (2,6,1);
insert into miolo_access (idgroup, idtransaction, rights) values (3,1,15);
insert into miolo_access (idgroup, idtransaction, rights) values (3,2,15);
insert into miolo_access (idgroup, idtransaction, rights) values (3,3,15);
insert into miolo_access (idgroup, idtransaction, rights) values (3,4,15);
insert into miolo_access (idgroup, idtransaction, rights) values (3,5,15);
insert into miolo_access (idgroup, idtransaction, rights) values (3,6,15);

insert into miolo_groupuser (idgroup, iduser) values (1,1);
insert into miolo_groupuser (idgroup, iduser) values (2,1);
insert into miolo_groupuser (idgroup, iduser) values (3,1);
insert into miolo_groupuser (idgroup, iduser) values (2,2);

INSERT INTO miolo_module (idmodule, name) values('admin','admin');
INSERT INTO miolo_module (idmodule, name) values('common','common');
INSERT INTO miolo_module (idmodule, name) values('helloworld','helloworld');
INSERT INTO miolo_module (idmodule, name) values('hangman','hangman');
INSERT INTO miolo_module (idmodule, name) values('tutorial','tutorial');
INSERT INTO miolo_module (idmodule, name) values('exemplo','exemplo');
