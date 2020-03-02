<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/01/2011
 *
 **/

$MIOLO = MIOLO::getInstance();
$messagesG = array();

$nomeBase = $MIOLO->getConf( "db.gnuteca3.name" );

// Altera configuração do conform_string
bBaseDeDados::consultar("ALTER DATABASE {$nomeBase} SET standard_conforming_strings = off;");

//caso a baslog não exista, cria para evitar problemas no syncdb
if ( !bCatalogo::verificarExistenciaDaTabela('public', 'baslog') )
{
    bBaseDeDados::consultar("CREATE TABLE baslog (username varchar(20), datetime timestamptz, ipaddress inet);");
    $messagesG[][] =  "Criando tabela baslog";
}


//Renomear a tabela basDomain para basDomain
// Altera check, para usar basdomain ao invés de gtcdomain
bBaseDeDados::consultar('CREATE OR REPLACE FUNCTION gtc_chk_domain(p_domain varchar , p_key varchar) 
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
$BODY$ language plpgsql;');

$result = bCatalogo::renomearTabelaSeExistir('gtcdomain','basdomain');

if ( bCatalogo::verificarExistenciaDaTabela('public', 'basdocument') && !bCatalogo::verificarExistenciaDaTabela('public', 'basdocumenttype'))
{
    //lista chaves estrangeiras da basdocument
    $references = bCatalogo::obterChavesEstrangeiras('public','basdocument');

    //Por padrão define que a chave estrangeira para a tabela basdocumenttype não existe
    $documentTypeReference = false;

    if ( is_array($references))
    {
        //verifica cada uma das chaves estrangeiras da basdocument
        foreach ( $references as $reference )
       {
           //Se basdocument tiver referencia para basdocumenttype
           if ( strtolower($reference->tableRef) == 'basdocumenttype' )
           {
              $documentTypeReference = true;
           }
       }
    }

    // se basdocument não tiver referencia da basdocumenttype
    if ( !$documentTypeReference )
    {   
        $messagesG[][] =  "Organizando tipos de documento";

        //cria basDocumentType temporária depois o vpp via incluir o resto dos campos
        bBaseDeDados::consultar("CREATE TABLE basdocumenttype
                                (
                                    documenttypeid integer,
                                    \"name\" text NOT NULL,
                                    mask text,
                                    persontype character(1) NOT NULL,
                                    sex character(1),
                                    minage integer,
                                    maxage integer,
                                    needdeliver boolean DEFAULT true NOT NULL,
                                    isblockenroll boolean DEFAULT false NOT NULL,
                                    fillhint text 
                                );");

        //remove contraint para tudo funcionar direito
        $basDocumentCheck = bCatalogo::obterChecagens('public', 'basdocument','basdocument_documenttypeid');

        //Se tiver checagem do tipo de documento na basdocument
        if ( $basDocumentCheck[0]->name )
        {
            //Remove checagem pois a partir de agora a será uma relação com a basdocumenttype
            bBaseDeDados::consultar( "ALTER TABLE basdocument DROP constraint basdocument_documenttypeid;");
        }

        //realiza update que troca o basdocument.documenttypeid que esta com o valor basdomain.'Document_type'.key para basdomain.'Document_type'.sequence evitando problema de integridade 
        bBaseDeDados::consultar("UPDATE basdocument SET documenttypeid = (SELECT sequence FROM basdomain WHERE domainid = 'DOCUMENT_TYPE' and key = documenttypeid);");
        //insere os documentos necessários
        bBaseDeDados::consultar("INSERT INTO basdocumenttype (documenttypeid,name,persontype) SELECT sequence,label,'P' FROM basdomain WHERE domainid = 'DOCUMENT_TYPE' AND sequence NOT IN ( SELECT documenttypeid FROM basdocumenttype );");
        //remove registros sobresalentes
        bBaseDeDados::consultar("DELETE FROM basdomain WHERE domainid = 'DOCUMENT_TYPE'");
    }
}

if ( bCatalogo::verificarExistenciaDaTabela('public', 'basphone') )
{
    $colunasBasPhone = bCatalogo::obterColunasDaTabela('public','basPhone');

    //cria código do telefone de forma serial para que o postgres atribua um id para todos os registros
    if ( !$colunasBasPhone['phoneid'] )
    {
        $messagesG[][] =  "Criando coluna fone id na tabela public.basPhone.";
        bBaseDeDados::consultar("ALTER TABLE basphone ADD column phoneid SERIAL NOT NULL;");
    }
}

//Caso a preferencia de change_write_person nao esteja no padrao que permite dar permissao por aba.
bBaseDeDados::consultar("UPDATE basconfig SET value = 
'tabMain=t
tabAccess=t
tabPhoto=t
tabPhone=t
tabDocument=t
tabBond=t
tabPenalty=t',
description =
'Permite alterações no formulário de pessoas.
                    Estas permissões são dada por abas, sendo estas respectivamente:

                    Gerais=tabMain
                    Acesso a biblioteca=tabAccess
                    Foto=tabPhoto
                    Telefones=tabPhone
                    Documentos=tabDocument
                    Vínculo=tabBond
                    Penalidade=tabPenalty

                    Deve ser atribuido o valor t para a aba que poderá ser editada e f para a aba que não poderá ser editada.' 
WHERE parameter = 'CHANGE_WRITE_PERSON' AND trim(value) NOT ILIKE '%=%';");

$langs = bCatalogo::listarLinguagens();

//adiciona suporta a linguagem caso não exista
if ( !in_array('plpgsql', $langs))
{
    bBaseDeDados::executar("CREATE LANGUAGE plpgsql;");
}


bBaseDeDados::executar("
--
-- ftomasini - Cria Tabela de diferenças caso ela não exista
--
CREATE OR REPLACE FUNCTION CriaTabela() RETURNS integer AS \$\$
DECLARE
foiCriada INTEGER :=0;
tabela RECORD;
tabelaIgnore RECORD;
BEGIN

SELECT INTO tabela tablename FROM pg_tables where tablename='dbchanges' and schemaname = ANY (current_schemas(true));

IF tabela.tablename IS NULL
THEN
    CREATE TABLE dbChanges
        (changeId SERIAL,
         change TEXT NOT NULL,
         applied BOOLEAN NOT NULL DEFAULT 'f',
         applicationVersion INTEGER NOT NULL,
         orderchange integer,
         error text,
         applieddate TIMESTAMP);

foiCriada = 1;

END IF;

SELECT INTO tabelaIgnore tablename FROM pg_tables where tablename='ignorexml' and schemaname = ANY (current_schemas(true));

IF tabelaIgnore.tablename IS NULL
THEN
    CREATE TABLE ignorexml (
        xmlname varchar(255) NOT NULL,
        PRIMARY KEY (xmlname));

foiCriada = 1;

END IF;


RETURN foiCriada;
END;
\$\$ LANGUAGE plpgsql;

--
-- ftomasini - Cria tabela se não existe
--
SELECT CriaTabela();


CREATE OR REPLACE FUNCTION applyChanges(p_changes TEXT, p_changeid integer)
RETURNS BOOLEAN AS \$body\$
BEGIN
    RAISE NOTICE 'Executando Changeid % : Comando %', p_changeid, p_changes;
    EXECUTE p_changes;

    RETURN TRUE;
    EXCEPTION WHEN OTHERS
    THEN
        UPDATE dbchanges set error = SQLERRM, applieddate = now() WHERE changeid = p_changeid;

        RETURN FALSE;
    END;

\$body\$ language plpgsql;



CREATE OR REPLACE FUNCTION syncDataBase(p_applicationversion integer)
RETURNS BOOLEAN AS \$body\$
DECLARE
    line RECORD;
    result_change BOOLEAN;
BEGIN
    --Percorre comandos que não foram executados e aplica na base
    FOR line IN  SELECT * 
                   FROM dbchanges 
                  WHERE applied IS false 
                    AND applicationversion <= p_applicationversion 
                    AND error IS NULL
               ORDER BY applicationversion, orderchange
    LOOP
        SELECT INTO result_change applyChanges(line.change, line.changeid);

        IF result_change IS TRUE
        THEN
            EXECUTE 'UPDATE dbchanges SET applied = true, error = null, applieddate = now() WHERE changeId = '|| line.changeid || '';
        END IF;

    END LOOP;

    RETURN True;
END;
\$body\$ language plpgsql;

"
);

bBaseDeDados::consultar("SET DateStyle TO 'SQL, DMY';");

$fields[] = new MTableRaw( 'Script de sincronização inicial', $messagesG, array(_M('Mensagem','gnuteca3')), 'initialMessage');
$fields[] = new MSeparator('<br/>');

$theme->appendContent( $fields );
?>
