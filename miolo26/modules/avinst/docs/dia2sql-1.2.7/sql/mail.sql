----------------------------------------------------------------------
-- --
--
-- Table: ava_mail
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_mail" 
(
    "id_mail"        serial,
    "ref_avaliacao"  integer,
    "ref_perfil"     integer,
    "ref_formulario" integer,
    "datahora"       timestamp,
    "assunto"        text,
    "conteudo"       text,
    "tipo_envio"     integer,
    "grupo_envio"    integer,
    "processo"       integer,
    "cco"            text
);

ALTER TABLE "ava_mail" ALTER COLUMN "id_mail" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "ref_avaliacao" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "ref_perfil" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "datahora" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "assunto" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "conteudo" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "tipo_envio" SET NOT NULL;
ALTER TABLE "ava_mail" ALTER COLUMN "grupo_envio" SET NOT NULL;

ALTER TABLE "ava_mail" ADD PRIMARY KEY ("id_mail");

ALTER TABLE "ava_mail" ADD FOREIGN KEY ("ref_avaliacao") REFERENCES "ava_avaliacao"("id_avaliacao");
ALTER TABLE "ava_mail" ADD FOREIGN KEY ("ref_perfil") REFERENCES "ava_perfil"("id_perfil");
ALTER TABLE "ava_mail" ADD FOREIGN KEY ("ref_formulario") REFERENCES "ava_formulario"("id_formulario");

----------------------------------------------------------------------
-- --
--
-- Table: ava_mail_log
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_mail_log" 
(
    "id_mail_log"        serial,
    "ref_mail"           integer,
    "ref_destinatario"   integer,
    "destinatario"       text,
    "envio"              boolean,
    "datahora"           timestamp
);

ALTER TABLE "ava_mail_log" ALTER COLUMN "id_mail_log" SET NOT NULL;
ALTER TABLE "ava_mail_log" ALTER COLUMN "ref_mail" SET NOT NULL;
ALTER TABLE "ava_mail_log" ALTER COLUMN "ref_destinatario" SET NOT NULL;
ALTER TABLE "ava_mail_log" ALTER COLUMN "destinatario" SET NOT NULL;

ALTER TABLE "ava_mail_log" ADD PRIMARY KEY ("id_mail_log");

ALTER TABLE "ava_mail_log" ADD FOREIGN KEY ("ref_mail") REFERENCES "ava_mail"("id_mail");



