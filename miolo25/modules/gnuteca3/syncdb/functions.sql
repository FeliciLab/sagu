CREATE OR REPLACE FUNCTION compareyearperiod(searchcontent varchar , field1 varchar , field2 varchar) 
RETURNS bool as $BODY$
    DECLARE

        auxF1 varchar;
        auxF2 varchar;
        f1 varchar;
        f2 varchar;

        BEGIN

            IF strpos(searchContent, '-') <= 0 THEN
                RETURN FALSE;
            END IF;

            RAISE NOTICE ' == SearchContent: %, field1: %, field2: %; ', searchContent, field1, field2;

            auxF1   := getSearchContentToYearCompare(split_part(searchContent, '-', 1), FALSE);
            auxF2   := getSearchContentToYearCompare(split_part(searchContent, '-', 2), FALSE);
            f1      := getSearchContentToYearCompare(field1, FALSE);
            f2      := getSearchContentToYearCompare(field2, FALSE);

            IF char_length(auxF1) = 0 THEN
                auxF1 = 0;
            END IF;

            IF char_length(auxF2) = 0 THEN
                auxF2 = date_part('year', now());
            END IF;

            RAISE NOTICE ' == (split 1 % >= field1 % ) AND ( split2 % <= field2 % )', auxF1, f1,  auxF2,  f2;

            RETURN ((auxF1::integer >= f1::integer) AND (auxF2::integer <= f2::integer));

        END;

$BODY$ language plpgsql;

--corrige indicadores, utilizada pela migração FIXME precisa estar aqui?
CREATE OR REPLACE FUNCTION corrigeindicadores() 
RETURNS bool as $BODY$
    DECLARE

        row_data RECORD;

        BEGIN

            FOR row_data IN (SELECT DISTINCT controlNumber, fieldid, indicator1, indicator2 FROM gtcmaterial
                            WHERE subfieldid = '#' AND (char_length(indicator1) > 0 OR char_length(indicator2) > 0) )
            LOOP
                UPDATE  gtcMaterial
                SET     indicator1      = row_data.indicator1,
                        indicator2      = row_data.indicator2
                WHERE   controlNumber   = row_data.controlNumber
                AND     fieldid         = row_data.fieldid;
            END LOOP;

            DELETE FROM gtcmaterial WHERE subfieldid = '#';

            RETURN TRUE;

        END;

$BODY$ language plpgsql;


SELECT * FROM drop_function_if_exists('get_multa','p_fineid int4');
DROP TYPE IF EXISTS TYPE_MULTA;

CREATE TYPE TYPE_MULTA AS
(
    personid bigint,
    loanid int,
    begindate timestamp without time zone,
    value numeric(10,2),
    observation text,
    waspaid boolean,
    fineid int,
    operator varchar,
    allowance boolean,
    allowancejustify text,
    enddate timestamp without time zone,
    returnoperator varchar,
    slipthrough boolean
);


--utilizada pelo sagu para obter as multas
CREATE OR REPLACE FUNCTION get_multa(p_fineid int4) 
RETURNS SETOF type_multa as $BODY$
DECLARE
    v_line TYPE_MULTA;
    v_select text;
    
BEGIN
    
    v_select := 'SELECT C.personid, 
                        B.loanid, 
                        A.begindate, 
                        A.value, 
                        A.observation, 
                        (CASE WHEN finestatusid = 2
                        THEN
                            true
                        ELSE
                            false
                        END) AS waspaid, --foi paga
                        A.fineid,
                        B.loanoperator as operator,
                        (CASE WHEN finestatusid = 4 
                        THEN
                            true
                        ELSE
                            false
                        END) AS allowance, --foi abonada
                        '''' as allowancejustify, 
                        A.enddate,
                        B.returnoperator,
                        (CASE WHEN finestatusid = 3
                        THEN
                            true
                        ELSE
                        false
                        END) AS slipthrough --via boleto
                FROM gtcfine A 
            LEFT JOIN gtcloan B 
                    ON (A.loanid = B.loanid) 
            LEFT JOIN basPerson C 
                    ON (B.personid = C.personid)
                WHERE A.fineid = ' || p_fineid;


    FOR v_line IN EXECUTE v_select
    LOOP
        RETURN NEXT v_line;
    END LOOP;
    
    RETURN;
        
END;
$BODY$ language plpgsql;

--obtem conteúdo relacionado
CREATE OR REPLACE FUNCTION getrelated( int4) 
RETURNS varchar as $BODY$
    DECLARE

        text_output TEXT;
        row_data RECORD;

        BEGIN

            text_output := '';

            FOR row_data IN SELECT DISTINCT relatedcontent FROM gtcdictionaryrelatedcontent
                            WHERE dictionarycontentid = $1 LOOP
                text_output := text_output || row_data.relatedcontent || '<br>';
            END LOOP;

            RETURN text_output;

        END;

$BODY$ language plpgsql;

CREATE OR REPLACE FUNCTION getsearchcontenttoyearcompare( varchar ,  bool) 
RETURNS varchar as $BODY$
    DECLARE

        text_output TEXT;

        BEGIN

            text_output := $1;

            IF char_length(text_output) = 0 AND $2 THEN
                text_output := date_part('year', now());
            END IF;

            text_output := replace(text_output, '?', '0');
            text_output := regexp_replace(text_output, '[^0-9]', '', 'g');

            IF char_length(text_output) = 0 THEN
                RETURN '0';
            END IF;

            RETURN text_output;

        END;

$BODY$ language plpgsql;

SELECT * FROM drop_function_if_exists('getsuggestionmaterial','');
DROP TYPE IF EXISTS type_suggestion_material;
CREATE TYPE type_suggestion_material AS ( idPerson bigint, number int );

--obtem materias para sugestão na minha biblioteca
CREATE OR REPLACE FUNCTION getsuggestionmaterial()
RETURNS SETOF type_suggestion_material as $BODY$
DECLARE
    vclassification RECORD;
    vcontrolNumber RECORD;
BEGIN
    CREATE TEMP TABLE gtcPersonMaterial (personid bigint, controlnumber int); --tabela temporária para relacionar pessoa a número de controle
    
    FOR vclassification IN SELECT A.personId, 
                                A.classificationareaid, 
                                regexp_split_to_table(B.classification, ', ') as classification, 
                                regexp_split_to_table( regexp_split_to_table(coalesce(B.ignoreclassification,''), ', '), ' ,')  as ignoreclassification
                            FROM gtcinterestsarea A
                    INNER JOIN gtcclassificationarea B
                            USING (classificationareaid)
    LOOP
        FOR vcontrolNumber IN SELECT distinct(A.controlNumber) as controlNumber, 
                                            count(B.*) as max 
                                        FROM gtcexemplarycontrol A 
                                INNER JOIN gtcloan B 
                                    USING (itemnumber) 
                                LEFT JOIN gtcMyLibrary C
                                        ON (C.tableid = 'gtcMaterial')  
                                INNER JOIN gtcMaterial D
                                        USING (controlNumber)
                                    WHERE A.controlNumber NOT IN (SELECT controlnumber 
                                                                    FROM gtcloan 
                                                                INNER JOIN gtcExemplaryControl 
                                                                    USING (itemnumber) 
                                                                    WHERE personid = vclassification.personId)
                                        AND (D.fieldid = '090' AND D.subfieldid = 'a')
                                        AND D.content LIKE (vclassification.classification) 
                                        AND D.content NOT LIKE (vclassification.ignoreclassification)
                                        AND controlNumber NOT IN (SELECT tableid::int FROM gtcMyLibrary WHERE tablename = 'gtcMaterial' AND personId = vclassification.personId)                      
                                    GROUP BY 1 ORDER BY 2 DESC LIMIT 1
        LOOP                    
            INSERT INTO gtcPersonMaterial VALUES ( vclassification.personId, vcontrolNumber.controlnumber );

        END LOOP;            
    END LOOP;

    RETURN QUERY SELECT DISTINCT personId, controlNumber FROM gtcPersonMaterial;

    DROP TABLE gtcPersonMaterial;
    
END;
$BODY$ language plpgsql;

--verifica integridade dos dominios
CREATE OR REPLACE FUNCTION gtc_chk_domain(p_domain varchar , p_key varchar) 
RETURNS bool as $BODY$
DECLARE
    v_result boolean;
BEGIN

    --Se o valor do dominio for nulo permite inserir pois, em alguns casos, o campo da tabela em questão pode aceitar NULL.
    IF p_key iS NULL
    THEN
        RETURN TRUE;
    END IF;

    PERFORM * FROM basDomain LIMIT 1;
    IF NOT FOUND
    THEN
        RETURN TRUE; --Caso não haja nenhum dado na basDomain retorna como true. Isso é para resolver o bug do postgres que não ignora os check no dump
    END IF;

    SELECT INTO v_result count(*) > 0
        FROM basDomain
        WHERE domainId = p_domain
            AND key = p_key;

    RETURN v_result;

END;
$BODY$ language plpgsql;

--verifica integridade dos parametros
CREATE OR REPLACE FUNCTION gtc_chk_parameter(p_parameter text) 
RETURNS bool as $BODY$
DECLARE
    v_result boolean;
BEGIN
        SELECT INTO v_result count(*) > 0 FROM basConfig WHERE parameter = p_parameter;
        
        RETURN v_result;
END;
$BODY$ language plpgsql;

-- cria tabela de pesquisa, baseada na view
SELECT * FROM drop_function_if_exists('gtcfnc_updatesearchmaterialviewtable','');
CREATE OR REPLACE FUNCTION gtcfnc_updatesearchmaterialviewtable() 
RETURNS trigger as $BODY$
            BEGIN

                DELETE FROM gtcSearchMaterialView;
                INSERT INTO gtcSearchMaterialView SELECT * FROM searchMaterialView;

                DELETE FROM gtcSearchTableUpdateControl;
                INSERT INTO gtcSearchTableUpdateControl (lastUpdate) values (now());

                RETURN OLD;
            END;
        $BODY$ language plpgsql;

-- cria tabela de pesquisa, baseada na view
CREATE OR REPLACE FUNCTION gtcfnc_updatesearchmaterialviewtablebool() 
RETURNS bool as $BODY$
DECLARE
    lastUpdate_ BOOLEAN;
BEGIN
    --Havia problemas de corromper o indice. Então sempre exclui o indice e recria
    DROP INDEX index_gtcsearchmaterialview_controlnumber;

    DELETE FROM gtcSearchMaterialView;

    CREATE INDEX index_gtcsearchmaterialview_controlnumber ON gtcSearchMaterialView(controlnumber);

    INSERT INTO gtcSearchMaterialView SELECT * FROM searchMaterialView;

    DELETE FROM gtcSearchTableUpdateControl;
    INSERT INTO gtcSearchTableUpdateControl (lastUpdate) values (now());

    RETURN TRUE;
END;
$BODY$ language plpgsql;

--atualiza pai e filho chamada via trigger
CREATE OR REPLACE FUNCTION gtcfncupdatematerialson() 
RETURNS trigger as $BODY$
    DECLARE

DECLARE
 
         row_data    RECORD;
         row_data1   RECORD;
 
         fatherCategory  char(2);
         fatherLevel     char(1);
 
         loopX       int;
         tag         char(5);
         fieldS      char(3);
         subFieldS   char(1);
 
         currentControlNumber    	     int;
         currentFieldId          	     char(3);
         currentSubFieldId       	     char(1);
         currentContent          	     text;
         currentSearchContent    	     text;
	 currentSearchContentForSearchModule text;
         currentLine             	     int;
 
     BEGIN
 
         IF (TG_OP != 'DELETE') THEN
 
             currentControlNumber      	            := NEW.controlnumber;
             currentFieldId        		    := NEW.fieldid;
             currentSubFieldId     		    := NEW.subfieldid;
             currentContent        		    := NEW.content;
             currentSearchContent                   := NEW.searchcontent;
             currentSearchContentForSearchModule    := NEW.searchcontentforsearchmodule;
             currentLine             		    := NEW.line;
 
         ELSE
 
             currentControlNumber    		    := OLD.controlnumber;
             currentFieldId         		    := OLD.fieldid;
             currentSubFieldId       	    	    := OLD.subfieldid;
             currentContent          		    := OLD.content;
             currentSearchContent   		    := OLD.searchcontent;
             currentSearchContentForSearchModule    := OLD.searchcontentforsearchmodule;
             currentLine             		    := OLD.line;
 
         END IF;
 
         /**
         * BUSCA CATEGORIA E LEVEL DO PAI
         */
         FOR row_data IN (SELECT  category, level FROM  gtcMaterialControl WHERE  controlnumber = currentControlNumber)
         LOOP
             fatherCategory  := row_data.category;
             fatherLevel     := row_data.level;
         END LOOP;
 
         FOR row_data1 IN
         (
             SELECT  LK.tag, LK.tagson, MC.controlnumber
             FROM  gtcmaterialcontrol MC
         INNER JOIN  gtclinkoffieldsbetweenspreadsheets LK
                 ON  (MC.category = LK.categoryson AND MC.level = LK.levelson )
             WHERE  LK.category         = fatherCategory
             AND  LK.level            = fatherLevel
             AND  LK.tag      like    ('%' || currentFieldId || '.' || currentSubFieldId || '%')
             AND  MC.controlnumberfather = currentControlNumber
             AND  LK.type = '2'
         )
         LOOP
 
             IF (strpos(row_data1.tagson, ',') = 0) THEN
                 row_data1.tagson = row_data1.tagson || ',';
             END IF;
 
             loopX := 1;
 
             LOOP
 
                 tag = split_part(row_data1.tagson, ',', loopX);
 
                 IF char_length(tag) = 0 THEN
   EXIT;
                 END IF;
 
                 fieldS      := split_part(tag, '.', 1);
                 subFieldS   := split_part(tag, '.', 2);
 
                 IF (TG_OP = 'DELETE') THEN
 
   DELETE FROM gtcMaterial
   WHERE controlnumber    = row_data1.controlnumber
   AND fieldid          = fieldS
   AND subfieldid       = subFieldS
   AND line             = currentLine;
 
                 ELSIF (TG_OP = 'UPDATE') THEN
 
   UPDATE gtcMaterial
   SET content                      = currentContent,
       searchcontent                = currentSearchContent,
       searchcontentforsearchmodule = currentSearchContentForSearchModule
   WHERE controlnumber              = row_data1.controlnumber
   AND fieldid                      = fieldS
   AND subfieldid                   = subFieldS
   AND line                         = currentLine;
 
                 ELSIF (TG_OP = 'INSERT') THEN
 
   INSERT INTO gtcMaterial
       (content, searchcontent, searchcontentforsearchmodule, controlnumber, fieldid, subfieldid, line)
   VALUES
       (currentContent, currentSearchContent, currentSearchContentForSearchModule, row_data1.controlnumber, fieldS, subFieldS, currentLine);
 
                 END IF;
 
                 loopX := loopX + 1;
 
             END LOOP;
 
         END LOOP;
 
         RETURN NULL;
     END;
$BODY$ language plpgsql;

--checagem utilizada pelas ajudas
CREATE OR REPLACE FUNCTION gtcgnccheckhelp() 
RETURNS trigger as $BODY$
DECLARE
    v_result boolean;
BEGIN

    IF ( TG_OP = 'UPDATE' )
    THEN
        IF ( (NEW.form = OLD.form) )
        THEN
            IF ( NEW.subform IS NOT NULL )
            THEN
                IF ( NEW.subform = OLD.subform )
                THEN
                    RETURN NEW;
                END IF;
            ELSE
                RETURN NEW;
            END IF; 
        END IF;
        
        RAISE EXCEPTION 'Não é possível alterar o formulário deste registro.';    
    ELSE
        IF (NEW.subform IS NULL )
        THEN
            SELECT into v_result count(*) = 0
            FROM gtcHelp
            WHERE form = NEW.form
                AND subform IS NULL;
        ELSE
        
            SELECT into v_result count(*) = 0 
            FROM gtcHelp
            WHERE form = NEW.form
                AND subform = NEW.subform;
        END IF;
        
        IF ( v_result )
        THEN
            RETURN NEW;
        END IF;
        
        RAISE EXCEPTION 'Já existe um registo para este formulário.';
    END IF;
    
    RETURN NULL;    

END;
$BODY$ language plpgsql;


--função utilizada pelo Sagu para pagar uma multa
CREATE OR REPLACE FUNCTION upd_pagar_multa(p_codigo_da_multa int4 , p_operador varchar) 
RETURNS bool as $BODY$
DECLARE
    v_select varchar;
    v_line gtcFine;
BEGIN
    -- Funcao para pagar uma multa em aberto. Será utilizado pelo SAGU

    SELECT INTO v_line * from gtcFine where fineId = p_codigo_da_multa;

    IF ( v_line.fineStatusId = 2 OR v_line.fineStatusId = 3 )
    THEN
        raise exception 'Não foi possível pagar a multa % pois ela está como paga.', v_line.fineId;
        return FALSE;
    END IF;

    IF (v_line.fineStatusId = 4)
    THEN
        raise exception 'Não foi possível pagar a multa % pois ela está como abonada.', v_line.fineId;
        return FALSE;
    END IF;

    UPDATE gtcFine SET fineStatusId = 2, enddate = now() where fineId = p_codigo_da_multa;
    INSERT INTO gtcFineStatusHistory (fineid, finestatusid, date, operator) VALUES (p_codigo_da_multa, 2, now(), p_operador);

    return true;
END;
$BODY$ language plpgsql;

--prepara search content para índice topográfico 090.a
CREATE OR REPLACE FUNCTION preparetopographicindex(content character varying, complement character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
DECLARE
    result varchar;
    resultAux varchar;
    number integer;
    content_v varchar;

    count int;
    new_content varchar;
    letter char;
BEGIN
    --tira acentos e converte pra minusculas e adiciona | como terminador de string.
    result := lower ( unaccent ( trim( content ) ) ) || '|';

    -- Separa somente números
    new_content := '';
    count := 0;
    number := 0;

    WHILE (count < char_length(content))
    LOOP
        -- Obtém caractere.
        letter := substring(content, count+1, 1);

        -- Testa se caractere é numérico.
        IF letter ~ '^[0-9]$' AND char_length(new_content) < 9
        THEN
            new_content := new_content || letter;
        ELSE
            IF char_length(new_content) > 0
            THEN
                 number := new_content::integer;
                EXIT;
            END IF;
        END IF;

        count = count + 1;
    END LOOP;

    content_v := substring(content from position(number::text in content));

    /**
        Exemplo de precedência que deve ser levado em conta, vide #12268 :
        658.012.4+657 -> + vem primeiro
        658.012.4/.5 -> / vem segundo
        658.012.4 -> Numeros inteiros em terceiro
        658.012.4:266 -> : depois dos números inteiros
    */
    result := translate( result, '+/|:=("*-.\'0123456789', 'ABCDEFGHIJKMNOPQRSTUV');
    --tratamento da excessão (0 => EI deve vir após (1/9 => EJ/9
    --result := replace( result, 'FJ','FT');

    --tratamento da exceção "(=" vai depois de ([0-9].
    result := replace( result, 'FE','FW');

    --Trata a excessão quando o termo >= 820 e < 900 o (1/9 => F[KLMNOPQRS] vai depois do . => I (J))
    IF (number>= 82 AND number < 90) OR (number >= 820 and number < 900 )
    THEN
        IF ( (content_v ~ E'^(([0-9]{3})|([0-9]{2}))\\([1-9].*$') OR (content_v ~ E'^(([0-9]{3})|([0-9]{2}))\\.[0-9]{1}\\([1-9].*$') )
        THEN
             resultAux = substring(result FROM position(')' in result));
             result = substring(result FROM 0 FOR position(')' in result));

             result := regexp_replace(result, 'F([NOPQRSTUV])', E'L\\1','g');
             result := result || resultAux;

         END IF;
    END IF;

    -- Substitui o ')' por um caractere de baixa prioridade.
    result = translate( result, ')', '5');

    --adiciona F na frente de cada caracter minusculo a fim de priorizar alguns caracteres
    result := trim( regexp_replace( result,'([a-z])',E'GX\\1','g') );

    --caso tenha complemento concatena
    IF complement IS NOT NULL AND result <> ''
    THEN
        result := result || '@' || complement;
    END IF;

    return result;
END;
$_$;


SELECT * FROM drop_function_if_exists('sea_bibliography_data','p_controlnumber int4 , p_content varchar , p_libraryunit int4 , p_tags varchar');
DROP TYPE IF EXISTS TYPE_BIBLIOGRAPHY_DATA;
CREATE TYPE TYPE_BIBLIOGRAPHY_DATA AS
(
    controlnumber int,
    fieldid varchar,
    subfieldid varchar,
    content varchar
);

--busca por bibliografias, utilizado pelo Sagu
CREATE OR REPLACE FUNCTION sea_bibliography_data(p_controlnumber int4 , p_content varchar , p_libraryunit int4 , p_tags varchar) 
RETURNS SETOF type_bibliography_data as $BODY$
DECLARE
    v_line TYPE_BIBLIOGRAPHY_DATA;
    v_select text;
    
BEGIN
    
    v_select  = 'SELECT DISTINCT controlnumber
                        FROM gtcmaterial 
                        WHERE subfieldid <> ''#''';


    IF p_controlnumber IS NOT NULL
    THEN
        v_select = v_select || ' AND controlnumber = ' || p_controlnumber;
    END IF;
                                            
    IF p_content IS NOT NULL 
    THEN
        v_select = v_select || ' AND lower( unaccent( searchcontent ) ) LIKE lower( unaccent( ''%' || p_content || '%'' ) )';
    END IF;

                
    v_select = 'SELECT controlnumber,
                    fieldid,
                    subfieldid,
                    content
                FROM gtcmaterial
                WHERE controlnumber IN ( ' || v_select || ')';
                
    IF p_tags IS NOT NULL 
    THEN
        v_select = v_select || ' AND fieldid || ''.'' || subfieldid IN ( '''|| replace(p_tags, ',', ''',''') || ''')';
    END IF;            
            
    v_select = v_select || ' ORDER BY controlnumber, fieldid, subfieldid';        
                        
    FOR v_line IN EXECUTE v_select
    LOOP
        RETURN NEXT v_line;
    END LOOP;
    
    RETURN;
        
END;
$BODY$ language plpgsql;


SELECT * FROM drop_function_if_exists('sea_multas_em_aberto','p_codigo_da_pessoa int4');
DROP TYPE IF EXISTS type_multas_em_aberto;
CREATE TYPE type_multas_em_aberto AS (codigodamulta integer, codigodoemprestimo integer, valor numeric(10,2), observacao text, datahora timestamp );

--retornar multas em aberto de uma pessoa, utilizado pelo sagu
CREATE OR REPLACE FUNCTION sea_multas_em_aberto(p_codigo_da_pessoa int4) 
RETURNS SETOF type_multas_em_aberto as $BODY$
DECLARE
    v_select varchar;
    v_line type_multas_em_aberto;
BEGIN

    -- Funcao para buscar as multas em aberto. Será utilizado pelo SAGU
    v_select := 'SELECT F.fineId as codigodamulta , F.loanId as codigodoemprestimo, F.value as valor, F.observation as observacao, F.begindate as datahora FROM gtcFine F INNER JOIN gtcLoan L ON (F.loanId = L.loanId) WHERE L.personId = ' || p_codigo_da_pessoa || ' AND F.fineStatusId = 1';

    FOR v_line IN EXECUTE v_select
    LOOP
        RETURN NEXT v_line;
    END LOOP;
END;
$BODY$ language plpgsql;

--tira completamente os acentos de uma string
CREATE OR REPLACE FUNCTION unaccent(text)
  RETURNS text AS
$BODY$
BEGIN
     RETURN translate($1, 'ãáàâãäéèêëíìïóòôõöúùûüÁÀÂÃÄÉÈÊËÍÌÏÓÒÔÕÖÚÙÛÜçÇñÑ', 'aaaaaaeeeeiiiooooouuuuAAAAAEEEEIIIOOOOOUUUUcCnN');
END;
$BODY$
LANGUAGE 'plpgsql' IMMUTABLE;

--Atualiza a multa, utilizada pelo Sagu
CREATE OR REPLACE FUNCTION upd_gerar_boleto_multa(p_codigo_da_multa int4 , p_operador varchar) 
RETURNS bool as $BODY$
DECLARE
    v_select varchar;
    v_line gtcFine;
BEGIN

    -- Funcao para pagar uma multa em aberto. Será utilizado pelo SAGU

    SELECT INTO v_line * from gtcFine where fineId = p_codigo_da_multa;

    IF ( v_line.fineStatusId = 2 )
    THEN
        raise exception 'Não foi possível gerar o boleto da multa % pois ela está como paga.', v_line.fineId;
        return FALSE;
    END IF;

    IF ( v_line.fineStatusId = 4 )
    THEN
        raise exception 'Não foi possível gerar o boleto da multa % pois ela está como abonada.', v_line.fineId;
        return FALSE;
    END IF;

    IF ( v_line.fineStatusId = 3 )
    THEN
        raise exception 'Não foi possível gerar o boleto da multa % pois ela está como paga via boleto.', v_line.fineId;
        return FALSE;
    END IF;

    UPDATE gtcFine SET fineStatusId = 3, enddate = now() where fineId = p_codigo_da_multa;
    INSERT INTO gtcFineStatusHistory (fineid, finestatusid, date, operator) VALUES (p_codigo_da_multa, 3, now(), p_operador);

    return true;
END;
$BODY$ language plpgsql;

--
CREATE OR REPLACE FUNCTION obterQuantPenalidades(p_personid integer)
RETURNS bigint AS
/*************************************************************************************
  NAME: obterQuantPenalidades
  DESCRIPTION: Obtém a quantidade de penalidades que a pessoa possui no gnuteca.
**************************************************************************************/
$BODY$ 
BEGIN
    RETURN ( SELECT COUNT(*)
               FROM gtcpenalty
              WHERE coalesce( penaltyEndDate > now(), penaltyEndDate IS NULL )
                AND personid = p_personid );
END; 
$BODY$ 
language plpgsql;

--
CREATE OR REPLACE FUNCTION obterQuantMultas(p_personid integer)
RETURNS bigint AS
/*************************************************************************************
  NAME: obterQuantMultas
  DESCRIPTION: Obtém a quantidade de multas que a pessoa possui no gnuteca.
**************************************************************************************/
$BODY$ 
BEGIN
    RETURN ( SELECT COUNT(*)
               FROM gtcfine f
          LEFT JOIN gtcloan l
                 ON f.loanid = l.loanid
              WHERE finestatusid = ( SELECT value 
                                       FROM basconfig 
                                      WHERE parameter = 'ID_FINESTATUS_OPEN' )::int
                AND personid = p_personid );
END; 
$BODY$ 
language plpgsql;

--
CREATE OR REPLACE FUNCTION obterQuantEmprestimos(p_personid integer)
RETURNS bigint AS
/*************************************************************************************
  NAME: obterQuantEmprestimos
  DESCRIPTION: Obtém a quantidade de empréstimos que a pessoa possui no gnuteca.
**************************************************************************************/
$BODY$ 
BEGIN
    RETURN ( SELECT count(*)
               FROM gtcloan
              WHERE personid = p_personid
                AND returnforecastdate::date < now()::date
                AND returndate is null);
END; 
$BODY$ 
language plpgsql;


SELECT * FROM drop_function_if_exists('gtcobterrestricoes','person int4');
SELECT * FROM drop_function_if_exists('gtcobterrestricoesparasagu','person int4');
DROP TYPE IF EXISTS type_obter_restricoes; 
CREATE TYPE type_obter_restricoes AS ( tipo text, quantidade bigint );

--retorna as restrições do usuário na instituição
CREATE OR REPLACE FUNCTION gtcObterRestricoes(p_personid integer) 
RETURNS SETOF type_obter_restricoes AS
/*************************************************************************************
  NAME: gtcObterRestricoes
  DESCRIPTION: Verifica se a pessoa possui pendências no Gnuteca.
**************************************************************************************/
$BODY$
DECLARE
  v_sql varchar;
  v_parameter varchar;
BEGIN
    v_sql := '';
    SELECT INTO v_parameter value FROM basconfig WHERE parameter = 'PENDENCIAS_NADA_CONSTA';
    
    IF strpos(v_parameter, 'P') > 0
    THEN
        v_sql := v_sql || 'SELECT ''Penalidade''::text AS "tipo",
                                  obterQuantPenalidades(' || p_personid || ') AS "quantidade"';
    END IF;

    IF strpos(v_parameter, 'M') > 0
    THEN
        IF length(v_sql) > 0
        THEN
            v_sql := v_sql || ' UNION ';
        END IF;
 
        v_sql := v_sql || 'SELECT ''Multas''::text AS "tipo",
                                  obterQuantMultas(' || p_personid || ') AS "quantidade"';
    END IF;

    IF strpos(v_parameter, 'E') > 0
    THEN
        IF length(v_sql) > 0
        THEN
            v_sql := v_sql || ' UNION ';
        END IF;
	
        v_sql := v_sql || 'SELECT ''Empréstimos atrasados''::text AS "tipo",
                                  obterQuantEmprestimos(' || p_personid || ') AS "quantidade"';
    END IF;

    IF length(v_sql) = 0
    THEN
        v_sql := v_sql || ' SELECT ''Verifique o parâmetro PENDENCIAS_NADA_CONSTA. O parâmetro não está configurado corretamente.''::text, 1::bigint';
     END IF;

    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
language plpgsql;

--retorna se o usuário tem ou não alguma pendência na instituição
CREATE OR REPLACE FUNCTION gtcnadaconsta(person int4) 
RETURNS bool as $BODY$
DECLARE
    v_result boolean;
BEGIN
    SELECT into v_result SUM(quantidade) = 0 FROM gtcObterRestricoes(person);
    
    RETURN v_result;
END; 
$BODY$ language plpgsql;

--REprepara o conteúdo de pesquisa de toda a base
CREATE OR REPLACE FUNCTION prepareallsearchcontent() 
RETURNS bool as $BODY$
DECLARE
    vClassification varchar;
    vDate varchar;
BEGIN
    vClassification := value FROM basconfig WHERE parameter = 'MARC_CLASSIFICATION_TAG';
    vDate := value FROM basconfig WHERE parameter = 'CATALOGUE_DATE_FIELDS';

    --atualiza o searchContent de todos materiais para unaccent, conforme unaccent do PHP troca a + pelo A
    UPDATE gtcmaterial SET searchcontent = trim( upper( translate( unaccent( content ) ,'+', 'A') ) ),
                           searchcontentforsearchmodule = trim( upper( translate( unaccent( content ) ,'+', 'A') ) );

    -- atualiza as tags 090.a e etc considerando a preferencia MARC_CLASSIFICATION_TAG
    UPDATE gtcmaterial EM SET searchContent = prepareTopographicIndex
        ( content,
            ( SELECT content
            FROM gtcmaterial IM
            WHERE fieldid = '090'
                AND subfieldid = 'b'
                AND line = 0
                AND EM.controlnumber = IM.controlNumber
            )
        ),
        searchcontentforsearchmodule = preparetopographicindexsearch
        ( content,
            ( SELECT content
            FROM gtcmaterial IM
            WHERE fieldid = '090'
                AND subfieldid = 'b'
                AND line = 0
                AND EM.controlnumber = IM.controlNumber
            )
        )
        
    WHERE fieldid || '.' || subfieldid in (  SELECT regexp_split_to_table( vClassification, ',' ) );

    -- atualiza as tags de data. Observação: na 3.2 tem que ser dd/mm/yyyy
    UPDATE gtcmaterial SET searchContent = to_char( content::date, 'YYYY-mm-dd'),
                           searchcontentforsearchmodule = to_char( content::date, 'YYYY-mm-dd')
    WHERE fieldid || '.' || subfieldid in ( SELECT regexp_split_to_table( vDate , ',') );

    return true;
END;
$BODY$ language plpgsql;

CREATE OR REPLACE FUNCTION preparesearchcontent(tag varchar , content varchar , complement varchar) 
RETURNS varchar as $BODY$
DECLARE
    isClassification integer;
    isDate integer;
BEGIN
    -- Controla casos onde a tag vem nula ou somente com ponto.
    IF length( tag ) > 1 
    THEN
        isClassification = position( tag in ( SELECT value FROM basconfig WHERE parameter = 'MARC_CLASSIFICATION_TAG' ) );

        IF isClassification > 0
        THEN
            return prepareTopographicIndex( content,complement );
        END IF;

        isDate = position( tag in ( SELECT value FROM basconfig WHERE parameter = 'CATALOGUE_DATE_FIELDS' ) );

        IF isDate > 0
        THEN
            return to_char( content::date, 'YYYY-mm-dd');
            --return to_char( content::date, 'dd/mm/yyyy'); --na 3.2 tem que ser dd/mm/yyyy
        END IF;
    END IF;

    return trim( upper( unaccent( translate( content ,'+', 'A') ) ) );
END;
$BODY$ language plpgsql;

CREATE OR REPLACE FUNCTION preparesearchcontentforsearchmodule(tag varchar , content varchar , complement varchar) 
RETURNS varchar as $BODY$
DECLARE
    isClassification integer;
    isDate integer;
BEGIN
    -- Controla casos onde a tag vem nula ou somente com ponto.
    IF length( tag ) > 1 
    THEN
        isClassification = position( tag in ( SELECT value FROM basconfig WHERE parameter = 'MARC_CLASSIFICATION_TAG' ) );

        IF isClassification > 0
        THEN
            return preparetopographicindexsearch( content,complement );
        END IF;

        isDate = position( tag in ( SELECT value FROM basconfig WHERE parameter = 'CATALOGUE_DATE_FIELDS' ) );

        IF isDate > 0
        THEN
            return to_char( content::date, 'YYYY-mm-dd');
            --return to_char( content::date, 'dd/mm/yyyy'); --na 3.2 tem que ser dd/mm/yyyy
        END IF;
    END IF;

    return trim( upper( unaccent( translate( content ,'+', 'A') ) ) );
END;
$BODY$ language plpgsql;

CREATE OR REPLACE FUNCTION preparetopographicindexsearch(content character varying, complement character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
DECLARE
    result varchar;
    resultAux varchar;
    number integer;
    content_v varchar;

    count int;
    new_content varchar;
    letter char;
BEGIN
    --tira acentos e converte pra minusculas e adiciona | como terminador de string.
    result := lower ( unaccent ( trim( content ) ) );

    -- Separa somente números
    new_content := '';
    count := 0;
    number := 0;

    WHILE (count < char_length(content))
    LOOP
        -- Obtém caractere.
        letter := substring(content, count+1, 1);

        -- Testa se caractere é numérico.
        IF letter ~ '^[0-9]$' AND char_length(new_content) < 9
        THEN
            new_content := new_content || letter;
        ELSE
            IF char_length(new_content) > 0
            THEN
                 number := new_content::integer;
                EXIT;
            END IF;
        END IF;

        count = count + 1;
    END LOOP;

    content_v := substring(content from position(number::text in content));

    /**
        Exemplo de precedência que deve ser levado em conta, vide #12268 :
        658.012.4+657 -> + vem primeiro
        658.012.4/.5 -> / vem segundo
        658.012.4 -> Numeros inteiros em terceiro
        658.012.4:266 -> : depois dos números inteiros
    */
    result := translate( result, '+/|:=("*-.\'0123456789', 'ABCDEFGHIJKMNOPQRSTUV');
    --tratamento da excessão (0 => EI deve vir após (1/9 => EJ/9
    --result := replace( result, 'FJ','FT');

    --tratamento da exceção "(=" vai depois de ([0-9].
    result := replace( result, 'FE','FW');

    --Trata a excessão quando o termo >= 820 e < 900 o (1/9 => F[KLMNOPQRS] vai depois do . => I (J))
    IF (number>= 82 AND number < 90) OR (number >= 820 and number < 900 )
    THEN
        IF ( (content_v ~ E'^(([0-9]{3})|([0-9]{2}))\\([1-9].*$') OR (content_v ~ E'^(([0-9]{3})|([0-9]{2}))\\.[0-9]{1}\\([1-9].*$') )
        THEN
             resultAux = substring(result FROM position(')' in result));
             result = substring(result FROM 0 FOR position(')' in result));

             result := regexp_replace(result, 'F([NOPQRSTUV])', E'L\\1','g');
             result := result || resultAux;

         END IF;
    END IF;

    -- Substitui o ')' por um caractere de baixa prioridade.
    result = translate( result, ')', '5');

    --adiciona F na frente de cada caracter minusculo a fim de priorizar alguns caracteres
    result := trim( regexp_replace( result,'([a-z])',E'GX\\1','g') );

    --caso tenha complemento concatena
    IF complement IS NOT NULL AND result <> ''
    THEN
        result := result || '@' || complement;
    END IF;

    return result;
END;
$_$;

--retorna um cpf com máscara
CREATE OR REPLACE FUNCTION maskcpf(p_cpf text) 
RETURNS text as $BODY$
DECLARE
    v_unmasked text;
BEGIN

    SELECT INTO v_unmasked unmaskCPF( p_cpf ) ;

    RETURN substring( v_unmasked from 1 for 3 ) || '.' 
        || substring( v_unmasked from 4 for 3 ) || '.' 
        || substring( v_unmasked from 7 for 3 ) || '-' 
        || substring( v_unmasked from 10 for 2 );
END;
 $BODY$ language plpgsql;

--retorna um cpf sem máscara
CREATE OR REPLACE FUNCTION unmaskcpf(p_cpf text) 
RETURNS text as $BODY$
DECLARE
BEGIN
    RETURN lpad( regexp_replace( p_cpf, '[^0-9]', '', 'gi'),  11, '0');
END;
 $BODY$ language plpgsql;


--atualiza todas sequencias do banco
CREATE OR REPLACE FUNCTION updatesequences() 
RETURNS bool as $BODY$
/**
  NAME: updateSequences
  PURPOSE: Atualizar todas as sequences do banco para os valores de acordo com a
  tabela que gerenciam, fazendo SELECT MAX(coluna_gerenciada) FROM tabela.

  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       09/07/2011 Alex Smith        1. Função criada.
*/
DECLARE
    v_row RECORD;
BEGIN
    FOR v_row IN SELECT DISTINCT 'SELECT setval(''' || REGEXP_REPLACE(pg_catalog.pg_get_expr(d.adbin, d.adrelid), '(^.*''([^'']*)[''].*$)',E'\\2') || ''', COALESCE((SELECT MAX(' || a.attname || ') FROM ' || n.nspname || '.' || c.relname || '), 1));' AS sqlToRun
                   FROM pg_catalog.pg_attribute a
             INNER JOIN pg_catalog.pg_attrdef d
                     ON d.adrelid = a.attrelid
                    AND d.adnum = a.attnum
                    AND a.atthasdef
             INNER JOIN pg_class c
                     ON a.attrelid = c.oid
              LEFT JOIN pg_catalog.pg_namespace n
                     ON n.oid = c.relnamespace
                  WHERE a.attnum > 0
                    AND NOT a.attisdropped
                    AND a.attislocal -- somente campos nao herdados
                    AND d.adsrc like '%nextval%' AND atttypid <> 1043 --somente campos que não são string
               ORDER BY 1
    LOOP
        RAISE NOTICE '%', v_row.sqlToRun;
        EXECUTE v_row.sqlToRun;
    END LOOP;

    RETURN TRUE;
END;
 $BODY$ language plpgsql;

--Remove a funcao de proximo numero do relatorio porque a partir de agora ela retornara um bigint, isso gera problema de retorno se existir uma que retorne um tipo diferente.
SELECT * FROM drop_function_if_exists('fnc_nextitemnumber', 'integer');
--Remove função que verifica integridade de dominios
--SELECT * FROM drop_function_if_exists('gtc_chk_domain','varchar,varchar');

--cria a mesma função que faz a mesma coisa que a gtc_chk_domain mas com nome bas_chk_domain para compatibilizar com o sagu
CREATE OR REPLACE FUNCTION bas_chk_domain(p_domain varchar , p_key varchar) 
RETURNS bool as $BODY$
DECLARE
    v_result boolean;
BEGIN

    --Se o valor do dominio for nulo permite inserir pois, em alguns casos, o campo da tabela em questão pode aceitar NULL.
    IF p_key IS NULL
    THEN
        RETURN TRUE;
    END IF;

    PERFORM * FROM basDomain LIMIT 1;
    IF NOT FOUND
    THEN
        RETURN TRUE; --Caso não haja nenhum dado na basDomain retorna como true. Isso é para resolver o bug do postgres que não ignora os check no dump
    END IF;

    SELECT INTO v_result count(*) > 0
        FROM basDomain
        WHERE domainId = p_domain
            AND key = p_key;

    RETURN v_result;

END;
$BODY$ language plpgsql;


--VERIFICA SE EXISTE UMA TABELA
CREATE OR REPLACE FUNCTION existTable( nomeTabela varchar ) 
RETURNS bool as $BODY$
DECLARE
    result boolean;
BEGIN
    result = false;             
   IF ( select count(*) > 0 from pg_catalog.pg_tables  WHERE tablename = nomeTabela )
   THEN
   result = true; 
   END IF; 
    RETURN result;
 END;
$BODY$ language plpgsql;

--VERIFICA SE A PESSOA INFORMADA POSSUI CONTRATO
CREATE OR REPLACE FUNCTION existContract( person bigint ) 
RETURNS bool as $BODY$
DECLARE
    result boolean;
BEGIN

   IF ( select count(*) > 0 from acdcontract where personid = person )
   THEN
   result = true; 
    ELSE
    result = false;
   END IF; 
    RETURN result;
 END;
$BODY$ language plpgsql;

--FUNÇÃO QUE FAZ A UNIÃO DE PESSOAS
CREATE OR REPLACE FUNCTION gtcPersonUnion(stayPerson bigint, outPerson bigint) 
RETURNS bool as $BODY$
DECLARE
    BEGIN

        PERFORM * FROM basPerson WHERE personId = stayPerson LIMIT 1;
        IF NOT FOUND
        THEN
            RAISE EXCEPTION 'Pessoa % não existe.' , stayPerson;
        END IF;
        PERFORM * FROM basPerson WHERE personId = outPerson;
        IF NOT FOUND
        THEN
            RAISE EXCEPTION 'Pessoa % não existe.' , outPerson;
        END IF;
        IF stayPerson = outPerson 
        THEN
            RAISE EXCEPTION 'É necessário escolher duas pessoas diferentes.';
        END IF;

        --UNIÃO.
        -- empréstimos
        UPDATE gtcLoan SET personId = stayPerson WHERE personId = outPerson;
        -- emprésimos entre biblioteca
        UPDATE gtcLoanBetweenLibrary SET personId = stayPerson WHERE personId = outPerson; 
        -- avaliação
        UPDATE gtcMaterialEvaluation SET personId = stayPerson WHERE personId = outPerson;
        -- mINha biblioteca
        UPDATE gtcMylibrary SET personId = stayPerson WHERE personId = outPerson;
        -- penalidades
        UPDATE gtcPenalty SET personId = stayPerson WHERE personId = outPerson;
        -- solicitação de compras
        UPDATE gtcPurchaseRequest SET personId = stayPerson WHERE personId = outPerson;
        -- solicitação de alteração de estado do exempla (congelamento)
        UPDATE gtcRequestChangeExemplaryStatus SET personId = stayPerson WHERE personId = outPerson;
        -- reservas
        UPDATE gtcReserve SET personId = stayPerson WHERE personId = outPerson;
        --CONTROLE DE NOTIFICAÇÃO DE E-MAIL DE REQUISIÇÃO.
        UPDATE gtcEmailControlNotifyAquisition SET personId = stayPerson WHERE personId = outPerson;
        --MANTER A DA PESSOA SELECIONADA, REMOVER AS OUTRAS.
        DELETE FROM gtcPersonConfig WHERE personId = outPerson;

        --TELEFONES
        INSERT INTO basPhone 
                    (personId, 
                    type ,phone)    
            (SELECT stayPerson,     
                    type, 
                    phone 
               FROM basPhone 
              WHERE personId = outPerson 
                AND type 
                 IN ( SELECT type 
               FROM basPhone 
              WHERE personId = outPerson EXCEPT
             SELECT type 
               FROM basPhone 
              WHERE personId = stayPerson)
            );

         DELETE FROM basPhone where personId = outPerson;

        --AREAS DE INTERESSE.
        INSERT INTO gtcInterestsArea
                    (personId,
                    classificationAreaId )
            (SELECT stayPerson,
                    classificationAreaId
               FROM gtcINterestsarea
              WHERE personId = outPerson
                AND classificationAreaId
             NOT IN ( SELECT classificationAreaId
               FROM gtcInterestsArea
              WHERE personId = stayPerson)
             );

        DELETE FROM gtcInterestsarea WHERE personId = outPerson;

        --LIGAÇÃO ENTRE PESSOAS
        INSERT INTO baspersonlink
                    (personId, 
                    linkId, 
                    dateValidate ) 
            (SELECT stayPerson, 
                    linkId, 
                    dateValidate 
               FROM baspersonLink               
              WHERE personId = outPerson 
                AND linkId NOT IN 
           ( SELECT linkId 
               FROM basPersonLink 
              WHERE personId = stayPerson)
           );

        DELETE FROM basPersonLink WHERE personId = outPerson;

        --ANALÍTICA

        UPDATE gtcAnalytics set personId = stayPerson where personId = outPerson;

        DELETE FROM gtcAnalytics WHERE personId = outPerson;

        --FAVORITOS
       INSERT INTO gtcFavorite 
                   (personId, 
                   controlNumber) 
           (select stayPerson, 
                   controlNumber 
              from gtcFavorite 
             where personId = outPerson 
               and controlNumber not in 
           (select controlNumber 
              from gtcFavorite 
             where personId = stayPerson)
           );

       DELETE FROM gtcFavorite where personId = outPerson;

       --PESSOAS NAS UNIDADES DE BIBLOTECA
       INSERT INTO gtcPersonLibraryUnit
                   (personId, 
                   libraryUnitId)    
                (SELECT stayPerson,     
                        libraryUnitId
                   FROM gtcPersonLibraryUnit
                  WHERE personId = outPerson 
                    AND libraryUnitId
                 NOT IN ( SELECT libraryUnitId
                   FROM gtcPersonLibraryUnit
                  WHERE personId = stayPerson)
                );

       DELETE FROM gtcPersonLibraryUnit where personId = outPerson;

        --DOCUMENTOS
        INSERT INTO basDocument 
                    (personId, 
                    documentTypeId) 
            (SELECT stayPerson, documentTypeId 
               FROM basDocument 
              WHERE personId = outPerson 
                AND documentTypeId  
             NOT IN (SELECT documentTypeId 
               FROM basDocument  
              WHERE personId = stayPerson)
            );

        DELETE FROM basDocument where personId = outPerson;

        IF (existTable( 'basphysicalperson' ))
        THEN    
            IF (existContract( outperson ))
            THEN
                IF NOT ( existContract( stayperson ) )
                THEN
                    RAISE EXCEPTION 'Pessoa % possui contrato e não pode ser unida ou removida. Tenta inverter as pessoas.', outPerson;
                END IF;

                RAISE EXCEPTION 'Pessoa % possui contrato e não pode ser unida ou removida.', outPerson;
            END IF;
        INSERT INTO basphysicalpersonkinship
                    (personid,
                     relativepersonid,
                     kinshipid,
                     datetime
                    ) 
            (SELECT stayPerson , 
                    relativepersonid, 
                    kinshipid,
                    datetime
               FROM basphysicalpersonkinship            
              WHERE personId = outPerson 
                AND kinshipid NOT IN 
           ( SELECT kinshipid
               FROM basphysicalpersonkinship
              WHERE personId = stayPerson )
           );
        DELETE FROM basphysicalpersonkinship WHERE personid = outPerson;

        INSERT INTO basbadgeloan
                    (personid,
                     datetime,
                     loanid, 
                     badgeid, 
                     loandate,  
                     expectedreturndate) 
            (SELECT stayPerson,
                    datetime, 
                    loanid,
                    badgeid,
                    loandate,
                    expectedreturndate
               FROM basbadgeloan            
              WHERE personId = outPerson 
                AND badgeid NOT IN 
           ( SELECT badgeid
               FROM basbadgeloan
              WHERE personId = stayPerson)
           );
        DELETE FROM basbadgeloan WHERE personid = outPerson;

        INSERT INTO bashistoricofuncional
                    (personid,
                     datetime,
                     historicofuncionalid,
                     data, 
                     assunto, 
                     inicio,  
                     observacao) 
            (SELECT stayPerson,
                    datetime, 
                    historicofuncionalid,
                    data,
                    assunto,
                    inicio,
                    observacao
               FROM bashistoricofuncional           
              WHERE personId = outPerson 
                AND historicofuncionalid NOT IN 
           ( SELECT historicofuncionalid
               FROM bashistoricofuncional
              WHERE personId = stayPerson)
           );
        DELETE FROM bashistoricofuncional WHERE personid = outPerson;

        INSERT INTO basprofessionalactivitypeople
                    (personid,
                     datetime,
                     professionalactivitypeopleid,
                     professionalactivityid, 
                     legalpersonid, 
                     begindate,  
                     professionalactivitylinktypeid) 
            (SELECT stayPerson,
                    datetime, 
                    professionalactivitypeopleid,
                    professionalactivityid,
                    legalpersonid,
                    begindate,
                    professionalactivitylinktypeid
               FROM basprofessionalactivitypeople           
              WHERE personId = outPerson 
                AND professionalactivityid NOT IN 
           ( SELECT professionalactivityid
               FROM basprofessionalactivitypeople
              WHERE personId = stayPerson)
           );
        DELETE FROM basprofessionalactivitypeople WHERE personid = outPerson;

        INSERT INTO bassectorboss
                    (bossid,
                     sectorid,
                     issendemail, 
                     email 
                     ) 
            (SELECT stayPerson,
                    sectorid,
                    issendemail, 
                    email
               FROM bassectorboss   
              WHERE bossid = outPerson 
                AND sectorid NOT IN 
           ( SELECT sectorid 
               FROM bassectorboss
              WHERE bossid = stayPerson)
           );
        DELETE FROM bassectorboss WHERE bossid = outPerson;

        INSERT INTO basstamp
                    (personid,
                     datetime,
                     stampid,
                     functiondescription 
                     ) 
            (SELECT stayPerson,
                    datetime, 
                    stampid,
                    functiondescription
               FROM basstamp            
              WHERE personId = outPerson 
                AND stampid NOT IN 
           ( SELECT stampid
               FROM basstamp
              WHERE personId = stayPerson)
           );
        DELETE FROM basstamp WHERE personid = outPerson;

        INSERT INTO basphysicalpersonprofessor 
                    ( personid , 
                    persondv , 
                    personmask , 
                    name , 
                    shortname , 
                    cityid , 
                    zipcode , 
                    location , 
                    number , 
                    complement , 
                    neighborhood , 
                    email , 
                    emailalternative , 
                    url , 
                    datein , 
                    password , 
                    isallowpersonaldata , 
                    miolousername , 
                    locationtypeid , 
                    sentemail , 
                    photoid , 
                    login , 
                    obs , 
                    sex , 
                    maritalstatusid , 
                    residentialphone , 
                    workphone , 
                    cellphone , 
                    messagephone , 
                    messagecontact , 
                    datebirth , 
                    cityidbirth , 
                    countryidbirth , 
                    responsablelegalid , 
                    carplate , 
                    specialnecessityid , 
                    specialnecessitydescription , 
                    cityidwork , 
                    zipcodework , 
                    locationwork , 
                    complementwork , 
                    neighborhoodwork , 
                    ethnicoriginid , 
                    datedeath , 
                    persontitleid , 
                    locationtypeidwork , 
                    mothername ,    
                    fathername , 
                    workemployername , 
                    workfunction , 
                    workstartdate ,
                    workenddate , 
                    workathome , 
                    ethnicorigin , 
                    identifier ) 
          ( SELECT  stayperson, 
                    persondv , 
                    personmask , 
                    name , 
                    shortname , 
                    cityid , 
                    zipcode , 
                    location , 
                    number , 
                    complement , 
                    neighborhood , 
                    email , 
                    emailalternative , 
                    url , 
                    datein , 
                    password , 
                    isallowpersonaldata , 
                    miolousername ,         
                    locationtypeid , 
                    sentemail , 
                    photoid , 
                    login , 
                    obs , 
                    sex , 
                    maritalstatusid , 
                    residentialphone , 
                    workphone ,         
                    cellphone  , 
                    messagephone , 
                    messagecontact ,    
                    datebirth , 
                    cityidbirth , 
                    countryidbirth , 
                    responsablelegalid , 
                    carplate , 
                    specialnecessityid , 
                    specialnecessitydescription , 
                    cityidwork , 
                    zipcodework , 
                    locationwork , 
                    complementwork , 
                    neighborhoodwork , 
                    ethnicoriginid , 
                    datedeath , 
                    persontitleid , 
                    locationtypeidwork , 
                    mothername , 
                    fathername , 
                    workemployername , 
                    workfunction , 
                    workstartdate , 
                    workenddate , 
                    workathome , 
                    ethnicorigin , 
                    identifier 
               FROM basphysicalpersonprofessor 
              WHERE personid = outperson);

        INSERT INTO basprofessorcommitment
                    (personid,
                     datetime,
                     begindate,
                     workload 
                     ) 
            (SELECT stayPerson,
                    datetime, 
                    begindate,
                    workload
               FROM basprofessorcommitment          
              WHERE personId = outPerson 
                AND begindate NOT IN 
           ( SELECT begindate
               FROM basprofessorcommitment
              WHERE personId = stayPerson)
           );
        DELETE FROM basprofessorcommitment WHERE personid = outPerson; 

        UPDATE fininvoice SET personId = stayPerson WHERE personId = outPerson;

        INSERT INTO acdprofessorformation
                    (professorid,
                     formationlevelid,
                     externalcourseid,
                     begindate,
                     dateconclusion,
                     institutionid 
                     ) 
            (SELECT stayPerson,
                    formationlevelid,
                    externalcourseid,
                    begindate,
                    dateconclusion,
                    institutionid 
               FROM acdprofessorformation           
              WHERE professorid = outPerson 
                AND formationlevelid NOT IN 
           ( SELECT formationlevelid
               FROM acdprofessorformation
              WHERE professorid = stayPerson)
                AND externalcourseid NOT IN
           ( SELECT externalcourseid
               FROM acdprofessorformation
              WHERE professorid = stayPerson));
         DELETE FROM acdprofessorformation WHERE professorid = outPerson;

   INSERT INTO acdprofessorcenter
                    (professorid,
                     centerid,
                     begindate 
                     ) 
            (SELECT stayPerson,
                    centerid,
                    begindate
               FROM acdprofessorcenter      
              WHERE professorid = outPerson 
                AND centerid NOT IN 
           ( SELECT centerid
               FROM acdprofessorcenter
              WHERE professorid = stayPerson));
    DELETE FROM acdprofessorcenter WHERE professorid = outPerson;

    INSERT INTO basphysicalpersonemployee 
                (personid , 
                persondv , 
                personmask , 
                name , 
                shortname , 
                cityid , 
                zipcode , 
                location , 
                number , 
                complement , 
                neighborhood , 
                email , 
                emailalternative , 
                url , 
                datein , 
                password , 
                isallowpersonaldata , 
                miolousername ,     
                locationtypeid , 
                sentemail , 
                photoid , 
                login , 
                obs , 
                sex , 
                maritalstatusid , 
                residentialphone , 
                workphone , 
                cellphone , 
                messagephone , 
                messagecontact , 
                datebirth , 
                cityidbirth , 
                countryidbirth , 
                responsablelegalid , 
                carplate , 
                specialnecessityid , 
                specialnecessitydescription , 
                cityidwork , 
                zipcodework , 
                locationwork , 
                complementwork , 
                neighborhoodwork , 
                ethnicoriginid , 
                datedeath , 
                persontitleid , 
                locationtypeidwork ,    
                mothername , 
                fathername ,
                workemployername , 
                workfunction , 
                workstartdate , 
                workenddate , 
                workathome , 
                ethnicorigin ,  
                identifier) 
       ( SELECT stayperson ,   
                persondv , 
                personmask , 
                name , 
                shortname , 
                cityid , 
                zipcode , 
                location , 
                number , 
                complement , 
                neighborhood , 
                email , 
                emailalternative , 
                url , 
                datein , 
                password , 
                isallowpersonaldata , 
                miolousername , 
                locationtypeid , 
                sentemail , 
                photoid , 
                login , 
                obs , 
                sex , 
                maritalstatusid , 
                residentialphone , 
                workphone , 
                cellphone , 
                messagephone , 
                messagecontact , 
                datebirth , 
                cityidbirth , 
                countryidbirth , 
                responsablelegalid , 
                carplate , 
                specialnecessityid , 
                specialnecessitydescription , 
                cityidwork , 
                zipcodework , 
                locationwork , 
                complementwork , 
                neighborhoodwork , 
                ethnicoriginid , 
                datedeath , 
                persontitleid , 
                locationtypeidwork , 
                mothername , 
                fathername , 
                workemployername , 
                workfunction , 
                workstartdate , 
                workenddate , 
                workathome , 
                ethnicorigin , 
                identifier 
           FROM basphysicalpersonemployee 
          WHERE personid = outperson);

    INSERT INTO basemployee
                    (personid,
                     employeetypeid 
                     ) 
            (SELECT stayPerson,
                    employeetypeid
               FROM basemployee     
              WHERE personid = outPerson 
                AND employeetypeid NOT IN 
           ( SELECT employeetypeid
               FROM basemployee
              WHERE personid = stayPerson));
    DELETE FROM basemployee WHERE personid = outPerson;

    INSERT INTO  Acdmoodlesubscription
                    (personId, 
                    groupid)    
            (SELECT stayPerson,
                    groupid     

               FROM Acdmoodlesubscription
              WHERE personId = outPerson 
                AND groupid
                 IN ( SELECT groupid
               FROM Acdmoodlesubscription 
              WHERE personId = outPerson EXCEPT
             SELECT groupid 
               FROM Acdmoodlesubscription 
              WHERE personId = stayPerson)
            );

         DELETE FROM Acdmoodlesubscription  where personId = outPerson;

    DELETE FROM basphysicalperson WHERE personId = outperson; 
    DELETE FROM basphysicalpersonprofessor WHERE personId = outperson;
    DELETE FROM basphysicalpersonemployee WHERE personId = outperson; 
    DELETE FROM basphysicalpersonstudent WHERE personId = outperson; 
    UPDATE fininvoice SET personId = stayPerson WHERE personId = outPerson; 
    END IF;

    DELETE FROM basDocument where personId = outPerson;
    DELETE FROM gtcLibPerson WHERE personId = outPerson;
    DELETE FROM basPerson WHERE personId = outperson;

        RETURN true;

        END;
$BODY$ language plpgsql;

-- obtem restrições da biblioteca para o SAGU
CREATE OR REPLACE FUNCTION gtcObterRestricoesParaSagu(person int4)
RETURNS SETOF type_obter_restricoes as $BODY$
DECLARE
BEGIN
	RETURN QUERY
	SELECT
		'Penalidade' AS tipo,
		( SELECT COUNT()
		  FROM gtcpenalty
		  WHERE coalesce( penaltyEndDate > now(), penaltyEndDate IS NULL )
		  AND personid = person ) as quantidade
	UNION
	(
		SELECT 'Multas' AS tipo,
		( SELECT COUNT()
		  FROM gtcfine f
		  LEFT JOIN gtcloan l
		  ON f.loanid = l.loanid
		  WHERE finestatusid = ( SELECT value FROM basconfig WHERE parameter ='ID_FINESTATUS_OPEN' )::int
		  AND personid = person ) as quantidade
	)
	UNION
	(
		SELECT 'Total de Empréstimos' AS tipo,
		( SELECT count()
		  FROM gtcloan
		  WHERE personid = person
		  AND returndate is null ) as quantidade
	)
	UNION
	(
		SELECT 'Empréstimos atrasados' AS tipo,
		( SELECT count()
		  FROM gtcloan
		  WHERE personid = person
		  AND returndate is null
		  AND returnforecastdate < NOW ) as quantidade
	);
END;
$BODY$ language plpgsql;




--Função que busca a posição correta da fila de reservas
CREATE OR REPLACE FUNCTION reserveposition(p_reserveid integer)
RETURNS integer as $BODY$
    DECLARE

        v_position integer;
        v_position_aux integer;
        v_reservestatusid integer;
       -- v_linha RECORD;
        v_itemnumber varchar;
    BEGIN 
	SELECT INTO v_reservestatusid reservestatusid FROM gtcreserve WHERE reserveid=p_reserveid;

	--se for atendida ou comunicada retorna 0 para depois ser tratado de outra forma
        IF v_reservestatusid = 2 OR v_reservestatusid = 3
        THEN
  	    v_position = 0;
	    
	ELSE --senão busca a posição da reserva  
            v_position = 0;
 
            FOR v_itemnumber IN (SELECT itemnumber FROM gtcreservecomposition WHERE reserveid = p_reserveid)
            LOOP
                
                SELECT INTO v_position_aux COUNT(DISTINCT gtcreserve.reserveid) 
              FROM gtcreserve 
        INNER JOIN gtcreservecomposition 
	        ON(gtcreserve.reserveid = gtcreservecomposition.reserveid) 
             WHERE gtcreservecomposition.itemnumber = v_itemnumber
               AND requesteddate <= (SELECT requesteddate 
                                       FROM gtcreserve 
	                              WHERE reserveid= p_reserveid) 
                AND reservestatusid='1'; 

                IF v_position_aux > v_position
                THEN 
                    v_position = v_position_aux;
                END IF; 
           
            END LOOP;

            
      	END IF;

        RETURN v_position;

     END;

$BODY$ language plpgsql;

--Função que atualiza os dados das tabelas que serão importadas da biblioteca participante para a integradora através da biblioteca virtual
CREATE OR REPLACE FUNCTION atualizaDadosBibliotecaVirtual(p_zipname varchar)
RETURNS bool as $BODY$
    DECLARE
		v_sequenceLibrary int;
		v_sequence int;
		v_maxLibraryUnit int;
		v_maxcontrolNumber int;
		v_maxItemNumber varchar;
		v_library int;
		v_controlNumber int;
	BEGIN
                v_maxcontrolNumber = MAX(controlnumber) FROM gtcmaterial;
                v_maxcontrolNumber = COALESCE(v_maxcontrolNumber, 0);
                
		v_maxLibraryUnit = MAX(libraryunitid) FROM gtclibraryunit;
                v_maxLibraryUnit = COALESCE(v_maxLibraryUnit, 0);

		v_maxItemNumber = MAX(itemnumber) FROM gtcexemplarycontrol;
                v_maxItemNumber = COALESCE(v_maxItemNumber, '0');
		
                EXECUTE 'CREATE TEMP SEQUENCE seq_tmpgtclibraryunit START 1';
                EXECUTE 'CREATE TEMP SEQUENCE seq_tmpgtcmaterial START 1';
                
                --Atualiza a tabela de Unidade de biblioteca
                FOR v_library IN SELECT libraryunitid FROM tmpgtclibraryunit
		LOOP
			v_sequenceLibrary = nextval('seq_tmpgtclibraryunit');
                       
                        EXECUTE 'UPDATE tmpgtclibraryunit SET libraryname = ''' || p_zipname || ' - ' || ''' || trim(libraryname) WHERE libraryunitid =' || v_library;
			EXECUTE 'UPDATE tmpgtclibraryunit SET libraryunitid =' || v_maxLibraryUnit + v_sequenceLibrary || ' WHERE libraryunitid = ' || v_library;
                        END LOOP;
                --Atualiza as tabelas de Materiais e de Exemplares (gtcmaterial, gtcmaterialcontrol, gtcexemplarycontrol)
		FOR v_controlNumber IN SELECT controlnumber FROM tmpgtcmaterialcontrol
		LOOP
			v_sequence = nextval('seq_tmpgtcmaterial');

                        EXECUTE 'UPDATE tmpgtcmaterial SET content =''' || v_maxLibraryUnit + v_sequence || ''' WHERE fieldid = ' || '''949''' || ' AND subfieldid = ' || '''b''' || ' AND controlNumber =' || v_controlNumber;
			EXECUTE 'UPDATE tmpgtcmaterial SET content = lpad(''' || v_maxItemNumber || v_sequence || ''', 8 , \'0\') WHERE fieldid = ' || '''949''' || ' AND subfieldid = ' || '''a''' || ' AND controlnumber =' || v_controlNumber;
			EXECUTE 'UPDATE tmpgtcmaterial SET controlnumber =' || v_maxcontrolNumber + v_sequence || ' WHERE controlnumber =' || v_controlNumber;
                        EXECUTE 'UPDATE tmpgtcmaterialcontrol SET controlnumber =' || v_maxcontrolNumber + v_sequence || ' WHERE controlnumber =' || v_controlNumber;
			EXECUTE 'UPDATE tmpgtcexemplarycontrol SET itemnumber = lpad(''' || v_maxItemNumber || v_sequence || ''' , 8 , ' || '''0''' || ') WHERE controlnumber =' || v_controlNumber;
                        EXECUTE 'UPDATE tmpgtcexemplarycontrol SET controlnumber =' || v_maxcontrolNumber + v_sequence || ' WHERE controlnumber =' || v_controlNumber;
			END LOOP;
	    
		RETURN TRUE;

     END;

$BODY$ language plpgsql;
