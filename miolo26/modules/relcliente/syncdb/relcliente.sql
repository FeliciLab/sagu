CREATE TABLE rccMensagemOuvidoria (MensagemOuvidoriaId  SERIAL NOT NULL, Nome varchar(255) NOT NULL, Email varchar(255), Telefone varchar(255), VinculoDeContatoId int4 NOT NULL, Matricula varchar(255), TipoDeContatoId int4 NOT NULL, AssuntoDeContatoId int4 NOT NULL, OrigemDeContatoId int4 NOT NULL, DataHora timestamp DEFAULT now() NOT NULL, Mensagem text NOT NULL, EstaCancelada bool NOT NULL, MotivoCancelamento text, PRIMARY KEY (MensagemOuvidoriaId));
COMMENT ON TABLE rccMensagemOuvidoria IS 'Mensagem da ouvidoria';
COMMENT ON COLUMN rccMensagemOuvidoria.MensagemOuvidoriaId IS 'Código da ouvidoria';
COMMENT ON COLUMN rccMensagemOuvidoria.VinculoDeContatoId IS 'Vínculo';
COMMENT ON COLUMN rccMensagemOuvidoria.Matricula IS 'Matrícula';
COMMENT ON COLUMN rccMensagemOuvidoria.TipoDeContatoId IS 'Tipo';
COMMENT ON COLUMN rccMensagemOuvidoria.AssuntoDeContatoId IS 'Assunto';
COMMENT ON COLUMN rccMensagemOuvidoria.OrigemDeContatoId IS 'Origem';
COMMENT ON COLUMN rccMensagemOuvidoria.DataHora IS 'Data hora';
COMMENT ON COLUMN rccMensagemOuvidoria.EstaCancelada IS 'Está cancelado';
COMMENT ON COLUMN rccMensagemOuvidoria.MotivoCancelamento IS 'Motivo cancelamento';
CREATE TABLE rccTipoDeContato (TipoDeContatoId  SERIAL NOT NULL, Descricao varchar(255) NOT NULL UNIQUE, PRIMARY KEY (TipoDeContatoId));
COMMENT ON TABLE rccTipoDeContato IS 'Tipo do contato';
COMMENT ON COLUMN rccTipoDeContato.TipoDeContatoId IS 'Código do tipo';
COMMENT ON COLUMN rccTipoDeContato.Descricao IS 'Descrição';
CREATE TABLE rccOrigemDeContato (OrigemDeContatoId  SERIAL NOT NULL, Descricao varchar(255) NOT NULL UNIQUE, PRIMARY KEY (OrigemDeContatoId));
COMMENT ON TABLE rccOrigemDeContato IS 'Origem do contato';
COMMENT ON COLUMN rccOrigemDeContato.OrigemDeContatoId IS 'Código da Origem';
COMMENT ON COLUMN rccOrigemDeContato.Descricao IS 'Descrição';
CREATE TABLE rccRespostaOuvidoria (RespostaOuvidoriaId  SERIAL NOT NULL, MensagemOuvidoriaId int4 NOT NULL, respondente int4 NOT NULL, OrigemDeContatoId int4 NOT NULL, DataHoraDaSolicitacao timestamp DEFAULT now() NOT NULL, DataHoraPrevista timestamp NOT NULL, Orientacao text, DataHoraDaResposta timestamp, Resposta text, PRIMARY KEY (RespostaOuvidoriaId));
COMMENT ON TABLE rccRespostaOuvidoria IS 'Resposta da ouvidoria';
COMMENT ON COLUMN rccRespostaOuvidoria.RespostaOuvidoriaId IS 'Código da resposta';
COMMENT ON COLUMN rccRespostaOuvidoria.OrigemDeContatoId IS 'Origem';
COMMENT ON COLUMN rccRespostaOuvidoria.DataHoraDaSolicitacao IS 'Data/hora da solicitação';
COMMENT ON COLUMN rccRespostaOuvidoria.DataHoraPrevista IS 'Data/hora prevista';
COMMENT ON COLUMN rccRespostaOuvidoria.Orientacao IS 'Orientação';
COMMENT ON COLUMN rccRespostaOuvidoria.DataHoraDaResposta IS 'Data/hora da resposta';
CREATE TABLE rccContato (ContatoId int4 NOT NULL, pessoa int4 NOT NULL, DataHoraPrevista timestamp, DataHoraDoContato timestamp DEFAULT now() NOT NULL, Orientacao text, Mensagem text, OrigemDeContatoId int4 NOT NULL, AssuntoDeContato int4 NOT NULL, TipoDeContatoId int4 NOT NULL, Operador varchar(255) NOT NULL, rccContatoContatoId int4, PRIMARY KEY (ContatoId));
COMMENT ON TABLE rccContato IS 'Contato';
COMMENT ON COLUMN rccContato.DataHoraPrevista IS 'Data/hora prevista';
COMMENT ON COLUMN rccContato.DataHoraDoContato IS 'Data/hora do contato';
COMMENT ON COLUMN rccContato.Orientacao IS 'Orientação';
COMMENT ON COLUMN rccContato.OrigemDeContatoId IS 'Origem';
COMMENT ON COLUMN rccContato.AssuntoDeContato IS 'Assunto';
COMMENT ON COLUMN rccContato.TipoDeContatoId IS 'Tipo';
CREATE TABLE rccInteresse (InteresseId  SERIAL NOT NULL, CursoId int4, ContratoId int4, DataHora timestamp DEFAULT now() NOT NULL, Nome varchar(255) NOT NULL, Telefone varchar(255) NOT NULL, Email varchar(255) NOT NULL, Cpf varchar(255) NOT NULL, Observacao text NOT NULL, PRIMARY KEY (InteresseId));
COMMENT ON TABLE rccInteresse IS 'Interesse';
COMMENT ON COLUMN rccInteresse.InteresseId IS 'Código do interesse';
COMMENT ON COLUMN rccInteresse.ContratoId IS 'Contrato';
COMMENT ON COLUMN rccInteresse.DataHora IS 'Data';
COMMENT ON COLUMN rccInteresse.Nome IS 'Nome';
COMMENT ON COLUMN rccInteresse.Telefone IS 'Telefone';
COMMENT ON COLUMN rccInteresse.Email IS 'E-mail';
COMMENT ON COLUMN rccInteresse.Cpf IS 'CPF';
COMMENT ON COLUMN rccInteresse.Observacao IS 'Observação';
CREATE TABLE rccRegistroEmail (RegistroDeEmailId  SERIAL NOT NULL, DataHora timestamp NOT NULL, Operador varchar(255) NOT NULL, Mensagem text NOT NULL, Assunto varchar(255) NOT NULL, Destinatarios varchar(255) NOT NULL, Anexos varchar(255), PRIMARY KEY (RegistroDeEmailId));
COMMENT ON TABLE rccRegistroEmail IS 'Registro de Email';
COMMENT ON COLUMN rccRegistroEmail.RegistroDeEmailId IS 'Código do registro';
COMMENT ON COLUMN rccRegistroEmail.DataHora IS 'Data/hora';
CREATE TABLE rccAssuntoDeContato (AssuntoDeContatoId  SERIAL NOT NULL, Descricao varchar(255) NOT NULL UNIQUE, PRIMARY KEY (AssuntoDeContatoId));
COMMENT ON TABLE rccAssuntoDeContato IS 'Assunto do contato';
COMMENT ON COLUMN rccAssuntoDeContato.AssuntoDeContatoId IS 'Código do assunto';
COMMENT ON COLUMN rccAssuntoDeContato.Descricao IS 'Descrição';
CREATE TABLE rccVinculoDeContato (VinculoDeContatoId  SERIAL NOT NULL, Descricao varchar(255) NOT NULL UNIQUE, PRIMARY KEY (VinculoDeContatoId));
COMMENT ON TABLE rccVinculoDeContato IS 'Vínculo do contato';
COMMENT ON COLUMN rccVinculoDeContato.VinculoDeContatoId IS 'Código do vínculo';
COMMENT ON COLUMN rccVinculoDeContato.Descricao IS 'Descrição';
ALTER TABLE rccRespostaOuvidoria ADD CONSTRAINT fk_resposta_origem FOREIGN KEY (OrigemDeContatoId) REFERENCES rccOrigemDeContato (OrigemDeContatoId);
ALTER TABLE rccMensagemOuvidoria ADD CONSTRAINT tipo2 FOREIGN KEY (TipoDeContatoId) REFERENCES rccTipoDeContato (TipoDeContatoId);
ALTER TABLE rccMensagemOuvidoria ADD CONSTRAINT origem2 FOREIGN KEY (OrigemDeContatoId) REFERENCES rccOrigemDeContato (OrigemDeContatoId);
ALTER TABLE rccMensagemOuvidoria ADD CONSTRAINT assunto2 FOREIGN KEY (AssuntoDeContatoId) REFERENCES rccAssuntoDeContato (AssuntoDeContatoId);
ALTER TABLE rccContato ADD CONSTRAINT contato2 FOREIGN KEY (TipoDeContatoId) REFERENCES rccTipoDeContato (TipoDeContatoId);
ALTER TABLE rccContato ADD CONSTRAINT assunto3 FOREIGN KEY (AssuntoDeContato) REFERENCES rccAssuntoDeContato (AssuntoDeContatoId);
ALTER TABLE rccContato ADD CONSTRAINT contato3 FOREIGN KEY (OrigemDeContatoId) REFERENCES rccOrigemDeContato (OrigemDeContatoId);
ALTER TABLE rccMensagemOuvidoria ADD CONSTRAINT fk_mensagem_vinculo FOREIGN KEY (VinculoDeContatoId) REFERENCES rccVinculoDeContato (VinculoDeContatoId);
ALTER TABLE rccInteresse ADD CONSTRAINT FKrccInteres404232 FOREIGN KEY (ContratoId) REFERENCES acpContrato (AcpContratoId);
ALTER TABLE rccContato ADD CONSTRAINT basPersonFk2 FOREIGN KEY (pessoa) REFERENCES basPerson (PersonId);
ALTER TABLE rccRespostaOuvidoria ADD CONSTRAINT rccMensagemOuvidoria2 FOREIGN KEY (MensagemOuvidoriaId) REFERENCES rccMensagemOuvidoria (MensagemOuvidoriaId);
ALTER TABLE rccInteresse ADD CONSTRAINT acpCurso2 FOREIGN KEY (CursoId) REFERENCES acpCurso (CursoId);
ALTER TABLE rccRespostaOuvidoria ADD CONSTRAINT basPerson2 FOREIGN KEY (respondente) REFERENCES basPerson (PersonId);
ALTER TABLE rccContato ADD CONSTRAINT rccContatoContato FOREIGN KEY (rccContatoContatoId) REFERENCES rccContato (ContatoId);





DROP VIEW IF EXISTS relclienteinadimplentes;
CREATE OR REPLACE VIEW relclienteinadimplentes AS
(SELECT a.personid, b.name, now()::date - c.datahoradocontato::date AS contato, a.balance, now()::date - a.maturitydate AS dias
   FROM ONLY basphysicalperson b
   JOIN ( SELECT min(aa.maturitydate) AS maturitydate, aa.personid, sum(aa.balance) AS balance
           FROM finreceivableinvoice aa
          WHERE aa.balance > 0::numeric 
          AND aa.maturitydate < now()::date
          AND aa.iscanceled = FALSE
          GROUP BY aa.personid) a ON a.personid = b.personid
   LEFT JOIN ( SELECT max(c.contatoid) AS contatoid, c.pessoa, c.datahoradocontato
      FROM rcccontato c
     GROUP BY c.pessoa, c.datahoradocontato) c ON c.pessoa = b.personid
  ORDER BY a.personid);

DROP VIEW IF EXISTS rccpopuppessoa;
CREATE OR REPLACE VIEW rccpopuppessoa AS 
    (SELECT a.personid, 
            a.name, 
            a.email,
            b.courseid,
            b.turnid,
            c.operationId,
            d.operationtypeid,
            c.value,
            c.invoiceId
  FROM ONLY basperson A
 INNER JOIN acdcontract B
    ON a.personid = b.personid
 INNER JOIN fininvoice Z
    ON a.personid = z.personid
 INNER JOIN finEntry C
    ON z.invoiceid = c.invoiceid
 INNER JOIN finOperation D
    ON D.operationId = C.operationId
    AND Z.maturitydate < now() 
    and Z.maturitydate > (SELECT MAX(begindate) 
                            FROM acdtimesheet )
    ORDER by invoiceId ASC
    LIMIT 5);
