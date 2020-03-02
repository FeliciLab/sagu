----------------------------------------------------------------------
-- --
--
-- Table: ava_widget
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_widget" 
(
    "id_widget"        varchar,
    "versao"           varchar,
    "nome"             varchar,
    "opcoes_padrao"    text
);

ALTER TABLE "ava_widget" ALTER COLUMN "id_widget" SET NOT NULL;
ALTER TABLE "ava_widget" ALTER COLUMN "versao" SET NOT NULL;
ALTER TABLE "ava_widget" ALTER COLUMN "nome" SET NOT NULL;

ALTER TABLE "ava_widget" ADD PRIMARY KEY ("id_widget");

----------------------------------------------------------------------
-- --
--
-- Table: ava_grupo
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_grupo" 
(
    "id_grupo"        serial,
    "identificador"   varchar
);

ALTER TABLE "ava_grupo" ALTER COLUMN "id_grupo" SET NOT NULL;
ALTER TABLE "ava_grupo" ALTER COLUMN "identificador" SET NOT NULL;

ALTER TABLE "ava_grupo" ADD PRIMARY KEY ("id_grupo");

----------------------------------------------------------------------
-- --
--
-- Table: ava_perfil_widget
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_perfil_widget" 
(
    "id_perfil_widget"  serial,
    "ref_perfil"        integer,
    "ref_widget"        varchar
);

ALTER TABLE "ava_perfil_widget" ALTER COLUMN "id_perfil_widget" SET NOT NULL;
ALTER TABLE "ava_perfil_widget" ALTER COLUMN "ref_widget" SET NOT NULL;
ALTER TABLE "ava_perfil_widget" ALTER COLUMN "ref_perfil" SET NOT NULL;

ALTER TABLE "ava_perfil_widget" ADD PRIMARY KEY ("id_perfil_widget");

ALTER TABLE "ava_perfil_widget" ADD FOREIGN KEY ("ref_perfil") REFERENCES "ava_perfil"("id_perfil");
ALTER TABLE "ava_perfil_widget" ADD FOREIGN KEY ("ref_widget") REFERENCES "ava_widget"("id_widget");

----------------------------------------------------------------------
-- --
--
-- Table: ava_avaliacao_widget
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_avaliacao_widget" 
(
    "id_avaliacao_widget"  serial,
    "ref_avaliacao"        integer,
    "ref_widget"           varchar,
    "opcoes"               text
);

ALTER TABLE "ava_avaliacao_widget" ALTER COLUMN "id_avaliacao_widget" SET NOT NULL;
ALTER TABLE "ava_avaliacao_widget" ALTER COLUMN "ref_avaliacao" SET NOT NULL;
ALTER TABLE "ava_avaliacao_widget" ALTER COLUMN "ref_widget" SET NOT NULL;

ALTER TABLE "ava_avaliacao_widget" ADD PRIMARY KEY ("id_avaliacao_widget");

ALTER TABLE "ava_avaliacao_widget" ADD FOREIGN KEY ("ref_avaliacao") REFERENCES "ava_avaliacao"("id_avaliacao");
ALTER TABLE "ava_avaliacao_widget" ADD FOREIGN KEY ("ref_widget") REFERENCES "ava_widget"("id_widget");

