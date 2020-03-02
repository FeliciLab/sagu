--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'LATIN1';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: tablefunc; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS tablefunc WITH SCHEMA public;


--
-- Name: EXTENSION tablefunc; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION tablefunc IS 'functions that manipulate whole tables, including crosstab';


SET search_path = public, pg_catalog;

--
-- Name: dblink_pkey_results; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE dblink_pkey_results AS (
	"position" integer,
	colname text
);


ALTER TYPE public.dblink_pkey_results OWNER TO postgres;

--
-- Name: dblink(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink(text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text) OWNER TO postgres;

--
-- Name: dblink(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink(text, text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, text) OWNER TO postgres;

--
-- Name: dblink(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink(text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, boolean) OWNER TO postgres;

--
-- Name: dblink(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink(text, text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_build_sql_delete(text, int2vector, integer, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_build_sql_delete(text, int2vector, integer, text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_delete';


ALTER FUNCTION public.dblink_build_sql_delete(text, int2vector, integer, text[]) OWNER TO postgres;

--
-- Name: dblink_build_sql_insert(text, int2vector, integer, text[], text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_build_sql_insert(text, int2vector, integer, text[], text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_insert';


ALTER FUNCTION public.dblink_build_sql_insert(text, int2vector, integer, text[], text[]) OWNER TO postgres;

--
-- Name: dblink_build_sql_update(text, int2vector, integer, text[], text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_build_sql_update(text, int2vector, integer, text[], text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_update';


ALTER FUNCTION public.dblink_build_sql_update(text, int2vector, integer, text[], text[]) OWNER TO postgres;

--
-- Name: dblink_cancel_query(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_cancel_query(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_cancel_query';


ALTER FUNCTION public.dblink_cancel_query(text) OWNER TO postgres;

--
-- Name: dblink_close(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_close(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text) OWNER TO postgres;

--
-- Name: dblink_close(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_close(text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, boolean) OWNER TO postgres;

--
-- Name: dblink_close(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_close(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, text) OWNER TO postgres;

--
-- Name: dblink_close(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_close(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_connect(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_connect(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect(text) OWNER TO postgres;

--
-- Name: dblink_connect(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_connect(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect(text, text) OWNER TO postgres;

--
-- Name: dblink_connect_u(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_connect_u(text) RETURNS text
    LANGUAGE c STRICT SECURITY DEFINER
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect_u(text) OWNER TO postgres;

--
-- Name: dblink_connect_u(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_connect_u(text, text) RETURNS text
    LANGUAGE c STRICT SECURITY DEFINER
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect_u(text, text) OWNER TO postgres;

--
-- Name: dblink_current_query(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_current_query() RETURNS text
    LANGUAGE c
    AS '$libdir/dblink', 'dblink_current_query';


ALTER FUNCTION public.dblink_current_query() OWNER TO postgres;

--
-- Name: dblink_disconnect(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_disconnect() RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_disconnect';


ALTER FUNCTION public.dblink_disconnect() OWNER TO postgres;

--
-- Name: dblink_disconnect(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_disconnect(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_disconnect';


ALTER FUNCTION public.dblink_disconnect(text) OWNER TO postgres;

--
-- Name: dblink_error_message(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_error_message(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_error_message';


ALTER FUNCTION public.dblink_error_message(text) OWNER TO postgres;

--
-- Name: dblink_exec(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_exec(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text) OWNER TO postgres;

--
-- Name: dblink_exec(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_exec(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, text) OWNER TO postgres;

--
-- Name: dblink_exec(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_exec(text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, boolean) OWNER TO postgres;

--
-- Name: dblink_exec(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_exec(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_fetch(text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_fetch(text, integer) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, integer) OWNER TO postgres;

--
-- Name: dblink_fetch(text, integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_fetch(text, integer, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, integer, boolean) OWNER TO postgres;

--
-- Name: dblink_fetch(text, text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_fetch(text, text, integer) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, text, integer) OWNER TO postgres;

--
-- Name: dblink_fetch(text, text, integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_fetch(text, text, integer, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, text, integer, boolean) OWNER TO postgres;

--
-- Name: dblink_get_connections(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_get_connections() RETURNS text[]
    LANGUAGE c
    AS '$libdir/dblink', 'dblink_get_connections';


ALTER FUNCTION public.dblink_get_connections() OWNER TO postgres;

--
-- Name: dblink_get_pkey(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_get_pkey(text) RETURNS SETOF dblink_pkey_results
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_pkey';


ALTER FUNCTION public.dblink_get_pkey(text) OWNER TO postgres;

--
-- Name: dblink_get_result(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_get_result(text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_result';


ALTER FUNCTION public.dblink_get_result(text) OWNER TO postgres;

--
-- Name: dblink_get_result(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_get_result(text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_result';


ALTER FUNCTION public.dblink_get_result(text, boolean) OWNER TO postgres;

--
-- Name: dblink_is_busy(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_is_busy(text) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_is_busy';


ALTER FUNCTION public.dblink_is_busy(text) OWNER TO postgres;

--
-- Name: dblink_open(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_open(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text) OWNER TO postgres;

--
-- Name: dblink_open(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_open(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_open(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_open(text, text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, text) OWNER TO postgres;

--
-- Name: dblink_open(text, text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_open(text, text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_send_query(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION dblink_send_query(text, text) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_send_query';


ALTER FUNCTION public.dblink_send_query(text, text) OWNER TO postgres;

--
-- Name: get_atributo(integer, text); Type: FUNCTION; Schema: public; Owner: avinst
--

CREATE FUNCTION get_atributo(integer, text) RETURNS text
    LANGUAGE sql
    AS $_$ SELECT valor FROM ava_atributos WHERE ref_resposta = $1 AND chave = $2 $_$;


ALTER FUNCTION public.get_atributo(integer, text) OWNER TO avinst;

--
-- Name: get_atributo_totalizador(integer, text); Type: FUNCTION; Schema: public; Owner: avinst
--

CREATE FUNCTION get_atributo_totalizador(integer, text) RETURNS text
    LANGUAGE sql
    AS $_$ SELECT valor FROM ava_totalizadores_atributos WHERE ref_totalizador = $1 AND chave = $2 $_$;


ALTER FUNCTION public.get_atributo_totalizador(integer, text) OWNER TO avinst;

--
-- Name: obteratributototalizador(character varying, integer); Type: FUNCTION; Schema: public; Owner: avinst
--

CREATE FUNCTION obteratributototalizador(character varying, integer) RETURNS character varying
    LANGUAGE sql
    AS $_$SELECT valor from ava_totalizadores_atributos where chave=$1 and ref_totalizador=$2 $_$;


ALTER FUNCTION public.obteratributototalizador(character varying, integer) OWNER TO avinst;

--
-- Name: plpgsql_call_handler(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION plpgsql_call_handler() RETURNS language_handler
    LANGUAGE c
    AS '$libdir/plpgsql', 'plpgsql_call_handler';


ALTER FUNCTION public.plpgsql_call_handler() OWNER TO postgres;

--
-- Name: plpgsql_validator(oid); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION plpgsql_validator(oid) RETURNS void
    LANGUAGE c
    AS '$libdir/plpgsql', 'plpgsql_validator';


ALTER FUNCTION public.plpgsql_validator(oid) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: ava_atributos; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_atributos (
    id_atributos integer NOT NULL,
    ref_resposta integer NOT NULL,
    chave character varying NOT NULL,
    valor text
);


ALTER TABLE public.ava_atributos OWNER TO avinst;

--
-- Name: ava_atributos_id_atributos_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_atributos_id_atributos_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_atributos_id_atributos_seq OWNER TO avinst;

--
-- Name: ava_atributos_id_atributos_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_atributos_id_atributos_seq OWNED BY ava_atributos.id_atributos;


--
-- Name: ava_avaliacao; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_avaliacao (
    id_avaliacao integer NOT NULL,
    nome text NOT NULL,
    dt_inicio date NOT NULL,
    dt_fim date,
    tipo_processo integer DEFAULT 1 NOT NULL,
    descritivo text
);


ALTER TABLE public.ava_avaliacao OWNER TO avinst;

--
-- Name: ava_avaliacao_id_avaliacao_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_avaliacao_id_avaliacao_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_avaliacao_id_avaliacao_seq OWNER TO avinst;

--
-- Name: ava_avaliacao_id_avaliacao_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_avaliacao_id_avaliacao_seq OWNED BY ava_avaliacao.id_avaliacao;


--
-- Name: ava_avaliacao_perfil_widget; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_avaliacao_perfil_widget (
    id_avaliacao_perfil_widget integer NOT NULL,
    ref_avaliacao integer NOT NULL,
    ref_perfil_widget integer,
    altura character varying,
    largura character varying,
    linha integer,
    coluna integer
);


ALTER TABLE public.ava_avaliacao_perfil_widget OWNER TO avinst;

--
-- Name: ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq OWNER TO avinst;

--
-- Name: ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq OWNED BY ava_avaliacao_perfil_widget.id_avaliacao_perfil_widget;


--
-- Name: ava_avaliacao_widget; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_avaliacao_widget (
    id_avaliacao_widget integer NOT NULL,
    ref_avaliacao integer NOT NULL,
    ref_widget character varying NOT NULL,
    opcoes text
);


ALTER TABLE public.ava_avaliacao_widget OWNER TO avinst;

--
-- Name: ava_avaliacao_widget_id_avaliacao_widget_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_avaliacao_widget_id_avaliacao_widget_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_avaliacao_widget_id_avaliacao_widget_seq OWNER TO avinst;

--
-- Name: ava_avaliacao_widget_id_avaliacao_widget_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_avaliacao_widget_id_avaliacao_widget_seq OWNED BY ava_avaliacao_widget.id_avaliacao_widget;


--
-- Name: ava_bloco; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_bloco (
    id_bloco integer NOT NULL,
    nome text NOT NULL,
    ref_formulario integer NOT NULL,
    ref_granularidade integer NOT NULL,
    ordem integer
);


ALTER TABLE public.ava_bloco OWNER TO avinst;

--
-- Name: ava_bloco_id_bloco_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_bloco_id_bloco_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_bloco_id_bloco_seq OWNER TO avinst;

--
-- Name: ava_bloco_id_bloco_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_bloco_id_bloco_seq OWNED BY ava_bloco.id_bloco;


--
-- Name: ava_bloco_questoes; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_bloco_questoes (
    id_bloco_questoes integer NOT NULL,
    ref_bloco integer NOT NULL,
    ref_questao integer NOT NULL,
    ordem integer,
    obrigatorio boolean,
    ativo boolean
);


ALTER TABLE public.ava_bloco_questoes OWNER TO avinst;

--
-- Name: ava_bloco_questoes_id_bloco_questoes_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_bloco_questoes_id_bloco_questoes_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_bloco_questoes_id_bloco_questoes_seq OWNER TO avinst;

--
-- Name: ava_bloco_questoes_id_bloco_questoes_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_bloco_questoes_id_bloco_questoes_seq OWNED BY ava_bloco_questoes.id_bloco_questoes;


--
-- Name: ava_config; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_config (
    chave text NOT NULL,
    valor text
);


ALTER TABLE public.ava_config OWNER TO avinst;

--
-- Name: ava_totalizadores_atributos; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_totalizadores_atributos (
    id_totalizador_atributo integer NOT NULL,
    ref_totalizador integer NOT NULL,
    chave text NOT NULL,
    valor text NOT NULL
);


ALTER TABLE public.ava_totalizadores_atributos OWNER TO avinst;

--
-- Name: ava_estatisticas_atributos_id_estatistica_atributo_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_estatisticas_atributos_id_estatistica_atributo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_estatisticas_atributos_id_estatistica_atributo_seq OWNER TO avinst;

--
-- Name: ava_estatisticas_atributos_id_estatistica_atributo_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_estatisticas_atributos_id_estatistica_atributo_seq OWNED BY ava_totalizadores_atributos.id_totalizador_atributo;


--
-- Name: ava_totalizadores; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_totalizadores (
    id_totalizador integer NOT NULL,
    ref_avaliacao integer NOT NULL,
    ref_granularidade integer NOT NULL,
    codigo text NOT NULL,
    descricao text,
    count integer NOT NULL
);


ALTER TABLE public.ava_totalizadores OWNER TO avinst;

--
-- Name: ava_estatisticas_id_estatistica_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_estatisticas_id_estatistica_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_estatisticas_id_estatistica_seq OWNER TO avinst;

--
-- Name: ava_estatisticas_id_estatistica_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_estatisticas_id_estatistica_seq OWNED BY ava_totalizadores.id_totalizador;


--
-- Name: ava_form_log_id_form_log_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_form_log_id_form_log_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_form_log_id_form_log_seq OWNER TO avinst;

--
-- Name: ava_form_log; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_form_log (
    id_form_log integer DEFAULT nextval('ava_form_log_id_form_log_seq'::regclass) NOT NULL,
    ref_avaliador integer NOT NULL,
    ref_formulario integer NOT NULL,
    tipo_acao integer NOT NULL,
    data timestamp without time zone DEFAULT now(),
    sessao character varying,
    tentativa character varying
);


ALTER TABLE public.ava_form_log OWNER TO avinst;

--
-- Name: ava_formulario; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_formulario (
    id_formulario integer NOT NULL,
    ref_avaliacao integer NOT NULL,
    ref_perfil integer NOT NULL,
    nome text NOT NULL,
    ref_servico integer NOT NULL,
    descritivo text
);


ALTER TABLE public.ava_formulario OWNER TO avinst;

--
-- Name: ava_formulario_id_formulario_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_formulario_id_formulario_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_formulario_id_formulario_seq OWNER TO avinst;

--
-- Name: ava_formulario_id_formulario_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_formulario_id_formulario_seq OWNED BY ava_formulario.id_formulario;


--
-- Name: ava_granularidade; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_granularidade (
    id_granularidade integer NOT NULL,
    descricao text NOT NULL,
    ref_servico integer NOT NULL,
    tipo integer,
    opcoes text
);


ALTER TABLE public.ava_granularidade OWNER TO avinst;

--
-- Name: ava_granularidade_id_granularidade_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_granularidade_id_granularidade_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_granularidade_id_granularidade_seq OWNER TO avinst;

--
-- Name: ava_granularidade_id_granularidade_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_granularidade_id_granularidade_seq OWNED BY ava_granularidade.id_granularidade;


--
-- Name: ava_mail; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_mail (
    id_mail integer NOT NULL,
    ref_avaliacao integer NOT NULL,
    ref_perfil integer NOT NULL,
    ref_formulario integer,
    datahora timestamp without time zone NOT NULL,
    assunto text NOT NULL,
    conteudo text NOT NULL,
    tipo_envio integer NOT NULL,
    grupo_envio integer NOT NULL,
    processo integer,
    cco text
);


ALTER TABLE public.ava_mail OWNER TO avinst;

--
-- Name: ava_mail_id_mail_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_mail_id_mail_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_mail_id_mail_seq OWNER TO avinst;

--
-- Name: ava_mail_id_mail_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_mail_id_mail_seq OWNED BY ava_mail.id_mail;


--
-- Name: ava_mail_log; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_mail_log (
    id_mail_log integer NOT NULL,
    ref_mail integer NOT NULL,
    ref_destinatario integer NOT NULL,
    destinatario text NOT NULL,
    envio boolean,
    datahora timestamp without time zone
);


ALTER TABLE public.ava_mail_log OWNER TO avinst;

--
-- Name: ava_mail_log_id_mail_log_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_mail_log_id_mail_log_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_mail_log_id_mail_log_seq OWNER TO avinst;

--
-- Name: ava_mail_log_id_mail_log_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_mail_log_id_mail_log_seq OWNED BY ava_mail_log.id_mail_log;


--
-- Name: ava_matriculados; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_matriculados (
    ref_campus integer NOT NULL,
    nome_campus text,
    ref_curso integer NOT NULL,
    nome_curso text,
    ref_projeto_pedagogico integer NOT NULL,
    nome_projeto_pedagogico text,
    ref_curriculo integer NOT NULL,
    nome_curriculo text,
    ref_disciplina integer NOT NULL,
    nome_disciplina text,
    dia integer NOT NULL,
    turno character(1),
    ref_professor integer NOT NULL,
    nome_professor text,
    matriculados integer NOT NULL
);


ALTER TABLE public.ava_matriculados OWNER TO avinst;

--
-- Name: ava_perfil; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_perfil (
    id_perfil integer NOT NULL,
    descricao text NOT NULL,
    tipo text NOT NULL,
    avaliavel boolean NOT NULL,
    posicao integer
);


ALTER TABLE public.ava_perfil OWNER TO avinst;

--
-- Name: ava_perfil_id_perfil_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_perfil_id_perfil_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_perfil_id_perfil_seq OWNER TO avinst;

--
-- Name: ava_perfil_id_perfil_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_perfil_id_perfil_seq OWNED BY ava_perfil.id_perfil;


--
-- Name: ava_perfil_widget; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_perfil_widget (
    id_perfil_widget integer NOT NULL,
    ref_perfil integer NOT NULL,
    ref_widget character varying NOT NULL
);


ALTER TABLE public.ava_perfil_widget OWNER TO avinst;

--
-- Name: ava_perfil_widget_id_perfil_widget_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_perfil_widget_id_perfil_widget_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_perfil_widget_id_perfil_widget_seq OWNER TO avinst;

--
-- Name: ava_perfil_widget_id_perfil_widget_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_perfil_widget_id_perfil_widget_seq OWNED BY ava_perfil_widget.id_perfil_widget;


--
-- Name: ava_questoes; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_questoes (
    id_questoes integer NOT NULL,
    descricao text NOT NULL,
    opcoes text,
    tipo integer
);


ALTER TABLE public.ava_questoes OWNER TO avinst;

--
-- Name: ava_questoes_id_questoes_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_questoes_id_questoes_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_questoes_id_questoes_seq OWNER TO avinst;

--
-- Name: ava_questoes_id_questoes_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_questoes_id_questoes_seq OWNED BY ava_questoes.id_questoes;


--
-- Name: ava_respostas; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_respostas (
    id_respostas integer NOT NULL,
    ref_bloco_questoes integer NOT NULL,
    ref_avaliado integer,
    ref_avaliador integer NOT NULL,
    valor text,
    questao character varying
);


ALTER TABLE public.ava_respostas OWNER TO avinst;

--
-- Name: ava_respostas_id_respostas_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_respostas_id_respostas_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_respostas_id_respostas_seq OWNER TO avinst;

--
-- Name: ava_respostas_id_respostas_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_respostas_id_respostas_seq OWNED BY ava_respostas.id_respostas;


--
-- Name: ava_servico; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_servico (
    id_servico integer NOT NULL,
    descricao text NOT NULL,
    localizacao text NOT NULL,
    metodo text NOT NULL,
    parametros text,
    atributos text
);


ALTER TABLE public.ava_servico OWNER TO avinst;

--
-- Name: ava_servico_id_servico_seq; Type: SEQUENCE; Schema: public; Owner: avinst
--

CREATE SEQUENCE ava_servico_id_servico_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ava_servico_id_servico_seq OWNER TO avinst;

--
-- Name: ava_servico_id_servico_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: avinst
--

ALTER SEQUENCE ava_servico_id_servico_seq OWNED BY ava_servico.id_servico;


--
-- Name: ava_view_respostas_alunos_cursos; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW ava_view_respostas_alunos_cursos AS
    SELECT atributos.id_resposta, atributos.centro_curso, atributos.coordenador_curso, atributos.curso, atributos.fl_contem_lfe, atributos.ref_campus, atributos.ref_centro_curso, atributos.ref_coordenador, atributos.ref_coordenador_curriculo, atributos.ref_coordenador_curso, atributos.ref_curriculo, atributos.ref_curso, atributos.ref_projeto_pedagogico, ava_bloco_questoes.ordem AS questao_ordem, ava_bloco.ordem AS bloco_ordem, ava_formulario.ref_perfil, ava_formulario.nome, ava_questoes.descricao, ava_questoes.tipo, ava_formulario.ref_avaliacao, ava_formulario.id_formulario, ava_respostas.ref_bloco_questoes, ava_bloco.ref_granularidade, ava_respostas.ref_avaliador, ava_respostas.valor FROM (((((crosstab('SELECT id_respostas, chave, ava_atributos.valor 
                       FROM ava_atributos
                 INNER JOIN ava_respostas ON (ava_atributos.ref_resposta=ava_respostas.id_respostas) 
                 INNER JOIN ava_bloco_questoes ON (ava_respostas.ref_bloco_questoes=ava_bloco_questoes.id_bloco_questoes) 
                 INNER JOIN ava_bloco ON (ava_bloco_questoes.ref_bloco=ava_bloco.id_bloco) 
                      WHERE ava_bloco.ref_granularidade=2 
                   ORDER BY 1,2'::text, 'SELECT DISTINCT chave 
                      FROM ava_atributos 
                INNER JOIN ava_respostas ON (ava_atributos.ref_resposta=ava_respostas.id_respostas) 
                INNER JOIN ava_bloco_questoes ON (ava_respostas.ref_bloco_questoes=ava_bloco_questoes.id_bloco_questoes) 
                INNER JOIN ava_bloco ON (ava_bloco_questoes.ref_bloco=ava_bloco.id_bloco) 
                     WHERE ava_bloco.ref_granularidade=2
                  ORDER BY 1 '::text) atributos(id_resposta integer, centro_curso character varying, coordenador_curso character varying, curso character varying, fl_contem_lfe character varying, ref_campus character varying, ref_centro_curso character varying, ref_coordenador character varying, ref_coordenador_curriculo character varying, ref_coordenador_curso character varying, ref_curriculo character varying, ref_curso character varying, ref_projeto_pedagogico character varying) JOIN ava_respostas ON ((atributos.id_resposta = ava_respostas.id_respostas))) JOIN ava_bloco_questoes ON ((ava_bloco_questoes.id_bloco_questoes = ava_respostas.ref_bloco_questoes))) JOIN ava_questoes ON ((ava_bloco_questoes.ref_questao = ava_questoes.id_questoes))) JOIN ava_bloco ON ((ava_bloco_questoes.ref_bloco = ava_bloco.id_bloco))) JOIN ava_formulario ON ((ava_bloco.ref_formulario = ava_formulario.id_formulario)));


ALTER TABLE public.ava_view_respostas_alunos_cursos OWNER TO avinst;

--
-- Name: ava_view_respostas_alunos_disciplinas; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW ava_view_respostas_alunos_disciplinas AS
    SELECT atributos.id_resposta, atributos.ref_tipo_curriculo, atributos.curriculo, atributos.tipo_formacao, atributos.ref_tipo_formacao, atributos.ref_centro_aluno, atributos.ref_curso, atributos.ref_projeto_pedagogico, atributos.ref_coordenador_curso, atributos.fl_contem_lfe, atributos.centro_disciplina, atributos.projeto_pedagogico, atributos.ref_curriculo, atributos.ref_turma, atributos.coordenador_curso, atributos.centro_professor, atributos.ref_coordenador_curriculo, atributos.tipo_curriculo, atributos.ref_campus, atributos.coordenador_curriculo, atributos.ref_professor, atributos.curso, atributos.ref_centro_disciplina, atributos.ref_centro_professor, atributos.ref_disciplina, ava_bloco_questoes.ordem AS questao_ordem, ava_bloco.ordem AS bloco_ordem, ava_formulario.ref_perfil, ava_formulario.nome, ava_questoes.descricao, ava_questoes.tipo, ava_respostas.ref_bloco_questoes, ava_respostas.ref_avaliador, ava_respostas.valor FROM (((((crosstab('SELECT id_respostas, chave, ava_atributos.valor 
                       FROM ava_atributos
                 INNER JOIN ava_respostas ON (ava_atributos.ref_resposta=ava_respostas.id_respostas) 
                 INNER JOIN ava_bloco_questoes ON (ava_respostas.ref_bloco_questoes=ava_bloco_questoes.id_bloco_questoes) 
                 INNER JOIN ava_bloco ON (ava_bloco_questoes.ref_bloco=ava_bloco.id_bloco) 
                      WHERE ava_bloco.ref_granularidade=1'::text, 'SELECT DISTINCT chave 
                      FROM ava_atributos 
                INNER JOIN ava_respostas ON (ava_atributos.ref_resposta=ava_respostas.id_respostas) 
                INNER JOIN ava_bloco_questoes ON (ava_respostas.ref_bloco_questoes=ava_bloco_questoes.id_bloco_questoes) 
                INNER JOIN ava_bloco ON (ava_bloco_questoes.ref_bloco=ava_bloco.id_bloco) 
                     WHERE ava_bloco.ref_granularidade=1'::text) atributos(id_resposta integer, ref_tipo_curriculo character varying, curriculo character varying, tipo_formacao character varying, ref_tipo_formacao character varying, ref_centro_aluno character varying, ref_curso character varying, ref_projeto_pedagogico character varying, ref_coordenador_curso character varying, fl_contem_lfe character varying, centro_disciplina character varying, projeto_pedagogico character varying, ref_curriculo character varying, ref_turma character varying, coordenador_curso character varying, centro_professor character varying, ref_coordenador_curriculo character varying, tipo_curriculo character varying, ref_campus character varying, coordenador_curriculo character varying, ref_professor character varying, curso character varying, ref_centro_disciplina character varying, ref_centro_professor character varying, ref_disciplina character varying) JOIN ava_respostas ON ((atributos.id_resposta = ava_respostas.id_respostas))) JOIN ava_bloco_questoes ON ((ava_bloco_questoes.id_bloco_questoes = ava_respostas.ref_bloco_questoes))) JOIN ava_questoes ON ((ava_bloco_questoes.ref_questao = ava_questoes.id_questoes))) JOIN ava_bloco ON ((ava_bloco_questoes.ref_bloco = ava_bloco.id_bloco))) JOIN ava_formulario ON ((ava_bloco.ref_formulario = ava_formulario.id_formulario)));


ALTER TABLE public.ava_view_respostas_alunos_disciplinas OWNER TO avinst;

--
-- Name: ava_view_totalizadores_alunos_cursos; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW ava_view_totalizadores_alunos_cursos AS
    SELECT totalizadores_atributos.id_totalizador, totalizadores_atributos.campus, totalizadores_atributos.centro_curso, totalizadores_atributos.coordenador_curriculo, totalizadores_atributos.coordenador_curso, totalizadores_atributos.curso, totalizadores_atributos.email_coordenador_curriculo, totalizadores_atributos.fl_contem_lfe, totalizadores_atributos.projeto_pedagogico, totalizadores_atributos.ref_campus, totalizadores_atributos.ref_centro_curso, totalizadores_atributos.ref_coordenador_curriculo, totalizadores_atributos.ref_coordenador_curso, totalizadores_atributos.ref_curso, totalizadores_atributos.ref_projeto_pedagogico, ava_totalizadores.ref_avaliacao, ava_totalizadores.ref_granularidade, ava_totalizadores.codigo, ava_totalizadores.descricao, ava_totalizadores.count FROM (crosstab('SELECT id_totalizador, chave, ava_totalizadores_atributos.valor 
                       FROM ava_totalizadores_atributos
                 INNER JOIN ava_totalizadores ON (ava_totalizadores_atributos.ref_totalizador=ava_totalizadores.id_totalizador)
                      WHERE ava_totalizadores.ref_granularidade=2
                   ORDER BY 1,2'::text, 'SELECT DISTINCT chave 
                      FROM ava_totalizadores_atributos
                INNER JOIN ava_totalizadores ON (ava_totalizadores.id_totalizador=ava_totalizadores_atributos.ref_totalizador) 
                     WHERE ava_totalizadores.ref_granularidade=2
                  ORDER BY 1'::text) totalizadores_atributos(id_totalizador integer, campus character varying, centro_curso character varying, coordenador_curriculo character varying, coordenador_curso character varying, curso character varying, email_coordenador_curriculo character varying, fl_contem_lfe character varying, projeto_pedagogico character varying, ref_campus character varying, ref_centro_curso character varying, ref_coordenador_curriculo character varying, ref_coordenador_curso character varying, ref_curso character varying, ref_projeto_pedagogico character varying) JOIN ava_totalizadores ON ((ava_totalizadores.id_totalizador = totalizadores_atributos.id_totalizador)));


ALTER TABLE public.ava_view_totalizadores_alunos_cursos OWNER TO avinst;

--
-- Name: ava_view_totalizadores_alunos_disciplinas; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW ava_view_totalizadores_alunos_disciplinas AS
    SELECT totalizadores_atributos.id_totalizador, totalizadores_atributos.turno, totalizadores_atributos.ref_tipo_curriculo, totalizadores_atributos.curriculo, totalizadores_atributos.tipo_formacao, totalizadores_atributos.ref_tipo_formacao, totalizadores_atributos.ref_centro_aluno, totalizadores_atributos.ref_projeto_pedagogico, totalizadores_atributos.ref_curso, totalizadores_atributos.ref_coordenador_curso, totalizadores_atributos.professor, totalizadores_atributos.centro_disciplina, totalizadores_atributos.fl_contem_lfe, totalizadores_atributos.projeto_pedagogico, totalizadores_atributos.ref_curriculo, totalizadores_atributos.centro_professor, totalizadores_atributos.centro_aluno, totalizadores_atributos.ref_coordenador_curriculo, totalizadores_atributos.ref_campus, totalizadores_atributos.tipo_curriculo, totalizadores_atributos.coordenador_curriculo, totalizadores_atributos.ref_professor, totalizadores_atributos.dia_semana, totalizadores_atributos.ref_centro_disciplina, totalizadores_atributos.curso, totalizadores_atributos.ref_centro_professor, totalizadores_atributos.ref_disciplina, ava_totalizadores.ref_avaliacao, ava_totalizadores.codigo, ava_totalizadores.descricao, ava_totalizadores.count FROM (crosstab('SELECT id_totalizador, chave, ava_totalizadores_atributos.valor 
                       FROM ava_totalizadores_atributos
                 INNER JOIN ava_totalizadores ON (ava_totalizadores_atributos.ref_totalizador=ava_totalizadores.id_totalizador)
                      WHERE ava_totalizadores.ref_granularidade=1'::text, 'SELECT DISTINCT chave 
                      FROM ava_totalizadores_atributos
                INNER JOIN ava_totalizadores ON (ava_totalizadores.id_totalizador=ava_totalizadores_atributos.ref_totalizador) 
                     WHERE ava_totalizadores.ref_granularidade=1'::text) totalizadores_atributos(id_totalizador integer, turno character varying, ref_tipo_curriculo character varying, curriculo character varying, tipo_formacao character varying, ref_tipo_formacao character varying, ref_centro_aluno character varying, ref_projeto_pedagogico character varying, ref_curso character varying, ref_coordenador_curso character varying, professor character varying, centro_disciplina character varying, fl_contem_lfe character varying, projeto_pedagogico character varying, ref_curriculo character varying, centro_professor character varying, centro_aluno character varying, ref_coordenador_curriculo character varying, ref_campus character varying, tipo_curriculo character varying, coordenador_curriculo character varying, ref_professor character varying, dia_semana character varying, ref_centro_disciplina character varying, curso character varying, ref_centro_professor character varying, ref_disciplina character varying) JOIN ava_totalizadores ON ((ava_totalizadores.id_totalizador = totalizadores_atributos.id_totalizador)));


ALTER TABLE public.ava_view_totalizadores_alunos_disciplinas OWNER TO avinst;

--
-- Name: ava_widget; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE ava_widget (
    id_widget character varying NOT NULL,
    versao character varying NOT NULL,
    nome character varying NOT NULL,
    opcoes_padrao text
);


ALTER TABLE public.ava_widget OWNER TO avinst;

--
-- Name: teste; Type: TABLE; Schema: public; Owner: avinst; Tablespace: 
--

CREATE TABLE teste (
    id_avaliacao integer,
    id_questao integer,
    questao text,
    tipo_questao integer,
    id_avaliador integer,
    id_resposta integer,
    valor text,
    nome_bloco text,
    ordem integer,
    id_granularidade integer,
    id_perfil integer,
    id_formulario integer,
    nome_formulario text
);


ALTER TABLE public.teste OWNER TO avinst;

--
-- Name: view_centros; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW view_centros AS
    SELECT DISTINCT (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'ref_centro'::text))) AS id, (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'nome_centro'::text))) AS nome FROM ava_totalizadores t ORDER BY (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'ref_centro'::text))), (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'nome_centro'::text)));


ALTER TABLE public.view_centros OWNER TO avinst;

--
-- Name: view_projetos_pedagogicos; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW view_projetos_pedagogicos AS
    SELECT DISTINCT (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'ref_projeto_pedagogico'::text))) AS id, (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'nome_projeto_pedagogico'::text))) AS nome FROM ava_totalizadores t ORDER BY (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'ref_projeto_pedagogico'::text))), (SELECT a.valor FROM ava_totalizadores_atributos a WHERE ((a.ref_totalizador = t.id_totalizador) AND (a.chave = 'nome_projeto_pedagogico'::text)));


ALTER TABLE public.view_projetos_pedagogicos OWNER TO avinst;

--
-- Name: view_respostas; Type: VIEW; Schema: public; Owner: avinst
--

CREATE VIEW view_respostas AS
    SELECT ava_avaliacao.id_avaliacao, ava_bloco_questoes.ref_questao AS id_questao, ava_bloco_questoes.ordem AS ordem_questao, ava_questoes.descricao AS questao, ava_questoes.tipo AS tipo_questao, ava_respostas.ref_avaliador AS id_avaliador, ava_respostas.id_respostas AS id_resposta, ava_respostas.valor, ava_bloco.nome AS nome_bloco, ava_bloco.ordem AS ordem_bloco, ava_bloco.ref_granularidade AS id_granularidade, ava_formulario.ref_perfil AS id_perfil, ava_formulario.id_formulario, ava_formulario.nome AS nome_formulario FROM ava_respostas, ava_bloco_questoes, ava_bloco, ava_formulario, ava_avaliacao, ava_questoes WHERE (((((ava_respostas.ref_bloco_questoes = ava_bloco_questoes.id_bloco_questoes) AND (ava_bloco_questoes.ref_questao = ava_questoes.id_questoes)) AND (ava_bloco_questoes.ref_bloco = ava_bloco.id_bloco)) AND (ava_bloco.ref_formulario = ava_formulario.id_formulario)) AND (ava_formulario.ref_avaliacao = ava_avaliacao.id_avaliacao));


ALTER TABLE public.view_respostas OWNER TO avinst;

--
-- Name: id_atributos; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_atributos ALTER COLUMN id_atributos SET DEFAULT nextval('ava_atributos_id_atributos_seq'::regclass);


--
-- Name: id_avaliacao; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_avaliacao ALTER COLUMN id_avaliacao SET DEFAULT nextval('ava_avaliacao_id_avaliacao_seq'::regclass);


--
-- Name: id_avaliacao_perfil_widget; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_avaliacao_perfil_widget ALTER COLUMN id_avaliacao_perfil_widget SET DEFAULT nextval('ava_avaliacao_perfil_widget_id_avaliacao_perfil_widget_seq'::regclass);


--
-- Name: id_avaliacao_widget; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_avaliacao_widget ALTER COLUMN id_avaliacao_widget SET DEFAULT nextval('ava_avaliacao_widget_id_avaliacao_widget_seq'::regclass);


--
-- Name: id_bloco; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco ALTER COLUMN id_bloco SET DEFAULT nextval('ava_bloco_id_bloco_seq'::regclass);


--
-- Name: id_bloco_questoes; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco_questoes ALTER COLUMN id_bloco_questoes SET DEFAULT nextval('ava_bloco_questoes_id_bloco_questoes_seq'::regclass);


--
-- Name: id_formulario; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_formulario ALTER COLUMN id_formulario SET DEFAULT nextval('ava_formulario_id_formulario_seq'::regclass);


--
-- Name: id_granularidade; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_granularidade ALTER COLUMN id_granularidade SET DEFAULT nextval('ava_granularidade_id_granularidade_seq'::regclass);


--
-- Name: id_mail; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail ALTER COLUMN id_mail SET DEFAULT nextval('ava_mail_id_mail_seq'::regclass);


--
-- Name: id_mail_log; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail_log ALTER COLUMN id_mail_log SET DEFAULT nextval('ava_mail_log_id_mail_log_seq'::regclass);


--
-- Name: id_perfil; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_perfil ALTER COLUMN id_perfil SET DEFAULT nextval('ava_perfil_id_perfil_seq'::regclass);


--
-- Name: id_perfil_widget; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_perfil_widget ALTER COLUMN id_perfil_widget SET DEFAULT nextval('ava_perfil_widget_id_perfil_widget_seq'::regclass);


--
-- Name: id_questoes; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_questoes ALTER COLUMN id_questoes SET DEFAULT nextval('ava_questoes_id_questoes_seq'::regclass);


--
-- Name: id_respostas; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_respostas ALTER COLUMN id_respostas SET DEFAULT nextval('ava_respostas_id_respostas_seq'::regclass);


--
-- Name: id_servico; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_servico ALTER COLUMN id_servico SET DEFAULT nextval('ava_servico_id_servico_seq'::regclass);


--
-- Name: id_totalizador; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_totalizadores ALTER COLUMN id_totalizador SET DEFAULT nextval('ava_estatisticas_id_estatistica_seq'::regclass);


--
-- Name: id_totalizador_atributo; Type: DEFAULT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_totalizadores_atributos ALTER COLUMN id_totalizador_atributo SET DEFAULT nextval('ava_estatisticas_atributos_id_estatistica_atributo_seq'::regclass);


--
-- Name: ava_atributos_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_atributos
    ADD CONSTRAINT ava_atributos_pkey PRIMARY KEY (id_atributos);


--
-- Name: ava_avaliacao_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_avaliacao
    ADD CONSTRAINT ava_avaliacao_pkey PRIMARY KEY (id_avaliacao);


--
-- Name: ava_avaliacao_widget_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_avaliacao_perfil_widget
    ADD CONSTRAINT ava_avaliacao_widget_pkey PRIMARY KEY (id_avaliacao_perfil_widget);


--
-- Name: ava_bloco_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_bloco
    ADD CONSTRAINT ava_bloco_pkey PRIMARY KEY (id_bloco);


--
-- Name: ava_bloco_questoes_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_bloco_questoes
    ADD CONSTRAINT ava_bloco_questoes_pkey PRIMARY KEY (id_bloco_questoes);


--
-- Name: ava_config_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_config
    ADD CONSTRAINT ava_config_pkey PRIMARY KEY (chave);


--
-- Name: ava_estatisticas_atributos_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_totalizadores_atributos
    ADD CONSTRAINT ava_estatisticas_atributos_pkey PRIMARY KEY (id_totalizador_atributo);


--
-- Name: ava_estatisticas_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_totalizadores
    ADD CONSTRAINT ava_estatisticas_pkey PRIMARY KEY (id_totalizador);


--
-- Name: ava_form_log_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_form_log
    ADD CONSTRAINT ava_form_log_pkey PRIMARY KEY (id_form_log);


--
-- Name: ava_formulario_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_formulario
    ADD CONSTRAINT ava_formulario_pkey PRIMARY KEY (id_formulario);


--
-- Name: ava_granularidade_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_granularidade
    ADD CONSTRAINT ava_granularidade_pkey PRIMARY KEY (id_granularidade);


--
-- Name: ava_mail_log_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_mail_log
    ADD CONSTRAINT ava_mail_log_pkey PRIMARY KEY (id_mail_log);


--
-- Name: ava_mail_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_mail
    ADD CONSTRAINT ava_mail_pkey PRIMARY KEY (id_mail);


--
-- Name: ava_perfil_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_perfil
    ADD CONSTRAINT ava_perfil_pkey PRIMARY KEY (id_perfil);


--
-- Name: ava_perfil_widget_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_perfil_widget
    ADD CONSTRAINT ava_perfil_widget_pkey PRIMARY KEY (id_perfil_widget);


--
-- Name: ava_questoes_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_questoes
    ADD CONSTRAINT ava_questoes_pkey PRIMARY KEY (id_questoes);


--
-- Name: ava_respostas_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_respostas
    ADD CONSTRAINT ava_respostas_pkey PRIMARY KEY (id_respostas);


--
-- Name: ava_servico_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_servico
    ADD CONSTRAINT ava_servico_pkey PRIMARY KEY (id_servico);


--
-- Name: ava_widget_pkey; Type: CONSTRAINT; Schema: public; Owner: avinst; Tablespace: 
--

ALTER TABLE ONLY ava_widget
    ADD CONSTRAINT ava_widget_pkey PRIMARY KEY (id_widget);


--
-- Name: ava_atributos_chave; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_atributos_chave ON ava_atributos USING btree (chave);


--
-- Name: ava_atributos_ref_resposta; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_atributos_ref_resposta ON ava_atributos USING btree (ref_resposta);


--
-- Name: ava_atributos_ref_respostas_chave; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_atributos_ref_respostas_chave ON ava_atributos USING btree (ref_resposta, ((chave)::text));


--
-- Name: ava_atributos_valor; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_atributos_valor ON ava_atributos USING btree (valor);


--
-- Name: ava_avaliacao_dt_fim; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_avaliacao_dt_fim ON ava_avaliacao USING btree (dt_fim);


--
-- Name: ava_avaliacao_dt_inicio; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_avaliacao_dt_inicio ON ava_avaliacao USING btree (dt_inicio);


--
-- Name: ava_avaliacao_dt_inicio_dt_fim; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_avaliacao_dt_inicio_dt_fim ON ava_avaliacao USING btree (dt_inicio, dt_fim);


--
-- Name: ava_bloco_questoes_ordem; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_bloco_questoes_ordem ON ava_bloco_questoes USING btree (ordem);


--
-- Name: ava_bloco_questoes_ref_bloco; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_bloco_questoes_ref_bloco ON ava_bloco_questoes USING btree (ref_bloco);


--
-- Name: ava_bloco_ref_granularidade; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_bloco_ref_granularidade ON ava_bloco USING btree (ref_granularidade);


--
-- Name: ava_form_log_ref_formulario_tipo_acao_tentativa; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_form_log_ref_formulario_tipo_acao_tentativa ON ava_form_log USING btree (ref_formulario, tipo_acao, tentativa);


--
-- Name: ava_form_log_tipo_acao_ref_formulario; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_form_log_tipo_acao_ref_formulario ON ava_form_log USING btree (tipo_acao, ref_formulario);


--
-- Name: ava_respostas_questao; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_respostas_questao ON ava_respostas USING btree (questao);


--
-- Name: ava_respostas_ref_avaliador_questao; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_respostas_ref_avaliador_questao ON ava_respostas USING btree (ref_avaliador, questao);


--
-- Name: ava_respostas_ref_bloco_questoes; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_respostas_ref_bloco_questoes ON ava_respostas USING btree (ref_bloco_questoes);


--
-- Name: ava_totalizadores_atributos_ref_totalizador_chave; Type: INDEX; Schema: public; Owner: avinst; Tablespace: 
--

CREATE INDEX ava_totalizadores_atributos_ref_totalizador_chave ON ava_totalizadores_atributos USING btree (ref_totalizador, chave);


--
-- Name: ava_atributos_ref_resposta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_atributos
    ADD CONSTRAINT ava_atributos_ref_resposta_fkey FOREIGN KEY (ref_resposta) REFERENCES ava_respostas(id_respostas);


--
-- Name: ava_avaliacao_perfil_widget_ref_perfil_widget_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_avaliacao_perfil_widget
    ADD CONSTRAINT ava_avaliacao_perfil_widget_ref_perfil_widget_fkey FOREIGN KEY (ref_perfil_widget) REFERENCES ava_perfil_widget(id_perfil_widget);


--
-- Name: ava_avaliacao_widget_ref_avaliacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_avaliacao_perfil_widget
    ADD CONSTRAINT ava_avaliacao_widget_ref_avaliacao_fkey FOREIGN KEY (ref_avaliacao) REFERENCES ava_avaliacao(id_avaliacao);


--
-- Name: ava_bloco_questoes_ref_bloco_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco_questoes
    ADD CONSTRAINT ava_bloco_questoes_ref_bloco_fkey FOREIGN KEY (ref_bloco) REFERENCES ava_bloco(id_bloco);


--
-- Name: ava_bloco_questoes_ref_questao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco_questoes
    ADD CONSTRAINT ava_bloco_questoes_ref_questao_fkey FOREIGN KEY (ref_questao) REFERENCES ava_questoes(id_questoes);


--
-- Name: ava_bloco_ref_formulario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco
    ADD CONSTRAINT ava_bloco_ref_formulario_fkey FOREIGN KEY (ref_formulario) REFERENCES ava_formulario(id_formulario);


--
-- Name: ava_bloco_ref_granularidade_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_bloco
    ADD CONSTRAINT ava_bloco_ref_granularidade_fkey FOREIGN KEY (ref_granularidade) REFERENCES ava_granularidade(id_granularidade);


--
-- Name: ava_estatisticas_atributos_ref_estatistica_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_totalizadores_atributos
    ADD CONSTRAINT ava_estatisticas_atributos_ref_estatistica_fkey FOREIGN KEY (ref_totalizador) REFERENCES ava_totalizadores(id_totalizador);


--
-- Name: ava_estatisticas_ref_granularidade_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_totalizadores
    ADD CONSTRAINT ava_estatisticas_ref_granularidade_fkey FOREIGN KEY (ref_granularidade) REFERENCES ava_granularidade(id_granularidade);


--
-- Name: ava_form_log_ref_formulario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_form_log
    ADD CONSTRAINT ava_form_log_ref_formulario_fkey FOREIGN KEY (ref_formulario) REFERENCES ava_formulario(id_formulario);


--
-- Name: ava_formulario_ref_avaliacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_formulario
    ADD CONSTRAINT ava_formulario_ref_avaliacao_fkey FOREIGN KEY (ref_avaliacao) REFERENCES ava_avaliacao(id_avaliacao);


--
-- Name: ava_formulario_ref_perfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_formulario
    ADD CONSTRAINT ava_formulario_ref_perfil_fkey FOREIGN KEY (ref_perfil) REFERENCES ava_perfil(id_perfil);


--
-- Name: ava_formulario_ref_servico_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_formulario
    ADD CONSTRAINT ava_formulario_ref_servico_fkey FOREIGN KEY (ref_servico) REFERENCES ava_servico(id_servico);


--
-- Name: ava_granularidade_ref_servico_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_granularidade
    ADD CONSTRAINT ava_granularidade_ref_servico_fkey FOREIGN KEY (ref_servico) REFERENCES ava_servico(id_servico);


--
-- Name: ava_mail_log_ref_mail_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail_log
    ADD CONSTRAINT ava_mail_log_ref_mail_fkey FOREIGN KEY (ref_mail) REFERENCES ava_mail(id_mail);


--
-- Name: ava_mail_ref_avaliacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail
    ADD CONSTRAINT ava_mail_ref_avaliacao_fkey FOREIGN KEY (ref_avaliacao) REFERENCES ava_avaliacao(id_avaliacao);


--
-- Name: ava_mail_ref_formulario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail
    ADD CONSTRAINT ava_mail_ref_formulario_fkey FOREIGN KEY (ref_formulario) REFERENCES ava_formulario(id_formulario);


--
-- Name: ava_mail_ref_perfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_mail
    ADD CONSTRAINT ava_mail_ref_perfil_fkey FOREIGN KEY (ref_perfil) REFERENCES ava_perfil(id_perfil);


--
-- Name: ava_perfil_widget_ref_perfil_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_perfil_widget
    ADD CONSTRAINT ava_perfil_widget_ref_perfil_fkey FOREIGN KEY (ref_perfil) REFERENCES ava_perfil(id_perfil);


--
-- Name: ava_perfil_widget_ref_widget_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_perfil_widget
    ADD CONSTRAINT ava_perfil_widget_ref_widget_fkey FOREIGN KEY (ref_widget) REFERENCES ava_widget(id_widget);


--
-- Name: ava_respostas_ref_ava_bloco_questao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_respostas
    ADD CONSTRAINT ava_respostas_ref_ava_bloco_questao_fkey FOREIGN KEY (ref_bloco_questoes) REFERENCES ava_bloco_questoes(id_bloco_questoes);


--
-- Name: ava_totalizadores_ref_avaliacao_fkey; Type: FK CONSTRAINT; Schema: public; Owner: avinst
--

ALTER TABLE ONLY ava_totalizadores
    ADD CONSTRAINT ava_totalizadores_ref_avaliacao_fkey FOREIGN KEY (ref_avaliacao) REFERENCES ava_avaliacao(id_avaliacao);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: view_centros; Type: ACL; Schema: public; Owner: avinst
--

REVOKE ALL ON TABLE view_centros FROM PUBLIC;
REVOKE ALL ON TABLE view_centros FROM avinst;
GRANT ALL ON TABLE view_centros TO avinst;


--
-- Name: view_projetos_pedagogicos; Type: ACL; Schema: public; Owner: avinst
--

REVOKE ALL ON TABLE view_projetos_pedagogicos FROM PUBLIC;
REVOKE ALL ON TABLE view_projetos_pedagogicos FROM avinst;
GRANT ALL ON TABLE view_projetos_pedagogicos TO avinst;


--
-- PostgreSQL database dump complete
--

