----------------------------------------------------------------------
-- --
--
-- Table: ava_config
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_config" 
(
    "chave"    text,
    "valor"    text
);

ALTER TABLE "ava_config" ALTER COLUMN "chave" SET NOT NULL;

ALTER TABLE "ava_config" ALTER COLUMN "chave" SET NOT NULL;
ALTER TABLE "ava_config" ADD PRIMARY KEY ("chave");

----------------------------------------------------------------------
-- --
--
-- Table: ava_form_log
-- Purpose: 1: Entrou no formul
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_form_log" 
(
    "id_form_log"       serial,
    "ref_avaliador"     int,
    "ref_formulario"    int,
    "tipo_acao"         integer
);

COMMENT ON TABLE "ava_form_log" IS '1: Entrou no formul';

ALTER TABLE "ava_form_log" ALTER COLUMN "ref_avaliador" SET not null;
ALTER TABLE "ava_form_log" ALTER COLUMN "ref_formulario" SET NOT NULL;
ALTER TABLE "ava_form_log" ALTER COLUMN "tipo_acao" SET NOT NULL;

ALTER TABLE "ava_form_log" ALTER COLUMN "id_form_log" SET NOT NULL;
ALTER TABLE "ava_form_log" ADD PRIMARY KEY ("id_form_log");

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
    "id_avaliacao"    serial,
    "nome"            text,
    "dt_inicio"       date,
    "dt_fim"          date
);

ALTER TABLE "ava_avaliacao" ALTER COLUMN "id_avaliacao" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ALTER COLUMN "nome" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ALTER COLUMN "dt_inicio" SET NOT NULL;

ALTER TABLE "ava_avaliacao" ALTER COLUMN "id_avaliacao" SET NOT NULL;
ALTER TABLE "ava_avaliacao" ADD PRIMARY KEY ("id_avaliacao");

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
    "id_questoes"    serial,
    "descricao"      text,
    "tipo"           text,
    "opcoes"         text
);

ALTER TABLE "ava_questoes" ALTER COLUMN "id_questoes" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_questoes" ALTER COLUMN "tipo" SET NOT NULL;

ALTER TABLE "ava_questoes" ALTER COLUMN "id_questoes" SET NOT NULL;
ALTER TABLE "ava_questoes" ADD PRIMARY KEY ("id_questoes");

----------------------------------------------------------------------
-- --
--
-- Table: ava_servico
-- Purpose: 
--
-- --
----------------------------------------------------------------------

CREATE TABLE "ava_servico" 
(
    "id_servico"     serial,
    "descricao"      text,
    "localizacao"    text,
    "metodo"         text,
    "parametros"     text
);

ALTER TABLE "ava_servico" ALTER COLUMN "id_servico" SET NOT NULL;
ALTER TABLE "ava_servico" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_servico" ALTER COLUMN "localizacao" SET NOT NULL;
ALTER TABLE "ava_servico" ALTER COLUMN "metodo" SET NOT NULL;

ALTER TABLE "ava_servico" ALTER COLUMN "id_servico" SET NOT NULL;
ALTER TABLE "ava_servico" ADD PRIMARY KEY ("id_servico");

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
    "id_perfil"      serial,
    "descricao"      text,
    "ref_servico"    int,
    "tipo"           text
);

ALTER TABLE "ava_perfil" ALTER COLUMN "id_perfil" SET NOT NULL;
ALTER TABLE "ava_perfil" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_perfil" ALTER COLUMN "ref_servico" SET NOT NULL;
ALTER TABLE "ava_perfil" ALTER COLUMN "tipo" SET NOT NULL;

ALTER TABLE "ava_perfil" ALTER COLUMN "id_perfil" SET NOT NULL;
ALTER TABLE "ava_perfil" ADD PRIMARY KEY ("id_perfil");

ALTER TABLE "ava_perfil" ADD FOREIGN KEY ("ref_servico") REFERENCES "ava_servico"("id_servico");

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
    "id_granularidade"    serial,
    "descricao"           text,
    "ref_servico"         int,
    "tipo"                int,
    "opcoes"              text
);

ALTER TABLE "ava_granularidade" ALTER COLUMN "id_granularidade" SET NOT NULL;
ALTER TABLE "ava_granularidade" ALTER COLUMN "descricao" SET NOT NULL;
ALTER TABLE "ava_granularidade" ALTER COLUMN "ref_servico" SET NOT NULL;

ALTER TABLE "ava_granularidade" ALTER COLUMN "id_granularidade" SET NOT NULL;
ALTER TABLE "ava_granularidade" ADD PRIMARY KEY ("id_granularidade");

ALTER TABLE "ava_granularidade" ADD FOREIGN KEY ("ref_servico") REFERENCES "ava_servico"("id_servico");

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
    "id_formulario"    serial,
    "ref_avaliacao"    integer,
    "ref_perfil"       integer,
    "nome"             text,
    "ref_servico"      int
);

ALTER TABLE "ava_formulario" ALTER COLUMN "id_formulario" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "ref_avaliacao" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "ref_perfil" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "nome" SET NOT NULL;
ALTER TABLE "ava_formulario" ALTER COLUMN "ref_servico" SET NOT NULL;

ALTER TABLE "ava_formulario" ALTER COLUMN "id_formulario" SET NOT NULL;
ALTER TABLE "ava_formulario" ADD PRIMARY KEY ("id_formulario");

ALTER TABLE "ava_formulario" ADD FOREIGN KEY ("ref_avaliacao") REFERENCES "ava_avaliacao"("id_avaliacao");

ALTER TABLE "ava_formulario" ADD FOREIGN KEY ("ref_perfil") REFERENCES "ava_perfil"("id_perfil");

ALTER TABLE "ava_formulario" ADD FOREIGN KEY ("ref_servico") REFERENCES "ava_servico"("id_servico");

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
    "id_bloco"             serial,
    "nome"                 text,
    "ref_formulario"       integer,
    "ref_granularidade"    integer,
    "ordem"                int
);

ALTER TABLE "ava_bloco" ALTER COLUMN "id_bloco" SET NOT NULL;
ALTER TABLE "ava_bloco" ALTER COLUMN "nome" SET NOT NULL;
ALTER TABLE "ava_bloco" ALTER COLUMN "ref_formulario" SET NOT NULL;
ALTER TABLE "ava_bloco" ALTER COLUMN "ref_granularidade" SET NOT NULL;

ALTER TABLE "ava_bloco" ALTER COLUMN "id_bloco" SET NOT NULL;
ALTER TABLE "ava_bloco" ADD PRIMARY KEY ("id_bloco");

ALTER TABLE "ava_bloco" ADD FOREIGN KEY ("ref_granularidade") REFERENCES "ava_granularidade"("id_granularidade");

ALTER TABLE "ava_bloco" ADD FOREIGN KEY ("ref_formulario") REFERENCES "ava_formulario"("id_formulario");

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
    "id_bloco_questoes"    serial,
    "ref_bloco"            integer,
    "ref_questao"          integer,
    "ordem"                int,
    "obrigatorio"          boolean,
    "ativo"                boolean
);

ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "id_bloco_questoes" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "ref_bloco" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "ref_questao" SET NOT NULL;

ALTER TABLE "ava_bloco_questoes" ALTER COLUMN "id_bloco_questoes" SET NOT NULL;
ALTER TABLE "ava_bloco_questoes" ADD PRIMARY KEY ("id_bloco_questoes");

ALTER TABLE "ava_bloco_questoes" ADD FOREIGN KEY ("ref_questao") REFERENCES "ava_questoes"("id_questoes");

ALTER TABLE "ava_bloco_questoes" ADD FOREIGN KEY ("ref_bloco") REFERENCES "ava_bloco"("id_bloco");

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
    "id_respostas"             serial,
    "ref_bloco_questoes"    integer,
    "ref_avaliado"             integer,
    "ref_avaliador"            integer,
    "valor"                    text,
    "questao"                  varchar
);

ALTER TABLE "ava_respostas" ALTER COLUMN "id_respostas" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_bloco_questoes" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_avaliado" SET NOT NULL;
ALTER TABLE "ava_respostas" ALTER COLUMN "ref_avaliador" SET NOT NULL;

ALTER TABLE "ava_respostas" ALTER COLUMN "id_respostas" SET NOT NULL;
ALTER TABLE "ava_respostas" ADD PRIMARY KEY ("id_respostas");

ALTER TABLE "ava_respostas" ADD FOREIGN KEY ("ref_bloco_questoes") REFERENCES "ava_bloco_questoes"("id_bloco_questoes");

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
    "id_atributos"    serial,
    "ref_resposta"    integer,
    "chave"           integer,
    "valor"           text
);

ALTER TABLE "ava_atributos" ALTER COLUMN "id_atributos" SET NOT NULL;
ALTER TABLE "ava_atributos" ALTER COLUMN "ref_resposta" SET NOT NULL;
ALTER TABLE "ava_atributos" ALTER COLUMN "chave" SET NOT NULL;

ALTER TABLE "ava_atributos" ALTER COLUMN "id_atributos" SET NOT NULL;
ALTER TABLE "ava_atributos" ADD PRIMARY KEY ("id_atributos");

ALTER TABLE "ava_atributos" ADD FOREIGN KEY ("ref_resposta") REFERENCES "ava_respostas"("id_respostas");

