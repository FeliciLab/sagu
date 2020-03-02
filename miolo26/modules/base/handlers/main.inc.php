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
set_time_limit(10000);
ini_set('max_execution_time', 10000);
ini_set('max_input_time',10000);

$theme->clearContent();

$syncModule = MIOLO::_REQUEST('syncModule');
$MIOLO->uses( 'classes/bBaseDeDados.class.php','base');
$MIOLO->uses( 'classes/BString.class.php','base');
$MIOLO->uses( 'classes/bCatalogo.class.php','base');
$MIOLO->uses( 'classes/bInfoColuna.class.php','base');

if ( !$syncModule )
{
    // Adiciona classes necessárias.
    $MIOLO->uses( 'classes/bForm.class.php','base');
    $MIOLO->uses( 'classes/bFormCadastro.class.php','base');
    $MIOLO->uses( 'classes/bFormBusca.class.php','base');
    $MIOLO->uses( 'classes/bTipo.class.php','base');
    $MIOLO->uses( 'classes/bBarraDeFerramentas.class.php','base');
    $MIOLO->uses( 'classes/bJavascript.class.php','base');
    $MIOLO->uses( 'classes/bBooleano.class.php','base');
    $MIOLO->uses( 'classes/bEscolha.class.php','base');

    // Adiciona biblioteca javascript do módulo Base.
    $MIOLO->page->addScript('base.js','base');

    // Define a base de dados.
    define(DB_NAME, 'base');
    
    // Constantes para o correto funcionamento do módulo.
    define(DB_TRUE, 't');
    define(DB_FALSE, 'f');
    define(FUNCAO_BUSCAR, 'buscar');
    define(FUNCAO_EDITAR, 'editar');
    define(FUNCAO_INSERIR, 'inserir');
    define(FUNCAO_REMOVER, 'remover');
    define(FUNCAO_EXPLORAR, 'explorar');
    define(MODULO, 'base');

    // Constantes para tamanho de campo.
    define(T_CODIGO, 10);
    define(T_INTEIRO, 10);
    define(T_DESCRICAO, 25);

    // Inclui o manipulador.
    $chave = MIOLO::_REQUEST('chave');
    
    if ( strlen($chave) > 0 )
    {
        $MIOLO->uses('handlers/manipulador.inc.php', 'base');
    }
    else
    {
        $shiftAction = $context->shiftAction();

        if ( $shiftAction )
        {
            $MIOLO->invokeHandler($module, $shiftAction);
        }
    }
}
else
{
    $MIOLO->uses( 'classes/bSync.interface.php','base');
    $MIOLO->uses( 'classes/bSyncDatabase.class.php','base');
    $MIOLO->uses( 'classes/bSyncDatabaseContent.class.php','base');
    $MIOLO->uses( 'classes/bSyncDatabaseFunction.class.php','base');
    $MIOLO->uses( 'classes/bSyncDatabaseView.class.php','base');

    if ( !defined('DB_NAME') )
    {
        define('DB_NAME', $syncModule );
    }
    
    $MIOLO->page->addJsCode(
    "
        /* Esconde / mostra conteúdo de uma tabela*/
        function showHideTable( element )
        {
            if ( element.tBodies[0].style.display == '' )
            {
                element.tBodies[0].style.display ='none'
            }
            else
            {
                element.tBodies[0].style.display ='';
            }
        }
    "
    );

    $MIOLO->page->onload("

    //passa por todas tabelas adicionando suporte a esconder/mostrar
    tables = document.getElementsByClassName('mSimpleTable  mTableRaw');
    //tables = document.getElementsByTagName('table');

    for ( i =0 ; i < tables.length ; i++ )
    {
        tables[i].style.width ='100%';
        tables[i].tBodies[0].style.display ='none';

        if ( tables[i].caption != null ) 
        {
            tables[i].caption.style.cursor= 'pointer';
            tables[i].caption.setAttribute('onclick', 'showHideTable(this.parentNode)' );
        }
    }");

    try
    {    
        //$db = new bBaseDeDados($syncModule);
        bBaseDeDados::iniciarTransacao();

        if ( ! $syncModule )
        {
            throw new Exception( new BString('É necessário informar modulo de sincronização (syncModule).') );
        }

        //cria um espaço superior
        $fields[] = new MDiv('','<br/><br/><br/>');

        //executa o script de inicialização
        $startScript = $MIOLO->getConf('home.miolo').'/modules/'.$syncModule.'/syncdb/start.php';

        if ( file_exists( $startScript ) )
        {
            require $startScript;
        }

        $syncFiles = BSyncDatabase::listSyncFiles( $syncModule );

        $syndDb = null;
        $tablesById = null;

        if ( is_array( $syncFiles ) ) 
        {
            foreach ( $syncFiles as $line => $syncFile )
            {
                $syncDb = new bSyncDatabase( file_get_contents( $syncFile ) );
                $syncDb->setModule($syncModule);
                $tablesById = $syncDb->syncronize();
            }
        }

        $functionFiles = BSyncDatabaseFunction::listSyncFiles( $syncModule );

        //efetua sincronização de funções de base de dados
        if ( is_array( $functionFiles ) ) 
        {
            foreach ( $functionFiles as $line => $function )
            {
                $function = new BSyncDatabaseFunction( $function , $syncModule );
                $fResult = $function->syncronize();
                $functions = array_values( $fResult->missing );

                $resultTable[0] = array( _M('Inicial'), $fResult->start );
                $resultTable[1] = array( _M('Arquivo'), $fResult->file );
                $resultTable[2] = array( _M('Final'), $fResult->final );
                $resultTable[3] = array( _M('Faltando'), ( $fResult->final- $fResult->file) );

                if ( $functions )
                {
                    $myTable = new MTableRaw( _M( new bString( 'Faltantes' ) ) , $functions, array(),'', true );
                    $resultTable[3][1] .= '<br/> ' .$myTable->generate();
                }

                $fields[] = new MTableRaw( _M( new bString( 'Funções' ) ) , $resultTable, array( new bString( _M('Situação') ),_M( new bString( 'Contagem') ) ), 'functions', true);

                if ( $functions )
                {
                    if ( $fResult->sql )
                    {
                        $fields[] = new MMultiLineField('sqlFunctions', $fResult->sql );
                    }
                }

                $fields[] = new MSeparator('<br/>');
            }
        }

        if ( $syncDb != null)
        {
            $syncDb->syncronizeTriggersAndContraints($tablesById);
            $messages = $syncDb->getMessages();

            $fields[] = new MTableRaw(new BString('Sincronização de estrutura de base de dados'), $messages, array( new BString('Mensagem') ), 'syncDatabase', true);
            $fields[] = new MSeparator('<br/>');
        }

        $views = BSyncDatabaseView::listSyncFiles( $syncModule );

        //efetua sincronização de funções de base de dados
        if ( is_array( $views ) ) 
        {
            foreach ( $views as $line => $view )
            {
                $view = new BSyncDatabaseView( $view, $syncModule );
                $vResult = $view->syncronize();

                $missing= array_values( $vResult->missing );

                $resultTable = array();
                $resultTable[0] = array( _M('Inicial'), $vResult->start );
                $resultTable[1] = array( _M('Arquivo'), $vResult->file );
                $resultTable[2] = array( _M('Final'), $vResult->final );
                $resultTable[3] = array( _M('Faltando'), ( $vResult->final- $vResult->file) );

                if ( $missing )
                {
                    $myTable = new MTableRaw( _M( new bString( 'Faltantes' ) ) , $missing, array(),'', true );
                    $resultTable[3][1] .= '<br/> ' .$myTable->generate();
                }

                $fields[] = new MTableRaw( _M( new bString( 'Visões' ) ) , $resultTable, array( new bString( _M('Situação') ),_M( new bString( 'Contagem') ) ), 'views', true);

                if ( $functions )
                {
                    if ( $vResult->sql )
                    {
                        $fields[] = new MMultiLineField('viewsMissing', $vResult->sql );
                    }
                }

                $fields[] = new MSeparator('<br/>');
            }
        }

        //obtem lista de arquivos xml a sincronizar
        $files = BSyncDatabaseContent::listSyncFiles( $syncModule );

        if ( is_array( $files ) )
        {
            foreach ( $files as $line => $file )
            {
                $tableExtra = null;
                $resultA = null;
                //$fields = null;

                $fileBase = str_replace('.xml', '', basename($file));

                $basConfig = new BSyncDatabaseContent( );
                $basConfig->setXmlPath($file);
                $basConfig->setModule( $syncModule );

                if ( strpos($file, 'miolo_') )
                {
                    $basConfig->setModule( 'admin' );
                }
                else
                {
                    $basConfig->setModule( $syncModule );
                }

                $result = $basConfig->syncronize();
                $resultExtras = null;

                $resultA = array();
                $resultA[0] = array( new BString( _M('Contagem xml') ), $result->countXml );
                $resultA[1] = array( new BString( _M('Contagem inicial') ),$result->countStart );
                $resultA[2] = array( new BString( _M('Atualizações') ) ,$result->updateCount );
                $resultA[3] = array( new BString( _M('Inserções') ),$result->insertCount );
                $resultA[4] = array( new BString( _M('Remoções') ),$result->deleteCount );
                $resultA[5] = array( new BString( _M('Contagem final') ),$result->countEnd );

                if ( is_array( $result->extras ) )
                {
                    foreach ( $result->extras as $line => $extra )
                    {
                        $resultE = array();
                        $columns = array();

                        foreach ( $extra as $l => $item )
                        {
                            $columns[] = $l;
                            $resultE[] = $item;
                        }

                        $resultExtras[] = $resultE;
                    }

                    $tableExtra[] = new MTableRaw( '', $resultExtras, array_values($columns), '', true );

                    $resultA[6] = array( _M('Itens sobrando'), $tableExtra );
                }

                $title = $fileBase ;

                if ( $resultA[6] )
                {
                    $title .= new bString( _M(' - Atualizado') );
                }

                $fields[] = new MTableRaw( $title , $resultA, array(_M('Tipo'),_M('Quantidade')), 'result'.$fileBase , true );
                $fields[] = new MSeparator('<br/>');

                //inclui xml das diferenças caso exista
                $xml = $basConfig->makeXMLfromResult( $result->extras );

                if ( $xml )
                {
                    $fields[] = new MultilineField('xml'.$fileBase , $xml);
                }
            }
        }

        $theme->appendContent( $fields );

        //caso exista script de sincronização, executa-o
        $syncScript = $MIOLO->getConf('home.miolo').'/modules/'.$syncModule.'/syncdb/sync.php';

        if ( file_exists( $syncScript ) )
        {
            require $syncScript;
        }

        bBaseDeDados::finalizarTransacao();
    }
    catch (Exception $e)
    {
        //$db = new bBaseDeDados($syncModule);
        die( bBaseDeDados::obterUltimoErro() .' -<br/>SQL = '.  bBaseDeDados::obterUltimaInstrucao() . '<br/>Mensagem='.$e->getMessage().'-'.$e->getFile().'-'.$e->getLine());
    }
}
?>
