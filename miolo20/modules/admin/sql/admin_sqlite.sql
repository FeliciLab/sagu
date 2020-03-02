create table miolo_sequence ( 
       sequence                      character(30)       not null primary key,
       value                         integer);

insert into miolo_sequence values('seq_miolo_user',0);
insert into miolo_sequence values('seq_miolo_transaction',0);
insert into miolo_sequence values('seq_miolo_group',0);
insert into miolo_sequence values('seq_miolo_session',0);
insert into miolo_sequence values('seq_miolo_log',0);

create table miolo_user ( 
       iduser                        integer        not null primary key,
       login                         character(25),
       name                          character(80),
       nickname                      character(25),
       m_password                      character(40),
       confirm_hash                  character(40),
       theme                         character(20));

create table miolo_transaction ( 
       idtransaction                 integer        not null primary key,
       m_transaction                   character(30));

create table miolo_group ( 
       idgroup                       integer        not null primary key,
       m_group                         character(50));

create table miolo_access ( 
       idgroup                       integer        not null,
       idtransaction                 integer        not null,
       rights                        integer);

create table miolo_session ( 
       idsession                     integer        not null primary key,
       tsin                          character(15),
       tsout                         character(15),
       name                          character(50),
       sid                           character(40),
       forced                        character(1),
       remoteaddr                    character(15),
       iduser                        integer        not null);

create table miolo_log ( 
       idlog                         integer        not null primary key,
       m_timestamp                     character(15),
       description                   character(200),
       module                        character(25),
       class                         character(25),
       iduser                        integer        not null,
       idtransaction                 integer        not null);

create table miolo_groupuser ( 
       iduser                        integer        not null,
       idgroup                       integer        not null);


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
