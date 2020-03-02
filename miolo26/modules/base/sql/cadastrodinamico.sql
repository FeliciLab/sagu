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
-- Name: cadastrodinamico; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cadastrodinamico (
    cadastrodinamicoid integer NOT NULL,
    identificador character varying(50) NOT NULL,
    referencia text NOT NULL,
    modulo character varying(20) NOT NULL
);


ALTER TABLE public.cadastrodinamico OWNER TO postgres;

--
-- Name: cadastrodinamico_cadastrodinamicoid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE cadastrodinamico_cadastrodinamicoid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cadastrodinamico_cadastrodinamicoid_seq OWNER TO postgres;

--
-- Name: cadastrodinamico_cadastrodinamicoid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE cadastrodinamico_cadastrodinamicoid_seq OWNED BY cadastrodinamico.cadastrodinamicoid;


--
-- Name: cadastrodinamicoid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cadastrodinamico ALTER COLUMN cadastrodinamicoid SET DEFAULT nextval('cadastrodinamico_cadastrodinamicoid_seq'::regclass);


--
-- Name: cadastrodinamico_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cadastrodinamico
    ADD CONSTRAINT cadastrodinamico_pkey PRIMARY KEY (cadastrodinamicoid);


--
-- PostgreSQL database dump complete
--

