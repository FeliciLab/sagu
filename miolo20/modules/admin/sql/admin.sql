CREATE TABLE miolo_sequence ( 
       sequence                      CHAR(20)       NOT NULL,
       value                         INTEGER);

ALTER TABLE miolo_sequence ADD CONSTRAINT PK_miolo_sequence PRIMARY KEY(sequence);

CREATE TABLE miolo_user ( 
       iduser                        INTEGER        NOT NULL,
       login                         CHAR(25),
       name                          VARCHAR(80),
       nickname                      CHAR(25),
       m_password                    CHAR(40),
       confirm_hash                  CHAR(40),
       theme                         CHAR(20));

ALTER TABLE miolo_user ADD CONSTRAINT PK_miolo_user PRIMARY KEY(iduser);

CREATE TABLE miolo_transaction ( 
       idtransaction                 INTEGER        NOT NULL,
       m_transaction                 CHAR(30));

ALTER TABLE miolo_transaction ADD CONSTRAINT PK_miolo_transaction PRIMARY KEY(idtransaction);

CREATE TABLE miolo_group ( 
       idgroup                       INTEGER        NOT NULL,
       m_group                       CHAR(50));

ALTER TABLE miolo_group ADD CONSTRAINT PK_miolo_group PRIMARY KEY(idgroup);

CREATE TABLE miolo_access ( 
       idgroup                       INTEGER        NOT NULL,
       idtransaction                 INTEGER        NOT NULL,
       rights                        INTEGER);

ALTER TABLE miolo_access ADD CONSTRAINT PK_miolo_access PRIMARY KEY(idgroup,idtransaction);
ALTER TABLE miolo_access ADD CONSTRAINT FK_miolo_access2_miolo FOREIGN KEY(idgroup) REFERENCES miolo_group ON DELETE CASCADE;
ALTER TABLE miolo_access ADD CONSTRAINT FK_miolo_access1_miolo FOREIGN KEY(idtransaction) REFERENCES miolo_transaction ON DELETE CASCADE;

CREATE TABLE miolo_session ( 
       idsession                     INTEGER        NOT NULL,
       tsin                          CHAR(15),
       tsout                         CHAR(15),
       name                          CHAR(50),
       sid                           CHAR(40),
       forced                        CHAR(1),
       remoteaddr                    CHAR(15),
       iduser                        INTEGER        NOT NULL);

ALTER TABLE miolo_session ADD CONSTRAINT PK_miolo_session PRIMARY KEY(idsession);
ALTER TABLE miolo_session ADD CONSTRAINT FK_miolo_session1_miolo FOREIGN KEY(iduser) REFERENCES miolo_user;

CREATE TABLE miolo_log ( 
       idlog                         INTEGER        NOT NULL,
       m_timestamp                   CHAR(15),
       description                   VARCHAR(200),
       module                        CHAR(25),
       class                         CHAR(25),
       iduser                        INTEGER        NOT NULL,
       idtransaction                 INTEGER        NOT NULL);

ALTER TABLE miolo_log ADD CONSTRAINT PK_miolo_log PRIMARY KEY(idlog);
ALTER TABLE miolo_log ADD CONSTRAINT FK_miolo_log2_miolo FOREIGN KEY(idtransaction) REFERENCES miolo_transaction;
ALTER TABLE miolo_log ADD CONSTRAINT FK_miolo_log1_miolo FOREIGN KEY(iduser) REFERENCES miolo_user;

CREATE TABLE miolo_groupuser ( 
       iduser                        INTEGER        NOT NULL,
       idgroup                       INTEGER        NOT NULL);

ALTER TABLE miolo_groupuser ADD CONSTRAINT PK_miolo_groupuser PRIMARY KEY(iduser,idgroup);
ALTER TABLE miolo_groupuser ADD CONSTRAINT FK_miolo_groupuser1_miolo FOREIGN KEY(idgroup) REFERENCES miolo_group ON DELETE CASCADE;
ALTER TABLE miolo_groupuser ADD CONSTRAINT FK_miolo_groupuser2_miolo FOREIGN KEY(iduser) REFERENCES miolo_user ON DELETE CASCADE;
