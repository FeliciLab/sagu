----------------------------------------------------------------------
-- --
--
-- Table: ava_avaliacao
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_avaliacao" 
(
    "id"           serial,
    "nome"         text,
    "dt_inicio"    date,
    "dt_fim"       date
);

ALTER TABLE "ava_avaliacao" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ALTER COLUMN "nome" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ALTER COLUMN "dt_inicio" SET NOT NULL;

ALTER TABLE "ava_avaliacao" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ADD PRIMARY KEY ("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_perfil
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_perfil" 
(
    "id"           serial,
    "descricao"    text,
    "servico"      text
);

ALTER TABLE "ava_perfil" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_perfil" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_perfil" ALTER COLUMN "servico" SET NOT NULL;

ALTER TABLE "ava_perfil" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_perfil" ADD PRIMARY KEY ("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_granularidade
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_granularidade" 
(
    "id"           serial,
    "descricao"    text,
    "metodo"       text
);

ALTER TABLE "ava_granularidade" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_granularidade" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_granularidade" ALTER COLUMN "metodo" SET NOT NULL;

ALTER TABLE "ava_granularidade" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_granularidade" ADD PRIMARY KEY ("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_questoes
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_questoes" 
(
    "id"             serial,
    "descricao"      text,
    "tipo"           text,
    "opcoes"         text,
    "obrigatorio"    boolean,
    "ordem"          integer,
    "ativo"          boolean
);

ALTER TABLE "ava_questoes" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "tipo" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "obrigatorio" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "obrigatorio" SET DEFAULT FALSE ;
ALTER TABLE "ava_questoes" ALTER COLUMN "ordem" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "ativo" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "ativo" SET DEFAULT TRUE ;

ALTER TABLE "ava_questoes" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_questoes" ADD PRIMARY KEY ("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_usuario
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_usuario" 
(
    "id"      serial,
    "nome"    text
);

ALTER TABLE "ava_usuario" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_usuario" ALTER COLUMN "nome" SET NOT NULL;

ALTER TABLE "ava_usuario" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_usuario" ADD PRIMARY KEY ("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_formulario
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_formulario" 
(
    "id"               serial,
    "ref_avaliacao"    integer,
    "ref_perfil"       integer,
    "nome"             text,
    "regra"            text
);

ALTER TABLE "ava_formulario" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "ref_avaliacao" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "ref_perfil" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "nome" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "regra" SET NOT NULL;

ALTER TABLE "ava_formulario" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_formulario" ADD PRIMARY KEY ("id");

ALTER TABLE "ava_formulario" ADD FOREIGN KEY ("id") REFERENCES "ava_avaliacao"("id");

ALTER TABLE "ava_formulario" ADD FOREIGN KEY ("ref_perfil") REFERENCES "ava_perfil"("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_bloco
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_bloco" 
(
    "id"                   serial,
    "ref_formulario"       integer,
    "ref_granularidade"    integer
);

ALTER TABLE "ava_bloco" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_bloco" ALTER COLUMN "ref_formulario" SET NOT NULL;
ALTER TABLE "ava_bloco" ALTER COLUMN "ref_granularidade" SET NOT NULL;

ALTER TABLE "ava_bloco" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_bloco" ADD PRIMARY KEY ("id");

ALTER TABLE "ava_bloco" ADD FOREIGN KEY ("ref_granularidade") REFERENCES "ava_granularidade"("id");

ALTER TABLE "ava_bloco" ADD FOREIGN KEY ("ref_formulario") REFERENCES "ava_formulario"("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_bloco_questoes
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_bloco_questoes" 
(
    "id"             serial,
    "ref_bloco"      integer,
    "ref_questao"    integer
);

ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "ref_bloco" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "ref_questao" SET NOT NULL;

ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ADD PRIMARY KEY ("id");

ALTER TABLE "ava_bloco_questoes" ADD FOREIGN KEY ("ref_questao") REFERENCES "ava_questoes"("id");

ALTER TABLE "ava_bloco_questoes" ADD FOREIGN KEY ("ref_bloco") REFERENCES "ava_bloco"("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_respostas
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_respostas" 
(
    "id"                       serial,
    "ref_bloco_questoes"    integer,
    "ref_avaliado"             integer,
    "ref_avaliador"            integer,
    "valor"                    text
);

ALTER TABLE "ava_respostas" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_bloco_questoes" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_avaliado" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_avaliador" SET NOT NULL;

ALTER TABLE "ava_respostas" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_respostas" ADD PRIMARY KEY ("id");

ALTER TABLE "ava_respostas" ADD FOREIGN KEY ("ref_avaliador") REFERENCES "ava_usuario"("id");

ALTER TABLE "ava_respostas" ADD FOREIGN KEY ("ref_avaliado") REFERENCES "ava_usuario"("id");

ALTER TABLE "ava_respostas" ADD FOREIGN KEY ("ref_bloco_questoes") REFERENCES "ava_bloco_questoes"("id");

----------------------------------------------------------------------
-- --
--
-- Table: ava_atributos
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_atributos" 
(
    "id"              serial,
    "ref_resposta"    integer,
    "chave"           integer,
    "valor"           text
);

ALTER TABLE "ava_atributos" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_atributos" ALTER COLUMN "ref_resposta" SET NOT NULL;
ALTER TABLE "ava_atributos" ALTER COLUMN "chave" SET NOT NULL;

ALTER TABLE "ava_atributos" ALTER COLUMN "id" SET NOT NULL;
ALTER TABLE "ava_atributos" ADD PRIMARY KEY ("id");

ALTER TABLE "ava_atributos" ADD FOREIGN KEY ("ref_resposta") REFERENCES "ava_respostas"("id");

