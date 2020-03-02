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
-- Name: campobuscadinamica; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE campobuscadinamica (
    campobuscadinamicaid integer NOT NULL,
    buscadinamicaid integer NOT NULL,
    tipo character varying(30),
    nome character varying(100) NOT NULL,
    valorespossiveis text,
    posicao integer,
    valorpadrao text,
    editavel boolean DEFAULT true NOT NULL,
    visivel boolean DEFAULT true NOT NULL,
    referencia text,
    filtravel boolean DEFAULT true NOT NULL,
    exibirnagrid boolean DEFAULT true NOT NULL,
    parametros text,
    chave boolean NOT NULL
);


ALTER TABLE public.campobuscadinamica OWNER TO postgres;

--
-- Name: campobuscadinamica_campobuscadinamicaid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE campobuscadinamica_campobuscadinamicaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.campobuscadinamica_campobuscadinamicaid_seq OWNER TO postgres;

--
-- Name: campobuscadinamica_campobuscadinamicaid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE campobuscadinamica_campobuscadinamicaid_seq OWNED BY campobuscadinamica.campobuscadinamicaid;


--
-- Name: campobuscadinamicaid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY campobuscadinamica ALTER COLUMN campobuscadinamicaid SET DEFAULT nextval('campobuscadinamica_campobuscadinamicaid_seq'::regclass);


--
-- Name: campobuscadinamica_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY campobuscadinamica
    ADD CONSTRAINT campobuscadinamica_pkey PRIMARY KEY (campobuscadinamicaid);


--
-- Name: buscadinamica2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY campobuscadinamica
    ADD CONSTRAINT buscadinamica2 FOREIGN KEY (buscadinamicaid) REFERENCES buscadinamica(buscadinamicaid);


--
-- PostgreSQL database dump complete
--

