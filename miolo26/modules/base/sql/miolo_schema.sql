--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
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


SET search_path = public, pg_catalog;

--
-- Name: dblink_pkey_results; Type: TYPE; Schema: public; Owner: miolo25
--

CREATE TYPE dblink_pkey_results AS (
	"position" integer,
	colname text
);


ALTER TYPE public.dblink_pkey_results OWNER TO miolo25;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: miolo_access; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_access (
    idtransaction integer NOT NULL,
    idgroup integer NOT NULL,
    rights integer,
    validatefunction text
);


ALTER TABLE public.miolo_access OWNER TO miolo25;

--
-- Name: miolo_group; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_group (
    idgroup integer NOT NULL,
    m_group character varying(50) NOT NULL,
    idmodule character varying(40)
);


ALTER TABLE public.miolo_group OWNER TO miolo25;

--
-- Name: miolo_group_idgroup_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_group_idgroup_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_group_idgroup_seq OWNER TO miolo25;

--
-- Name: miolo_group_idgroup_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_group_idgroup_seq OWNED BY miolo_group.idgroup;


--
-- Name: miolo_groupuser; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_groupuser (
    iduser integer NOT NULL,
    idgroup integer NOT NULL
);


ALTER TABLE public.miolo_groupuser OWNER TO miolo25;

--
-- Name: miolo_log; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_log (
    idlog integer NOT NULL,
    m_timestamp timestamp without time zone NOT NULL,
    description text,
    module character varying(40) NOT NULL,
    class character varying(25),
    iduser integer NOT NULL,
    idtransaction integer,
    remoteaddr character varying(15) NOT NULL
);


ALTER TABLE public.miolo_log OWNER TO miolo25;

--
-- Name: miolo_log_idlog_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_log_idlog_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_log_idlog_seq OWNER TO miolo25;

--
-- Name: miolo_log_idlog_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_log_idlog_seq OWNED BY miolo_log.idlog;


--
-- Name: miolo_module; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_module (
    idmodule character varying(40) NOT NULL,
    name character varying(100),
    description text
);


ALTER TABLE public.miolo_module OWNER TO miolo25;

--
-- Name: miolo_schedule; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_schedule (
    idschedule integer NOT NULL,
    idmodule character varying(40) NOT NULL,
    action text NOT NULL,
    parameters text,
    begintime timestamp without time zone,
    completed boolean DEFAULT false NOT NULL,
    running boolean DEFAULT false NOT NULL
);


ALTER TABLE public.miolo_schedule OWNER TO miolo25;

--
-- Name: miolo_schedule_idschedule_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_schedule_idschedule_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_schedule_idschedule_seq OWNER TO miolo25;

--
-- Name: miolo_schedule_idschedule_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_schedule_idschedule_seq OWNED BY miolo_schedule.idschedule;


--
-- Name: miolo_sequence; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_sequence (
    sequence character varying(30) NOT NULL,
    value integer NOT NULL
);


ALTER TABLE public.miolo_sequence OWNER TO miolo25;

--
-- Name: miolo_session; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_session (
    idsession integer NOT NULL,
    iduser integer NOT NULL,
    tsin character varying(15),
    tsout character varying(15),
    name character varying(50),
    sid character varying(40),
    forced character(1),
    remoteaddr character varying(15)
);


ALTER TABLE public.miolo_session OWNER TO miolo25;

--
-- Name: miolo_session_idsession_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_session_idsession_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_session_idsession_seq OWNER TO miolo25;

--
-- Name: miolo_session_idsession_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_session_idsession_seq OWNED BY miolo_session.idsession;


--
-- Name: miolo_transaction; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_transaction (
    idtransaction integer NOT NULL,
    m_transaction character varying(50) NOT NULL,
    idmodule character varying(40),
    nametransaction character varying(80),
    action character varying(80),
    parentm_transaction character varying
);


ALTER TABLE public.miolo_transaction OWNER TO miolo25;

--
-- Name: miolo_transaction_idtransaction_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_transaction_idtransaction_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_transaction_idtransaction_seq OWNER TO miolo25;

--
-- Name: miolo_transaction_idtransaction_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_transaction_idtransaction_seq OWNED BY miolo_transaction.idtransaction;


--
-- Name: miolo_user; Type: TABLE; Schema: public; Owner: miolo25; Tablespace: 
--

CREATE TABLE miolo_user (
    iduser integer NOT NULL,
    login character varying(25) NOT NULL,
    name character varying(100),
    nickname character varying(80),
    m_password character varying(40),
    confirm_hash character varying(40),
    theme character varying(20),
    idmodule character varying(40)
);


ALTER TABLE public.miolo_user OWNER TO miolo25;

--
-- Name: miolo_user_iduser_seq; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE miolo_user_iduser_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.miolo_user_iduser_seq OWNER TO miolo25;

--
-- Name: miolo_user_iduser_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: miolo25
--

ALTER SEQUENCE miolo_user_iduser_seq OWNED BY miolo_user.iduser;


--
-- Name: seq_miolo_group; Type: SEQUENCE; Schema: public; Owner: miolo25
--

CREATE SEQUENCE seq_miolo_group
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.seq_miolo_group OWNER TO miolo25;

--
-- Name: idgroup; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_group ALTER COLUMN idgroup SET DEFAULT nextval('miolo_group_idgroup_seq'::regclass);


--
-- Name: idlog; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_log ALTER COLUMN idlog SET DEFAULT nextval('miolo_log_idlog_seq'::regclass);


--
-- Name: idschedule; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_schedule ALTER COLUMN idschedule SET DEFAULT nextval('miolo_schedule_idschedule_seq'::regclass);


--
-- Name: idsession; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_session ALTER COLUMN idsession SET DEFAULT nextval('miolo_session_idsession_seq'::regclass);


--
-- Name: idtransaction; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_transaction ALTER COLUMN idtransaction SET DEFAULT nextval('miolo_transaction_idtransaction_seq'::regclass);


--
-- Name: iduser; Type: DEFAULT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_user ALTER COLUMN iduser SET DEFAULT nextval('miolo_user_iduser_seq'::regclass);


--
-- Name: miolo_group_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_group
    ADD CONSTRAINT miolo_group_pkey PRIMARY KEY (idgroup);


--
-- Name: miolo_groupuser_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_groupuser
    ADD CONSTRAINT miolo_groupuser_pkey PRIMARY KEY (iduser, idgroup);


--
-- Name: miolo_log_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_log
    ADD CONSTRAINT miolo_log_pkey PRIMARY KEY (idlog);


--
-- Name: miolo_module_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_module
    ADD CONSTRAINT miolo_module_pkey PRIMARY KEY (idmodule);


--
-- Name: miolo_schedule_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_schedule
    ADD CONSTRAINT miolo_schedule_pkey PRIMARY KEY (idschedule);


--
-- Name: miolo_sequence_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_sequence
    ADD CONSTRAINT miolo_sequence_pkey PRIMARY KEY (sequence);


--
-- Name: miolo_session_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_session
    ADD CONSTRAINT miolo_session_pkey PRIMARY KEY (idsession);


--
-- Name: miolo_transaction_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_transaction
    ADD CONSTRAINT miolo_transaction_pkey PRIMARY KEY (idtransaction);


--
-- Name: miolo_user_pkey; Type: CONSTRAINT; Schema: public; Owner: miolo25; Tablespace: 
--

ALTER TABLE ONLY miolo_user
    ADD CONSTRAINT miolo_user_pkey PRIMARY KEY (iduser);


--
-- Name: miolo_access_idtransaction_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_access
    ADD CONSTRAINT miolo_access_idtransaction_fkey FOREIGN KEY (idtransaction) REFERENCES miolo_transaction(idtransaction);


--
-- Name: miolo_groupuser_idgroup_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_groupuser
    ADD CONSTRAINT miolo_groupuser_idgroup_fkey FOREIGN KEY (idgroup) REFERENCES miolo_group(idgroup);


--
-- Name: miolo_groupuser_iduser_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_groupuser
    ADD CONSTRAINT miolo_groupuser_iduser_fkey FOREIGN KEY (iduser) REFERENCES miolo_user(iduser);


--
-- Name: miolo_schedule_idmodule_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_schedule
    ADD CONSTRAINT miolo_schedule_idmodule_fkey FOREIGN KEY (idmodule) REFERENCES miolo_module(idmodule);


--
-- Name: miolo_session_iduser_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_session
    ADD CONSTRAINT miolo_session_iduser_fkey FOREIGN KEY (iduser) REFERENCES miolo_user(iduser);


--
-- Name: miolo_transaction_idmodule_fkey; Type: FK CONSTRAINT; Schema: public; Owner: miolo25
--

ALTER TABLE ONLY miolo_transaction
    ADD CONSTRAINT miolo_transaction_idmodule_fkey FOREIGN KEY (idmodule) REFERENCES miolo_module(idmodule);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

