--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

CREATE SCHEMA med;


ALTER SCHEMA med OWNER TO postgres;

COMMENT ON SCHEMA med IS 'Residência médica';

SET search_path = med, pg_catalog;

--
-- Name: cargahorariatotal(integer, integer); Type: FUNCTION; Schema: med; Owner: postgres
--

CREATE FUNCTION cargahorariatotal(p_residenteid integer, p_unidadetematicaid integer) RETURNS real
    LANGUAGE plpgsql
    AS $$
/*********************************************************************************************
  NAME: med.cargaHorariaTotal
  PURPOSE: Obter a carga horéria total cursada por um residente em uma determinada
  unidade temática.
  DESCRIPTION: A FUNÇÃO percorre todos os locais onde o residente possa ter carga
  horéria registrada para a unidade temática informada, somando tudo o que for carga
  horéria vélida.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/07/2011 Alex Smith        1. FUNÇÃO criada.
  1.1       27/07/2011 ftomasini         1. estava comparando o Código da ultima ocorréncia
                                            com os status de ocorréncia de carga horéria vélida.
  1.2       23/08/2011 Moises Heberle    1. Modificado para que suporte que seja passado uma
                                            unidadeTematicaId como NULL, fazendo o somatorio total,
                                            sem filtrar por unidade tematica.
  1.3       26/08/2011 ftomasini         1. Correção no somatorio carga horéria
*********************************************************************************************/
DECLARE
    v_retVal med.encontro.cargaHoraria%TYPE;
    v_cargahoraria med.encontro.cargaHoraria%TYPE;
    v_cargahorariacomplementar med.encontro.cargaHoraria%TYPE;
BEGIN
    SELECT COALESCE (SUM(JJ2.cargaHoraria), 0) INTO v_cargahoraria
               FROM (SELECT B.cargaHoraria,
                            med.ultimaOcorrenciaDeOfertaId(A.residenteId, B.ofertaDeUnidadeTematicaId) as ultimaOcorrenciaDeOfertaId
                       FROM med.frequencia A
                 INNER JOIN med.encontro B
                         ON B.encontroId = A.encontroId
                 INNER JOIN med.ofertaDeUnidadeTematica C
                         ON C.ofertaDeUnidadeTematicaId = B.ofertaDeUnidadeTematicaId
                 INNER JOIN med.unidadeTematica D
                         ON D.unidadeTematicaId = C.unidadeTematicaId
                         -- considerar somente presenca ou falta justificada
                      WHERE A.presenca IN ('P', 'J')
                        AND A.residenteId = p_residenteId
               AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN D.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END ) JJ2
         INNER JOIN med.ocorrenciaDeoferta E
                 ON (E.ocorrenciadeofertaid = JJ2.ultimaOcorrenciaDeOfertaId )
         INNER JOIN med.ofertadoresidente ODR
                 ON ODR.ofertadoresidenteid = E.ofertadoresidenteid       
                   -- considerar somente ofertas cujo status para o residente
                   -- seja de Aprovacao, Interrupcao com aproveitamento de
                   -- carga horaria ou Apto
               Where E.status IN (1, 2, 4);
             
            -- carga horaria oriunda de outras fontes (aproveitamentos, por exemplo)
            SELECT COALESCE(SUM(A.cargaHoraria),0) INTO v_cargahorariacomplementar
              FROM med.cargaHorariaComplementar A
             WHERE A.residenteId = p_residenteId
               AND CASE WHEN p_unidadeTematicaId IS NOT NULL THEN A.unidadeTematicaId = p_unidadeTematicaId ELSE 1=1 END;

            -- Total da carga horéria (carga horéria complementar + carga horéria total das unidades temáticas)
	    v_retVal = ROUND(COALESCE((v_cargahorariacomplementar + v_cargahoraria),0)::numeric,2);

    RETURN v_retVal;
END;
$$;


ALTER FUNCTION med.cargahorariatotal(p_residenteid integer, p_unidadetematicaid integer) OWNER TO postgres;

--
-- Name: periodosocorrenciadecontrato(integer); Type: FUNCTION; Schema: med; Owner: postgres
--

CREATE FUNCTION periodosocorrenciadecontrato(p_residenteid integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: med.periodosOcorrenciaDeContrato
  PURPOSE: Retorna periodos separados por \n

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       23/08/2011 Moises Heberle    1. FUNÇÃO criada.
  1.0       20/10/2011 ftomasini         1. Alteração para não aparecer 1° periodo
                                            quando só existe um.
**************************************************************************************/
DECLARE
    v_retVal text[];
    v_tempVal text;
    v_row RECORD;
    v_bloqueia boolean;
    v_lastPeriod timestamp;
    v_count int;
    v_forCount int;
    v_periodCount int;
    v_aux varchar;
BEGIN
    v_aux := ' ';
    v_forCount := 0;
    v_periodCount := 1;

    SELECT INTO v_count COUNT(*)
           FROM med.ocorrenciaDeContrato ODC
          WHERE ODC.residenteId = p_residenteId;

    FOR v_row IN (SELECT ODC.dataHora,
                         SOC.bloqueiaResidencia
                    FROM med.ocorrenciaDeContrato ODC
              INNER JOIN med.statusDaOcorrenciaDeContrato SOC
                      ON SOC.statusdaocorrenciadecontratoid = ODC.statusdaocorrenciadecontratoid
                   WHERE ODC.residenteId = p_residenteId
                ORDER BY dataHora)
    LOOP
        v_forCount := v_forCount + 1;

        -- Caso nao tenha ainda status, define o atual
        IF v_bloqueia IS NULL
        THEN
            v_bloqueia = v_row.bloqueiaResidencia;
        END IF;

        -- Caso nao tenha ainda lastPeriod, define ultimo
        IF v_lastPeriod IS NULL
        THEN
            v_lastPeriod = v_row.dataHora;
        END IF;

        -- Quando muda o status OU ultimo contador, adiciona uma mensagem na fila
        IF ( (v_row.bloqueiaResidencia != v_bloqueia) OR (v_count = v_forCount) )
        THEN
            IF ( v_bloqueia IS FALSE )
            THEN
                v_aux := 'Período: ' || dataPorExtenso(v_lastPeriod::date) || ' à ' || dataPorExtenso(v_row.dataHora::date);
                IF v_periodCount > 1
                THEN
                   v_aux := v_periodCount ||'° '|| v_aux;
                END IF;

                v_retVal := array_append(v_retVal,  v_aux::text);
                v_periodCount := v_periodCount + 1;
            END IF;

            v_lastPeriod := v_row.dataHora;
        END IF;

        -- Define o status atual
        v_bloqueia = v_row.bloqueiaResidencia;
    END LOOP;

    RETURN array_to_string(v_retVal, E'\n');
END;
$$;


ALTER FUNCTION med.periodosocorrenciadecontrato(p_residenteid integer) OWNER TO postgres;

--
-- Name: ultimaocorrenciadecontratoid(integer); Type: FUNCTION; Schema: med; Owner: postgres
--

CREATE FUNCTION ultimaocorrenciadecontratoid(p_residenteid integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: med.ultimaOcorrenciaDeContratoId
  PURPOSE: Retorna o identificador da ocorrencia de contrato mais recente para o residente informado.
 *************************************************************************************/
DECLARE
    v_retVal med.ocorrenciaDeContrato.ocorrenciaDeContratoId%TYPE;
BEGIN
    SELECT A.ocorrenciaDeContratoId INTO v_retVal
      FROM med.ocorrenciaDeContrato A
     WHERE A.residenteId = p_residenteId
            AND A.dataHora::DATE <= now()
  ORDER BY A.dataHora DESC
     LIMIT 1;

    RETURN v_retVal;
END;
$$;


ALTER FUNCTION med.ultimaocorrenciadecontratoid(p_residenteid integer) OWNER TO postgres;

--
-- Name: ultimaocorrenciadeofertaid(integer, integer); Type: FUNCTION; Schema: med; Owner: postgres
--

CREATE FUNCTION ultimaocorrenciadeofertaid(p_residenteid integer, p_ofertadeunidadetematicaid integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
/*************************************************************************************
  NAME: med.ultimaOcorrenciaDeOfertaId
  PURPOSE: Retorna o identificador da ocorrência de oferta mais recente da oferta de
  unidade temática informada, para o residente informado.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       18/07/2011 Alex Smith        1. Função criada.
**************************************************************************************/
DECLARE
    v_retVal med.ocorrenciaDeOferta.ocorrenciaDeOfertaId%TYPE;
BEGIN
    SELECT A.ocorrenciaDeOfertaId INTO v_retVal
      FROM med.ocorrenciaDeOferta A
INNER JOIN med.ofertaDoResidente B
        ON B.ofertaDoResidenteId = A.ofertaDoResidenteId
     WHERE B.residenteId = p_residenteId
       AND B.ofertaDeUnidadeTematicaId = p_ofertaDeUnidadeTematicaId
  ORDER BY A.dataHora DESC
     LIMIT 1;

    RETURN v_retVal;
END;
$$;


ALTER FUNCTION med.ultimaocorrenciadeofertaid(p_residenteid integer, p_ofertadeunidadetematicaid integer) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: atividadeextra; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE atividadeextra (
    atividadeextraid integer NOT NULL,
    descricao character varying(255) NOT NULL,
    conteudo text,
    inicio timestamp without time zone NOT NULL,
    fim timestamp without time zone NOT NULL,
    cargahoraria real NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.atividadeextra OWNER TO postgres;

--
-- Name: atividadeextra_atividadeextraid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE atividadeextra_atividadeextraid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.atividadeextra_atividadeextraid_seq OWNER TO postgres;

--
-- Name: atividadeextra_atividadeextraid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE atividadeextra_atividadeextraid_seq OWNED BY atividadeextra.atividadeextraid;


--
-- Name: cargahorariacomplementar; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE cargahorariacomplementar (
    cargahorariacomplementarid integer NOT NULL,
    tipodecargahorariacomplementarid integer NOT NULL,
    unidadetematicaid integer NOT NULL,
    residenteid integer NOT NULL,
    cargahoraria real NOT NULL,
    justificativa character varying(255),
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.cargahorariacomplementar OWNER TO postgres;

--
-- Name: cargahorariacomplementar_cargahorariacomplementarid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE cargahorariacomplementar_cargahorariacomplementarid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.cargahorariacomplementar_cargahorariacomplementarid_seq OWNER TO postgres;

--
-- Name: cargahorariacomplementar_cargahorariacomplementarid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE cargahorariacomplementar_cargahorariacomplementarid_seq OWNED BY cargahorariacomplementar.cargahorariacomplementarid;


--
-- Name: coorientador; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE coorientador (
    personid bigint NOT NULL,
    trabalhodeconclusaoid integer NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.coorientador OWNER TO postgres;

--
-- Name: encontro; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE encontro (
    encontroid integer NOT NULL,
    temaid integer,
    ofertadeunidadetematicaid integer NOT NULL,
    inicio timestamp without time zone NOT NULL,
    fim timestamp without time zone NOT NULL,
    cargahoraria real NOT NULL,
    conteudoministrado text,
    ministrante character varying(255) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.encontro OWNER TO postgres;

--
-- Name: encontro_encontroid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE encontro_encontroid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.encontro_encontroid_seq OWNER TO postgres;

--
-- Name: encontro_encontroid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE encontro_encontroid_seq OWNED BY encontro.encontroid;


--
-- Name: ofertadeunidadetematica; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE ofertadeunidadetematica (
    ofertadeunidadetematicaid integer NOT NULL,
    personid bigint NOT NULL,
    unidadetematicaid integer NOT NULL,
    inicio date NOT NULL,
    fim date NOT NULL,
    encerramento timestamp without time zone,
    encerradopor integer,
    instituicaoexecutora bigint,
    equipe character varying
)
INHERITS (public.baslog);


ALTER TABLE med.ofertadeunidadetematica OWNER TO postgres;

--
-- Name: tema; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE tema (
    temaid integer NOT NULL,
    descricao character varying(255) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.tema OWNER TO postgres;

--
-- Name: unidadetematica; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE unidadetematica (
    unidadetematicaid integer NOT NULL,
    periodo character varying(10) NOT NULL,
    descricao character varying(255) NOT NULL,
    sumula text,
    cargahoraria real NOT NULL,
    frequenciaminima real NOT NULL,
    tipo character(1) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.unidadetematica OWNER TO postgres;

--
-- Name: encontrosofertadeunidadetematica; Type: VIEW; Schema: med; Owner: solis
--

CREATE VIEW encontrosofertadeunidadetematica AS
    SELECT a.ofertadeunidadetematicaid, e.personid AS personidpreceptor_oferta, e.name AS nomepreceptor_oferta, a.inicio AS inicio_oferta, a.fim AS fim_oferta, a.encerramento AS dataencerramento_oferta, a.encerradopor AS encerradopor_oferta, a.unidadetematicaid, b.descricao AS descricao_unidadetematica, b.periodo AS periodo_unidadetematica, b.sumula AS sumula_unidadetematica, b.cargahoraria AS cargahoraria_unidadetematica, b.frequenciaminima AS frequenciaminima_unidadetematica, b.tipo AS tipo_unidadetematica, CASE WHEN (b.tipo = 'T'::bpchar) THEN 'TEÓRICA'::text ELSE 'PRÁTICA'::text END AS descricaotipo_unidadetematica, f.encontroid AS codigo_encontro, f.temaid AS codigotema_encontro, g.descricao AS descricaotema_encontro, (f.inicio)::date AS datainicio_encontro, (f.fim)::date AS datafim_encontro, (f.inicio)::time without time zone AS horainicio_encontro, (f.fim)::time without time zone AS horafim_encontro, f.cargahoraria AS cargahoraria_encontro, f.conteudoministrado AS conteudoministrado_encontro, f.ministrante AS ministrante_encontro FROM ((((ofertadeunidadetematica a JOIN unidadetematica b ON ((a.unidadetematicaid = b.unidadetematicaid))) JOIN ONLY public.basphysicalperson e ON ((e.personid = a.personid))) JOIN encontro f ON ((f.ofertadeunidadetematicaid = a.ofertadeunidadetematicaid))) LEFT JOIN tema g ON ((f.temaid = g.temaid)));


ALTER TABLE med.encontrosofertadeunidadetematica OWNER TO solis;

--
-- Name: enfase; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE enfase (
    enfaseid integer NOT NULL,
    descricao character varying(255) NOT NULL,
    abreviatura character varying(30) NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.enfase OWNER TO postgres;

--
-- Name: enfase_enfaseid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE enfase_enfaseid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.enfase_enfaseid_seq OWNER TO postgres;

--
-- Name: enfase_enfaseid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE enfase_enfaseid_seq OWNED BY enfase.enfaseid;


--
-- Name: enfasedaunidadetematica; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE enfasedaunidadetematica (
    unidadetematicaid integer NOT NULL,
    enfaseid integer NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.enfasedaunidadetematica OWNER TO postgres;

--
-- Name: frequencia; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE frequencia (
    encontroid integer NOT NULL,
    residenteid integer NOT NULL,
    presenca character(1) NOT NULL,
    justificativa character varying(255),
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.frequencia OWNER TO postgres;

--
-- Name: membrodabanca; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE membrodabanca (
    personid bigint NOT NULL,
    trabalhodeconclusaoid integer NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.membrodabanca OWNER TO postgres;

--
-- Name: nucleodaunidadetematica; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE nucleodaunidadetematica (
    unidadetematicaid integer NOT NULL,
    nucleoprofissionalid integer NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.nucleodaunidadetematica OWNER TO postgres;

--
-- Name: nucleoprofissional; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE nucleoprofissional (
    nucleoprofissionalid integer NOT NULL,
    descricao character varying(255) NOT NULL,
    abreviatura character varying(30) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.nucleoprofissional OWNER TO postgres;

--
-- Name: nucleoprofissional_nucleoprofissionalid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE nucleoprofissional_nucleoprofissionalid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.nucleoprofissional_nucleoprofissionalid_seq OWNER TO postgres;

--
-- Name: nucleoprofissional_nucleoprofissionalid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE nucleoprofissional_nucleoprofissionalid_seq OWNED BY nucleoprofissional.nucleoprofissionalid;


--
-- Name: ocorrenciadecontrato; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE ocorrenciadecontrato (
    ocorrenciadecontratoid integer NOT NULL,
    statusdaocorrenciadecontratoid integer NOT NULL,
    residenteid integer,
    datahora timestamp without time zone NOT NULL,
    observacoes text,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.ocorrenciadecontrato OWNER TO postgres;

--
-- Name: ocorrenciadecontrato_ocorrenciadecontratoid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE ocorrenciadecontrato_ocorrenciadecontratoid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.ocorrenciadecontrato_ocorrenciadecontratoid_seq OWNER TO postgres;

--
-- Name: ocorrenciadecontrato_ocorrenciadecontratoid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE ocorrenciadecontrato_ocorrenciadecontratoid_seq OWNED BY ocorrenciadecontrato.ocorrenciadecontratoid;


--
-- Name: ocorrenciadeoferta; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE ocorrenciadeoferta (
    ocorrenciadeofertaid integer NOT NULL,
    ofertadoresidenteid integer NOT NULL,
    datahora timestamp without time zone NOT NULL,
    status integer NOT NULL,
    observacoes text,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.ocorrenciadeoferta OWNER TO postgres;

--
-- Name: ocorrenciadeoferta_ocorrenciadeofertaid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE ocorrenciadeoferta_ocorrenciadeofertaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.ocorrenciadeoferta_ocorrenciadeofertaid_seq OWNER TO postgres;

--
-- Name: ocorrenciadeoferta_ocorrenciadeofertaid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE ocorrenciadeoferta_ocorrenciadeofertaid_seq OWNED BY ocorrenciadeoferta.ocorrenciadeofertaid;


--
-- Name: ofertadeunidadetematica_ofertadeunidadetematicaid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE ofertadeunidadetematica_ofertadeunidadetematicaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.ofertadeunidadetematica_ofertadeunidadetematicaid_seq OWNER TO postgres;

--
-- Name: ofertadeunidadetematica_ofertadeunidadetematicaid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE ofertadeunidadetematica_ofertadeunidadetematicaid_seq OWNED BY ofertadeunidadetematica.ofertadeunidadetematicaid;


--
-- Name: ofertadoresidente; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE ofertadoresidente (
    ofertadoresidenteid integer NOT NULL,
    residenteid integer NOT NULL,
    ofertadeunidadetematicaid integer NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.ofertadoresidente OWNER TO postgres;

--
-- Name: ofertadoresidente_ofertadoresidenteid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE ofertadoresidente_ofertadoresidenteid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.ofertadoresidente_ofertadoresidenteid_seq OWNER TO postgres;

--
-- Name: ofertadoresidente_ofertadoresidenteid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE ofertadoresidente_ofertadoresidenteid_seq OWNED BY ofertadoresidente.ofertadoresidenteid;


--
-- Name: participacaoematividadeextra; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE participacaoematividadeextra (
    atividadeextraid integer NOT NULL,
    residenteid integer NOT NULL,
    cargahoraria real NOT NULL,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.participacaoematividadeextra OWNER TO postgres;

--
-- Name: penalidade; Type: TABLE; Schema: med; Owner: solis; Tablespace: 
--

CREATE TABLE penalidade (
    penalidadeid integer NOT NULL,
    residenteid integer,
    preceptorid integer,
    tipodepenalidadeid integer NOT NULL,
    data date,
    hora character varying,
    observacoes character varying(255),
    notificado character(1)
)
INHERITS (public.baslog);


ALTER TABLE med.penalidade OWNER TO solis;

--
-- Name: penalidade_penalidadeid_seq; Type: SEQUENCE; Schema: med; Owner: solis
--

CREATE SEQUENCE penalidade_penalidadeid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.penalidade_penalidadeid_seq OWNER TO solis;

--
-- Name: penalidade_penalidadeid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: solis
--

ALTER SEQUENCE penalidade_penalidadeid_seq OWNED BY penalidade.penalidadeid;


--
-- Name: periodounidadetematica; Type: VIEW; Schema: med; Owner: solis
--

CREATE VIEW periodounidadetematica AS
    (SELECT 'P1'::character varying AS periodoid, 'Primeiro ano'::character varying AS descricao UNION SELECT 'P2'::character varying AS periodoid, 'Segundo ano'::character varying AS descricao) UNION SELECT 'P3'::character varying AS periodoid, 'Terceiro ano'::character varying AS descricao;


ALTER TABLE med.periodounidadetematica OWNER TO solis;

--
-- Name: preceptoria; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE preceptoria (
    preceptorid integer NOT NULL,
    personid bigint NOT NULL,
    enfaseid integer,
    nucleoprofissionalid integer,
    inicio date NOT NULL,
    fim date,
    responsavel boolean DEFAULT false,
    titulacao character varying,
    chsemanal double precision,
    chmensal double precision,
    referencia boolean DEFAULT false,
    centerid integer
)
INHERITS (public.baslog);


ALTER TABLE med.preceptoria OWNER TO postgres;

--
-- Name: COLUMN preceptoria.referencia; Type: COMMENT; Schema: med; Owner: postgres
--

COMMENT ON COLUMN preceptoria.referencia IS 'Define que o preceptor é responsavel pela sua enfase';


--
-- Name: preceptoria_preceptorid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE preceptoria_preceptorid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.preceptoria_preceptorid_seq OWNER TO postgres;

--
-- Name: preceptoria_preceptorid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE preceptoria_preceptorid_seq OWNED BY preceptoria.preceptorid;


--
-- Name: residente; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE residente (
    residenteid integer NOT NULL,
    enfaseid integer NOT NULL,
    nucleoprofissionalid integer NOT NULL,
    inicio date NOT NULL,
    fimprevisto date NOT NULL,
    notaperiodo1semestre1 real,
    parecerperiodo1semestre1 text,
    notaperiodo1semestre2 real,
    parecerperiodo1semestre2 text,
    mediaperiodo1 real,
    parecermediaperiodo1 text,
    notaperiodo2semestre1 real,
    parecerperiodo2semestre1 text,
    notaperiodo2semestre2 real,
    parecerperiodo2semestre2 text,
    mediaperiodo2 real,
    parecermediaperiodo2 text,
    notafinal real,
    parecerfinal text,
    personid bigint NOT NULL,
    subscriptionid integer,
    unidade2 integer,
    unidade1 integer,
    descricao text,
    centerid integer,
    turmaid integer,
    instituicaoformadora bigint,
    notaperiodo3semestre1 double precision,
    parecerperiodo3semestre1 character varying(255),
    notaperiodo3semestre2 double precision,
    parecerperiodo3semestre2 character varying(255),
    mediaperiodo3 double precision,
    parecermediaperiodo3 character varying(255)
)
INHERITS (public.baslog);


ALTER TABLE med.residente OWNER TO postgres;

--
-- Name: residente_residenteid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE residente_residenteid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.residente_residenteid_seq OWNER TO postgres;

--
-- Name: residente_residenteid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE residente_residenteid_seq OWNED BY residente.residenteid;


--
-- Name: statusdaocorrenciadecontrato; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE statusdaocorrenciadecontrato (
    statusdaocorrenciadecontratoid integer NOT NULL,
    descricao character varying(255) NOT NULL,
    bloqueiaresidencia boolean NOT NULL,
    concluiresidencia boolean DEFAULT false
)
INHERITS (public.baslog);


ALTER TABLE med.statusdaocorrenciadecontrato OWNER TO postgres;

--
-- Name: statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq OWNER TO postgres;

--
-- Name: statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq OWNED BY statusdaocorrenciadecontrato.statusdaocorrenciadecontratoid;


--
-- Name: tema_temaid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE tema_temaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.tema_temaid_seq OWNER TO postgres;

--
-- Name: tema_temaid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE tema_temaid_seq OWNED BY tema.temaid;


--
-- Name: temadaunidadetematica; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE temadaunidadetematica (
    temaid integer NOT NULL,
    cargahoraria real NOT NULL,
    ofertadeunidadetematicaid integer
)
INHERITS (public.baslog);


ALTER TABLE med.temadaunidadetematica OWNER TO postgres;

--
-- Name: temasofertadeunidadetematica; Type: VIEW; Schema: med; Owner: solis
--

CREATE VIEW temasofertadeunidadetematica AS
    SELECT a.ofertadeunidadetematicaid, e.personid AS personidpreceptor_oferta, e.name AS nomepreceptor_oferta, a.inicio AS inicio_oferta, a.fim AS fim_oferta, a.encerramento AS dataencerramento_oferta, a.encerradopor AS encerradopor_oferta, a.unidadetematicaid, b.descricao AS descricao_unidadetematica, b.periodo AS periodo_unidadetematica, b.sumula AS sumula_unidadetematica, b.cargahoraria AS cargahoraria_unidadetematica, b.frequenciaminima AS frequenciaminima_unidadetematica, b.tipo AS tipo_unidadetematica, CASE WHEN (b.tipo = 'T'::bpchar) THEN 'TEÓRICA'::text ELSE 'PRÁTICA'::text END AS descricaotipo_unidadetematica, d.temaid, c.cargahoraria AS cargahoraria_temaoferta, d.descricao FROM ((((ofertadeunidadetematica a JOIN unidadetematica b ON ((a.unidadetematicaid = b.unidadetematicaid))) JOIN temadaunidadetematica c ON ((c.ofertadeunidadetematicaid = a.ofertadeunidadetematicaid))) JOIN tema d ON ((c.temaid = d.temaid))) JOIN ONLY public.basphysicalperson e ON ((e.personid = a.personid)));


ALTER TABLE med.temasofertadeunidadetematica OWNER TO solis;

--
-- Name: tipodecargahorariacomplementar; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE tipodecargahorariacomplementar (
    tipodecargahorariacomplementarid integer NOT NULL,
    descricao character varying(255) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.tipodecargahorariacomplementar OWNER TO postgres;

--
-- Name: tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq OWNER TO postgres;

--
-- Name: tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq OWNED BY tipodecargahorariacomplementar.tipodecargahorariacomplementarid;


--
-- Name: tipodepenalidade; Type: TABLE; Schema: med; Owner: solis; Tablespace: 
--

CREATE TABLE tipodepenalidade (
    tipopenalidadeid integer NOT NULL,
    descricao character varying NOT NULL,
    emailid integer
)
INHERITS (public.baslog);


ALTER TABLE med.tipodepenalidade OWNER TO solis;

--
-- Name: tipodepenalidade_tipopenalidadeid_seq; Type: SEQUENCE; Schema: med; Owner: solis
--

CREATE SEQUENCE tipodepenalidade_tipopenalidadeid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.tipodepenalidade_tipopenalidadeid_seq OWNER TO solis;

--
-- Name: tipodepenalidade_tipopenalidadeid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: solis
--

ALTER SEQUENCE tipodepenalidade_tipopenalidadeid_seq OWNED BY tipodepenalidade.tipopenalidadeid;


--
-- Name: tipounidadetematica; Type: VIEW; Schema: med; Owner: solis
--

CREATE VIEW tipounidadetematica AS
    (SELECT 'T'::character varying AS tipoid, 'Teórica'::character varying AS descricao UNION SELECT 'P'::character varying AS tipoid, 'Prática'::character varying AS descricao) UNION SELECT 'C'::character varying AS tipoid, 'Teórica conceitual'::character varying AS descricao;


ALTER TABLE med.tipounidadetematica OWNER TO solis;

--
-- Name: trabalhodeconclusao; Type: TABLE; Schema: med; Owner: postgres; Tablespace: 
--

CREATE TABLE trabalhodeconclusao (
    trabalhodeconclusaoid integer NOT NULL,
    orientadorid integer,
    residenteid integer NOT NULL,
    titulo character varying(255) NOT NULL,
    tema text,
    apto boolean,
    centerid integer,
    nota double precision
)
INHERITS (public.baslog);


ALTER TABLE med.trabalhodeconclusao OWNER TO postgres;

--
-- Name: trabalhodeconclusao_trabalhodeconclusaoid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE trabalhodeconclusao_trabalhodeconclusaoid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.trabalhodeconclusao_trabalhodeconclusaoid_seq OWNER TO postgres;

--
-- Name: trabalhodeconclusao_trabalhodeconclusaoid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE trabalhodeconclusao_trabalhodeconclusaoid_seq OWNED BY trabalhodeconclusao.trabalhodeconclusaoid;


--
-- Name: turma; Type: TABLE; Schema: med; Owner: solis; Tablespace: 
--

CREATE TABLE turma (
    turmaid integer NOT NULL,
    codigoturma character varying(50) NOT NULL,
    enfaseid integer,
    nucleoprofissionalid integer,
    descricao character varying NOT NULL,
    datainicio date,
    datafim date,
    quantidadeperiodo integer NOT NULL,
    vagas integer NOT NULL,
    tipoavaliacaotcr character(1) NOT NULL
)
INHERITS (public.baslog);


ALTER TABLE med.turma OWNER TO solis;

--
-- Name: COLUMN turma.tipoavaliacaotcr; Type: COMMENT; Schema: med; Owner: solis
--

COMMENT ON COLUMN turma.tipoavaliacaotcr IS 'Tipo de avaliação do trabalho de conclusão de residente, conceito(C) ou nota(N)';


--
-- Name: turma_turmaid_seq; Type: SEQUENCE; Schema: med; Owner: solis
--

CREATE SEQUENCE turma_turmaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.turma_turmaid_seq OWNER TO solis;

--
-- Name: turma_turmaid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: solis
--

ALTER SEQUENCE turma_turmaid_seq OWNED BY turma.turmaid;


--
-- Name: unidadetematica_unidadetematicaid_seq; Type: SEQUENCE; Schema: med; Owner: postgres
--

CREATE SEQUENCE unidadetematica_unidadetematicaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE med.unidadetematica_unidadetematicaid_seq OWNER TO postgres;

--
-- Name: unidadetematica_unidadetematicaid_seq; Type: SEQUENCE OWNED BY; Schema: med; Owner: postgres
--

ALTER SEQUENCE unidadetematica_unidadetematicaid_seq OWNED BY unidadetematica.unidadetematicaid;


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY atividadeextra ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY atividadeextra ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: atividadeextraid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY atividadeextra ALTER COLUMN atividadeextraid SET DEFAULT nextval('atividadeextra_atividadeextraid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: cargahorariacomplementarid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar ALTER COLUMN cargahorariacomplementarid SET DEFAULT nextval('cargahorariacomplementar_cargahorariacomplementarid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY encontro ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY encontro ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: encontroid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY encontro ALTER COLUMN encontroid SET DEFAULT nextval('encontro_encontroid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfase ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfase ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: enfaseid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfase ALTER COLUMN enfaseid SET DEFAULT nextval('enfase_enfaseid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfasedaunidadetematica ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfasedaunidadetematica ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY frequencia ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY frequencia ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleodaunidadetematica ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleodaunidadetematica ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleoprofissional ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleoprofissional ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: nucleoprofissionalid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleoprofissional ALTER COLUMN nucleoprofissionalid SET DEFAULT nextval('nucleoprofissional_nucleoprofissionalid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: ocorrenciadecontratoid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato ALTER COLUMN ocorrenciadecontratoid SET DEFAULT nextval('ocorrenciadecontrato_ocorrenciadecontratoid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadeoferta ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadeoferta ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: ocorrenciadeofertaid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadeoferta ALTER COLUMN ocorrenciadeofertaid SET DEFAULT nextval('ocorrenciadeoferta_ocorrenciadeofertaid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: ofertadeunidadetematicaid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica ALTER COLUMN ofertadeunidadetematicaid SET DEFAULT nextval('ofertadeunidadetematica_ofertadeunidadetematicaid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: ofertadoresidenteid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente ALTER COLUMN ofertadoresidenteid SET DEFAULT nextval('ofertadoresidente_ofertadoresidenteid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY participacaoematividadeextra ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY participacaoematividadeextra ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: penalidadeid; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade ALTER COLUMN penalidadeid SET DEFAULT nextval('penalidade_penalidadeid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: preceptorid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria ALTER COLUMN preceptorid SET DEFAULT nextval('preceptoria_preceptorid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: residenteid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente ALTER COLUMN residenteid SET DEFAULT nextval('residente_residenteid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY statusdaocorrenciadecontrato ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY statusdaocorrenciadecontrato ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: statusdaocorrenciadecontratoid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY statusdaocorrenciadecontrato ALTER COLUMN statusdaocorrenciadecontratoid SET DEFAULT nextval('statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tema ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tema ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: temaid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tema ALTER COLUMN temaid SET DEFAULT nextval('tema_temaid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY temadaunidadetematica ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY temadaunidadetematica ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tipodecargahorariacomplementar ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tipodecargahorariacomplementar ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: tipodecargahorariacomplementarid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY tipodecargahorariacomplementar ALTER COLUMN tipodecargahorariacomplementarid SET DEFAULT nextval('tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY tipodepenalidade ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY tipodepenalidade ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: tipopenalidadeid; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY tipodepenalidade ALTER COLUMN tipopenalidadeid SET DEFAULT nextval('tipodepenalidade_tipopenalidadeid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: trabalhodeconclusaoid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao ALTER COLUMN trabalhodeconclusaoid SET DEFAULT nextval('trabalhodeconclusao_trabalhodeconclusaoid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY turma ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY turma ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: turmaid; Type: DEFAULT; Schema: med; Owner: solis
--

ALTER TABLE ONLY turma ALTER COLUMN turmaid SET DEFAULT nextval('turma_turmaid_seq'::regclass);


--
-- Name: username; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY unidadetematica ALTER COLUMN username SET DEFAULT "current_user"();


--
-- Name: datetime; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY unidadetematica ALTER COLUMN datetime SET DEFAULT now();


--
-- Name: unidadetematicaid; Type: DEFAULT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY unidadetematica ALTER COLUMN unidadetematicaid SET DEFAULT nextval('unidadetematica_unidadetematicaid_seq'::regclass);


--
-- Data for Name: atividadeextra; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: atividadeextra_atividadeextraid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('atividadeextra_atividadeextraid_seq', 1, false);


--
-- Data for Name: cargahorariacomplementar; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: cargahorariacomplementar_cargahorariacomplementarid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('cargahorariacomplementar_cargahorariacomplementarid_seq', 1, false);


--
-- Data for Name: coorientador; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: encontro; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: encontro_encontroid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('encontro_encontroid_seq', 1, false);


--
-- Data for Name: enfase; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: enfase_enfaseid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('enfase_enfaseid_seq', 1, false);


--
-- Data for Name: enfasedaunidadetematica; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: frequencia; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: membrodabanca; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: nucleodaunidadetematica; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: nucleoprofissional; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: nucleoprofissional_nucleoprofissionalid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('nucleoprofissional_nucleoprofissionalid_seq', 1, false);


--
-- Data for Name: ocorrenciadecontrato; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: ocorrenciadecontrato_ocorrenciadecontratoid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('ocorrenciadecontrato_ocorrenciadecontratoid_seq', 1, false);


--
-- Data for Name: ocorrenciadeoferta; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: ocorrenciadeoferta_ocorrenciadeofertaid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('ocorrenciadeoferta_ocorrenciadeofertaid_seq', 1, false);


--
-- Data for Name: ofertadeunidadetematica; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: ofertadeunidadetematica_ofertadeunidadetematicaid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('ofertadeunidadetematica_ofertadeunidadetematicaid_seq', 1, false);


--
-- Data for Name: ofertadoresidente; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: ofertadoresidente_ofertadoresidenteid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('ofertadoresidente_ofertadoresidenteid_seq', 1, false);


--
-- Data for Name: participacaoematividadeextra; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Data for Name: penalidade; Type: TABLE DATA; Schema: med; Owner: solis
--



--
-- Name: penalidade_penalidadeid_seq; Type: SEQUENCE SET; Schema: med; Owner: solis
--

SELECT pg_catalog.setval('penalidade_penalidadeid_seq', 1, false);


--
-- Data for Name: preceptoria; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: preceptoria_preceptorid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('preceptoria_preceptorid_seq', 1, false);


--
-- Data for Name: residente; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: residente_residenteid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('residente_residenteid_seq', 1, false);


--
-- Data for Name: statusdaocorrenciadecontrato; Type: TABLE DATA; Schema: med; Owner: postgres
--

INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.842654-03', NULL, 1, 'ATIVO', false, false);
INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.847784-03', NULL, 2, 'SUSPENSO', true, false);
INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.84952-03', NULL, 3, 'LICENÇA MATERNIDADE E PATERNIDADE', false, false);
INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.850379-03', NULL, 4, 'DESLIGADO', true, false);
INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.851166-03', NULL, 5, 'AFASTAMENTO INSS', false, false);
INSERT INTO statusdaocorrenciadecontrato VALUES ('postgres', '2011-08-10 07:17:21.852122-03', NULL, 6, 'CONCLUÍDO', true, true);


--
-- Name: statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('statusdaocorrenciadecontrato_statusdaocorrenciadecontratoid_seq', 1, false);


--
-- Data for Name: tema; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: tema_temaid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('tema_temaid_seq', 1, false);


--
-- Data for Name: temadaunidadetematica; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('tipodecargahorariacomplementa_tipodecargahorariacomplementa_seq', 1, false);


--
-- Data for Name: tipodecargahorariacomplementar; Type: TABLE DATA; Schema: med; Owner: postgres
--

INSERT INTO tipodecargahorariacomplementar VALUES ('postgres', '2011-08-10 07:17:21.853069-03', NULL, 1, 'APROVEITAMENTO');
INSERT INTO tipodecargahorariacomplementar VALUES ('postgres', '2011-08-10 07:17:21.855378-03', NULL, 2, 'PARTICIPAÇÃO EM EVENTOS');


--
-- Data for Name: tipodepenalidade; Type: TABLE DATA; Schema: med; Owner: solis
--



--
-- Name: tipodepenalidade_tipopenalidadeid_seq; Type: SEQUENCE SET; Schema: med; Owner: solis
--

SELECT pg_catalog.setval('tipodepenalidade_tipopenalidadeid_seq', 1, false);


--
-- Data for Name: trabalhodeconclusao; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: trabalhodeconclusao_trabalhodeconclusaoid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('trabalhodeconclusao_trabalhodeconclusaoid_seq', 1, false);


--
-- Data for Name: turma; Type: TABLE DATA; Schema: med; Owner: solis
--



--
-- Name: turma_turmaid_seq; Type: SEQUENCE SET; Schema: med; Owner: solis
--

SELECT pg_catalog.setval('turma_turmaid_seq', 1, false);


--
-- Data for Name: unidadetematica; Type: TABLE DATA; Schema: med; Owner: postgres
--



--
-- Name: unidadetematica_unidadetematicaid_seq; Type: SEQUENCE SET; Schema: med; Owner: postgres
--

SELECT pg_catalog.setval('unidadetematica_unidadetematicaid_seq', 1, false);


--
-- Name: atividadeextra_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY atividadeextra
    ADD CONSTRAINT atividadeextra_pkey PRIMARY KEY (atividadeextraid);


--
-- Name: cargahorariacomplementar_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cargahorariacomplementar
    ADD CONSTRAINT cargahorariacomplementar_pkey PRIMARY KEY (cargahorariacomplementarid);


--
-- Name: coorientador_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY coorientador
    ADD CONSTRAINT coorientador_pkey PRIMARY KEY (personid, trabalhodeconclusaoid);


--
-- Name: encontro_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY encontro
    ADD CONSTRAINT encontro_pkey PRIMARY KEY (encontroid);


--
-- Name: enfase_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY enfase
    ADD CONSTRAINT enfase_pkey PRIMARY KEY (enfaseid);


--
-- Name: enfasedaunidadetematica_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY enfasedaunidadetematica
    ADD CONSTRAINT enfasedaunidadetematica_pkey PRIMARY KEY (unidadetematicaid, enfaseid);


--
-- Name: frequencia_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY frequencia
    ADD CONSTRAINT frequencia_pkey PRIMARY KEY (encontroid, residenteid);


--
-- Name: membrodabanca_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY membrodabanca
    ADD CONSTRAINT membrodabanca_pkey PRIMARY KEY (personid, trabalhodeconclusaoid);


--
-- Name: nucleodaunidadetematica_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY nucleodaunidadetematica
    ADD CONSTRAINT nucleodaunidadetematica_pkey PRIMARY KEY (unidadetematicaid, nucleoprofissionalid);


--
-- Name: nucleoprofissional_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY nucleoprofissional
    ADD CONSTRAINT nucleoprofissional_pkey PRIMARY KEY (nucleoprofissionalid);


--
-- Name: ocorrenciadecontrato_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ocorrenciadecontrato
    ADD CONSTRAINT ocorrenciadecontrato_pkey PRIMARY KEY (ocorrenciadecontratoid);


--
-- Name: ocorrenciadeoferta_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ocorrenciadeoferta
    ADD CONSTRAINT ocorrenciadeoferta_pkey PRIMARY KEY (ocorrenciadeofertaid);


--
-- Name: ofertadeunidadetematica_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT ofertadeunidadetematica_pkey PRIMARY KEY (ofertadeunidadetematicaid);


--
-- Name: ofertadoresidente_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY ofertadoresidente
    ADD CONSTRAINT ofertadoresidente_pkey PRIMARY KEY (ofertadoresidenteid);


--
-- Name: participacaoematividadeextra_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY participacaoematividadeextra
    ADD CONSTRAINT participacaoematividadeextra_pkey PRIMARY KEY (atividadeextraid, residenteid);


--
-- Name: penalidade_pkey; Type: CONSTRAINT; Schema: med; Owner: solis; Tablespace: 
--

ALTER TABLE ONLY penalidade
    ADD CONSTRAINT penalidade_pkey PRIMARY KEY (penalidadeid);


--
-- Name: preceptoria_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT preceptoria_pkey PRIMARY KEY (preceptorid);


--
-- Name: residente_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT residente_pkey PRIMARY KEY (residenteid);


--
-- Name: statusdaocorrenciadecontrato_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY statusdaocorrenciadecontrato
    ADD CONSTRAINT statusdaocorrenciadecontrato_pkey PRIMARY KEY (statusdaocorrenciadecontratoid);


--
-- Name: tema_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tema
    ADD CONSTRAINT tema_pkey PRIMARY KEY (temaid);


--
-- Name: tipodecargahorariacomplementar_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tipodecargahorariacomplementar
    ADD CONSTRAINT tipodecargahorariacomplementar_pkey PRIMARY KEY (tipodecargahorariacomplementarid);


--
-- Name: tipodepenalidade_pkey; Type: CONSTRAINT; Schema: med; Owner: solis; Tablespace: 
--

ALTER TABLE ONLY tipodepenalidade
    ADD CONSTRAINT tipodepenalidade_pkey PRIMARY KEY (tipopenalidadeid);


--
-- Name: trabalhodeconclusao_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY trabalhodeconclusao
    ADD CONSTRAINT trabalhodeconclusao_pkey PRIMARY KEY (trabalhodeconclusaoid);


--
-- Name: turma_codigoturma_key; Type: CONSTRAINT; Schema: med; Owner: solis; Tablespace: 
--

ALTER TABLE ONLY turma
    ADD CONSTRAINT turma_codigoturma_key UNIQUE (codigoturma);


--
-- Name: turma_pkey; Type: CONSTRAINT; Schema: med; Owner: solis; Tablespace: 
--

ALTER TABLE ONLY turma
    ADD CONSTRAINT turma_pkey PRIMARY KEY (turmaid);


--
-- Name: unidadetematica_pkey; Type: CONSTRAINT; Schema: med; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY unidadetematica
    ADD CONSTRAINT unidadetematica_pkey PRIMARY KEY (unidadetematicaid);


--
-- Name: cargaHorariaComplementar_residenteid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX "cargaHorariaComplementar_residenteid" ON cargahorariacomplementar USING btree (residenteid);


--
-- Name: cargaHorariaComplementar_unidadetematicaid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX "cargaHorariaComplementar_unidadetematicaid" ON cargahorariacomplementar USING btree (unidadetematicaid);


--
-- Name: encontro_ofertadeunidadetematicaid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX encontro_ofertadeunidadetematicaid ON encontro USING btree (ofertadeunidadetematicaid);


--
-- Name: frequencia_encontroid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX frequencia_encontroid ON frequencia USING btree (encontroid);


--
-- Name: frequencia_presenca; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX frequencia_presenca ON frequencia USING btree (presenca);


--
-- Name: frequencia_residenteid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX frequencia_residenteid ON frequencia USING btree (residenteid);


--
-- Name: ocorrenciaDeOferta_ofertadoresidenteid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX "ocorrenciaDeOferta_ofertadoresidenteid" ON ocorrenciadeoferta USING btree (ofertadoresidenteid);


--
-- Name: ocorrenciaDeoferta_status; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX "ocorrenciaDeoferta_status" ON ocorrenciadeoferta USING btree (status);


--
-- Name: ofertadeunidadetematica_unidadetematicaid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX ofertadeunidadetematica_unidadetematicaid ON ofertadeunidadetematica USING btree (unidadetematicaid);


--
-- Name: ofertadoresidente_ofertadeunidadetematicaid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX ofertadoresidente_ofertadeunidadetematicaid ON ofertadoresidente USING btree (ofertadeunidadetematicaid);


--
-- Name: ofertadoresidente_residenteid; Type: INDEX; Schema: med; Owner: postgres; Tablespace: 
--

CREATE INDEX ofertadoresidente_residenteid ON ofertadoresidente USING btree (residenteid);


--
-- Name: trg_registraperiododaturmanoresidente; Type: TRIGGER; Schema: med; Owner: postgres
--

CREATE TRIGGER trg_registraperiododaturmanoresidente BEFORE INSERT OR UPDATE ON residente FOR EACH ROW EXECUTE PROCEDURE public.registraperiododaturmanoresidente();


--
-- Name: trg_verificavagasdaturma; Type: TRIGGER; Schema: med; Owner: postgres
--

CREATE TRIGGER trg_verificavagasdaturma BEFORE INSERT OR UPDATE ON residente FOR EACH ROW EXECUTE PROCEDURE public.verificavagasdaturma();


--
-- Name: centeridfk_78; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar
    ADD CONSTRAINT centeridfk_78 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_79; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador
    ADD CONSTRAINT centeridfk_79 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_80; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfase
    ADD CONSTRAINT centeridfk_80 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_81; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfasedaunidadetematica
    ADD CONSTRAINT centeridfk_81 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_82; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY frequencia
    ADD CONSTRAINT centeridfk_82 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_83; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca
    ADD CONSTRAINT centeridfk_83 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_84; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato
    ADD CONSTRAINT centeridfk_84 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_85; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadeoferta
    ADD CONSTRAINT centeridfk_85 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_86; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente
    ADD CONSTRAINT centeridfk_86 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_87; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY participacaoematividadeextra
    ADD CONSTRAINT centeridfk_87 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_88; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT centeridfk_88 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_89; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT centeridfk_89 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: centeridfk_90; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao
    ADD CONSTRAINT centeridfk_90 FOREIGN KEY (centerid) REFERENCES public.acdcenter(centerid);


--
-- Name: coorientador_personid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador
    ADD CONSTRAINT coorientador_personid_fkey FOREIGN KEY (personid) REFERENCES public.basperson(personid);


--
-- Name: fkcargahorar134326; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar
    ADD CONSTRAINT fkcargahorar134326 FOREIGN KEY (tipodecargahorariacomplementarid) REFERENCES tipodecargahorariacomplementar(tipodecargahorariacomplementarid);


--
-- Name: fkcargahorar597456; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar
    ADD CONSTRAINT fkcargahorar597456 FOREIGN KEY (unidadetematicaid) REFERENCES unidadetematica(unidadetematicaid);


--
-- Name: fkcargahorar871031; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY cargahorariacomplementar
    ADD CONSTRAINT fkcargahorar871031 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fkcoorientad838498; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador
    ADD CONSTRAINT fkcoorientad838498 FOREIGN KEY (personid) REFERENCES public.basphysicalperson(personid);


--
-- Name: fkcoorientad87962; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY coorientador
    ADD CONSTRAINT fkcoorientad87962 FOREIGN KEY (trabalhodeconclusaoid) REFERENCES trabalhodeconclusao(trabalhodeconclusaoid);


--
-- Name: fkencontro647754; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY encontro
    ADD CONSTRAINT fkencontro647754 FOREIGN KEY (temaid) REFERENCES tema(temaid);


--
-- Name: fkencontro767294; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY encontro
    ADD CONSTRAINT fkencontro767294 FOREIGN KEY (ofertadeunidadetematicaid) REFERENCES ofertadeunidadetematica(ofertadeunidadetematicaid);


--
-- Name: fkenfasedaun277432; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfasedaunidadetematica
    ADD CONSTRAINT fkenfasedaun277432 FOREIGN KEY (unidadetematicaid) REFERENCES unidadetematica(unidadetematicaid);


--
-- Name: fkenfasedaun546785; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY enfasedaunidadetematica
    ADD CONSTRAINT fkenfasedaun546785 FOREIGN KEY (enfaseid) REFERENCES enfase(enfaseid);


--
-- Name: fkfrequencia125239; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY frequencia
    ADD CONSTRAINT fkfrequencia125239 FOREIGN KEY (encontroid) REFERENCES encontro(encontroid);


--
-- Name: fkfrequencia967830; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY frequencia
    ADD CONSTRAINT fkfrequencia967830 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fkmembrodaba339099; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca
    ADD CONSTRAINT fkmembrodaba339099 FOREIGN KEY (trabalhodeconclusaoid) REFERENCES trabalhodeconclusao(trabalhodeconclusaoid);


--
-- Name: fkmembrodaba412638; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca
    ADD CONSTRAINT fkmembrodaba412638 FOREIGN KEY (personid) REFERENCES public.basphysicalperson(personid);


--
-- Name: fknucleodaun298548; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleodaunidadetematica
    ADD CONSTRAINT fknucleodaun298548 FOREIGN KEY (nucleoprofissionalid) REFERENCES nucleoprofissional(nucleoprofissionalid);


--
-- Name: fknucleodaun507563; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY nucleodaunidadetematica
    ADD CONSTRAINT fknucleodaun507563 FOREIGN KEY (unidadetematicaid) REFERENCES unidadetematica(unidadetematicaid);


--
-- Name: fkocorrencia52742; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato
    ADD CONSTRAINT fkocorrencia52742 FOREIGN KEY (statusdaocorrenciadecontratoid) REFERENCES statusdaocorrenciadecontrato(statusdaocorrenciadecontratoid);


--
-- Name: fkocorrencia585881; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadeoferta
    ADD CONSTRAINT fkocorrencia585881 FOREIGN KEY (ofertadoresidenteid) REFERENCES ofertadoresidente(ofertadoresidenteid);


--
-- Name: fkocorrencia797792; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ocorrenciadecontrato
    ADD CONSTRAINT fkocorrencia797792 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fkofertadeun725745; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT fkofertadeun725745 FOREIGN KEY (personid) REFERENCES public.basphysicalperson(personid);


--
-- Name: fkofertadeun855386; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT fkofertadeun855386 FOREIGN KEY (unidadetematicaid) REFERENCES unidadetematica(unidadetematicaid);


--
-- Name: fkofertadeun947021; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT fkofertadeun947021 FOREIGN KEY (encerradopor) REFERENCES public.miolo_user(iduser);


--
-- Name: fkofertadore401370; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente
    ADD CONSTRAINT fkofertadore401370 FOREIGN KEY (ofertadeunidadetematicaid) REFERENCES ofertadeunidadetematica(ofertadeunidadetematicaid);


--
-- Name: fkofertadore672250; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadoresidente
    ADD CONSTRAINT fkofertadore672250 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fkparticipac217110; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY participacaoematividadeextra
    ADD CONSTRAINT fkparticipac217110 FOREIGN KEY (atividadeextraid) REFERENCES atividadeextra(atividadeextraid);


--
-- Name: fkparticipac563015; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY participacaoematividadeextra
    ADD CONSTRAINT fkparticipac563015 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fkpreceptori184605; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT fkpreceptori184605 FOREIGN KEY (personid) REFERENCES public.basphysicalperson(personid);


--
-- Name: fkpreceptori387991; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT fkpreceptori387991 FOREIGN KEY (enfaseid) REFERENCES enfase(enfaseid);


--
-- Name: fkpreceptori448329; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT fkpreceptori448329 FOREIGN KEY (nucleoprofissionalid) REFERENCES nucleoprofissional(nucleoprofissionalid);


--
-- Name: fkresidente378258; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente378258 FOREIGN KEY (unidade1) REFERENCES public.basunit(unitid);


--
-- Name: fkresidente378259; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente378259 FOREIGN KEY (unidade2) REFERENCES public.basunit(unitid);


--
-- Name: fkresidente562142; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente562142 FOREIGN KEY (nucleoprofissionalid) REFERENCES nucleoprofissional(nucleoprofissionalid);


--
-- Name: fkresidente622480; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente622480 FOREIGN KEY (enfaseid) REFERENCES enfase(enfaseid);


--
-- Name: fkresidente776513; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente776513 FOREIGN KEY (personid) REFERENCES public.basphysicalperson(personid);


--
-- Name: fkresidente866505; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT fkresidente866505 FOREIGN KEY (subscriptionid) REFERENCES spr.subscription(subscriptionid);


--
-- Name: fktemadaunid130944; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY temadaunidadetematica
    ADD CONSTRAINT fktemadaunid130944 FOREIGN KEY (ofertadeunidadetematicaid) REFERENCES ofertadeunidadetematica(ofertadeunidadetematicaid);


--
-- Name: fktemadaunid644952; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY temadaunidadetematica
    ADD CONSTRAINT fktemadaunid644952 FOREIGN KEY (temaid) REFERENCES tema(temaid);


--
-- Name: fktrabalhode119803; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao
    ADD CONSTRAINT fktrabalhode119803 FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: fktrabalhode610562; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY trabalhodeconclusao
    ADD CONSTRAINT fktrabalhode610562 FOREIGN KEY (orientadorid) REFERENCES public.basphysicalperson(personid);


--
-- Name: membrodabanca_personid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY membrodabanca
    ADD CONSTRAINT membrodabanca_personid_fkey FOREIGN KEY (personid) REFERENCES public.basperson(personid);


--
-- Name: ofertadeunidadetematica_instituicaoexecutora_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT ofertadeunidadetematica_instituicaoexecutora_fkey FOREIGN KEY (instituicaoexecutora) REFERENCES public.baslegalperson(personid);


--
-- Name: ofertadeunidadetematica_personid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY ofertadeunidadetematica
    ADD CONSTRAINT ofertadeunidadetematica_personid_fkey FOREIGN KEY (personid) REFERENCES public.basperson(personid);


--
-- Name: penalidade_preceptorid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade
    ADD CONSTRAINT penalidade_preceptorid_fkey FOREIGN KEY (preceptorid) REFERENCES preceptoria(preceptorid);


--
-- Name: penalidade_residenteid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade
    ADD CONSTRAINT penalidade_residenteid_fkey FOREIGN KEY (residenteid) REFERENCES residente(residenteid);


--
-- Name: penalidade_tipodepenalidadeid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY penalidade
    ADD CONSTRAINT penalidade_tipodepenalidadeid_fkey FOREIGN KEY (tipodepenalidadeid) REFERENCES tipodepenalidade(tipopenalidadeid);


--
-- Name: preceptoria_personid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY preceptoria
    ADD CONSTRAINT preceptoria_personid_fkey FOREIGN KEY (personid) REFERENCES public.basperson(personid);


--
-- Name: residente_instituicaoformadora_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT residente_instituicaoformadora_fkey FOREIGN KEY (instituicaoformadora) REFERENCES public.baslegalperson(personid);


--
-- Name: residente_personid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT residente_personid_fkey FOREIGN KEY (personid) REFERENCES public.basperson(personid);


--
-- Name: residente_turmaid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: postgres
--

ALTER TABLE ONLY residente
    ADD CONSTRAINT residente_turmaid_fkey FOREIGN KEY (turmaid) REFERENCES turma(turmaid);


--
-- Name: tipodepenalidade_emailid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY tipodepenalidade
    ADD CONSTRAINT tipodepenalidade_emailid_fkey FOREIGN KEY (emailid) REFERENCES public.basemail(emailid);


--
-- Name: turma_enfaseid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY turma
    ADD CONSTRAINT turma_enfaseid_fkey FOREIGN KEY (enfaseid) REFERENCES enfase(enfaseid);


--
-- Name: turma_nucleoprofissionalid_fkey; Type: FK CONSTRAINT; Schema: med; Owner: solis
--

ALTER TABLE ONLY turma
    ADD CONSTRAINT turma_nucleoprofissionalid_fkey FOREIGN KEY (nucleoprofissionalid) REFERENCES nucleoprofissional(nucleoprofissionalid);


--
-- PostgreSQL database dump complete
--

