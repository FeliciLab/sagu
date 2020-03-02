--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: buscadinamica; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE buscadinamica (
    buscadinamicaid integer NOT NULL,
    identificador character varying(50) NOT NULL,
    modulo character varying(20) NOT NULL
);


ALTER TABLE public.buscadinamica OWNER TO postgres;

--
-- Name: buscadinamica_buscadinamicaid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE buscadinamica_buscadinamicaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.buscadinamica_buscadinamicaid_seq OWNER TO postgres;

--
-- Name: buscadinamica_buscadinamicaid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE buscadinamica_buscadinamicaid_seq OWNED BY buscadinamica.buscadinamicaid;


--
-- Name: buscadinamicaid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY buscadinamica ALTER COLUMN buscadinamicaid SET DEFAULT nextval('buscadinamica_buscadinamicaid_seq'::regclass);


--
-- Name: buscadinamica_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY buscadinamica
    ADD CONSTRAINT buscadinamica_pkey PRIMARY KEY (buscadinamicaid);

ALTER TABLE buscadinamica ADD ordenar text;

--
-- PostgreSQL database dump complete
--

