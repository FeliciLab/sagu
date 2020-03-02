<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
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
 * Main handler
 * Contains the principals menus and the possibility to access submenus
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/07/2008
 *
 **/
global $startTime;
$startTime = microtime(true);
define('BASE_ENCODING', 'UTF-8');

//Devido a problema com Debian 6, onde diretivas do vhost nao entram em acao
//Esta sendo definido direto no main, vide ticket #17391
setlocale('LC_ALL','UTF-8');
mb_internal_encoding('UTF-8');

if ( !function_exists('registerShutDown') )
{
    function registerShutDown()
    {

        $MIOLO = MIOLO::getInstance();

        //Pega o último erro ocorrido
        $errors = error_get_last();

        //Se for um erro do tipo Fatal (Constante de valor 1)
        if ( $errors['type'] == 1 )
        {
            $endTime = microtime(true); //obtém o tempo em que terminou a requisição

            //Gera mensagem que será gravada no log :
            $message = "Fatal error: " . $errors['message']. " - " . $errors['file'] . " - " . $errors['line'];

            $busAnalycts = $MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');
            $busAnalycts = $MIOLO->getBusiness('gnuteca3', 'BusAnalytics');
            $busAnalycts = new BusinessGnuteca3BusAnalytics();
            //Grava o erro no log
            $busAnalycts->insertError($message, 0);    


            //Prepara o prompt para mostrar o erro na tela
            $prompt = new GPrompt('Error', $errors['message']);
            $prompt->setType( MPrompt::MSG_TYPE_ERROR );
            $prompt->addCloseButton( 'javascript:gnuteca.closeAction();' );
            //Mostra na tela o GPrompt
            echo $prompt->generate();
        }

    }

    //Registra função de erro.
    register_shutdown_function('registerShutDown');
}

try
{
    //garante funcionamento do clog de notice do banco, no default handler
    if ( $MIOLO->getConf('gnuteca.debug') )
    {
        ini_set('pgsql.ignore_notice', 0);
        ini_set('pgsql.log_notice',1);
    }

    if ( !$module )
    {
        $module = 'gnuteca3';
    }
    
    //adiciona suporte a outros módulos (simulando comportamento do main.inc.php do common
    if ( $module != 'gnuteca3')
    {
        if ( ($sa = $context->shiftAction() ) != NULL )
        {
            $a = $sa;
        }
        elseif ( $module != 'common' )
        {
            $a = 'main';
        }
        
        $handled = $MIOLO->invokeHandler($module, $a);
        
        return;
    }
    
    include_once(str_replace("\\", "/", dirname(__FILE__)) . "/debugFunctions.inc.php"); // include do arquivo com funções para degug e display de conteudo para companhamento.
    include_once(str_replace("\\", "/", dirname(__FILE__)) . "/define.inc.php"); // include das constantes
    include_once( str_replace('handlers', '', dirname(__FILE__) ). "handlers/defaultHandler.inc.php");
    
    $shiftAction = $context->shiftAction();
    $img = new MImage('icon', 'Configuration', GUtil::getImageTheme('home.png'));
    $navbar->setLabelHome( '' ); //tira o home padrão
    $link = new MLink('',$img->generate() . $label , 'javascript:'.GUtil::getActionLink('main', null, 'gnuteca'), '', MLink::TARGET_SELF, false );
    $navbar->addItem( 'option', $link );

    //Condicao para funcionar login referente a mensagem de nao ter permissao
    $eventTagetValue = MIOLO::_REQUEST("{$page->getFormId()}__EVENTTARGETVALUE");

    if ($eventTagetValue  == 'btnLogin:click')
    {
        $shiftAction = 'login';
    }
    
    if ( !$shiftAction && MIOLO::_REQUEST('controlNumber') )
    {
        $shiftAction = 'search';
    }

    $handled = $MIOLO->invokeHandler($module,$shiftAction);
    $action= MIOLO::_REQUEST( 'action');

    if ( MIOLO::_REQUEST( 'menuItem') )
    {
        $menuUrl = $action.'&menuItem='.MIOLO::_REQUEST( 'menuItem');
    }
    else
    {
        $menuUrl = $action;
    }

    $menuItem = $_SESSION['menuItems'][$menuUrl];

    if ( $menuItem )
    {
        foreach ($menuItem->label as $line => $label)
        {
            if ( $label )
            {
                $image = new MImage('icon', $label, GUtil::getImageTheme( $menuItem->image[$line] ));
                $span = new MSpan('', $image->generate() . $label );
                $navbar->addItem( 'option', $span );
                $menuTitle[] = $label;
            }
        }

        //título do menu utilizado na log abaixo
        $menuTitle = implode(':', $menuTitle);
    }

    $function = strtoupper( MIOLO::_REQUEST('function') );
    $action = strtoupper( $action );

    if ( $function == 'INSERT' || $function == 'NEW' || $function == 'DINAMICMENU' )  //FIXME de onde vem esse DINAMICMENU??
    {
        $image = GUtil::getImageTheme('button_insert.png');
        $funcLabel = _M('Inserção', $module);
    }
    else if ( $function == 'UPDATE')
    {
        $image = GUtil::getImageTheme('button_edit.png');
        $funcLabel = _M('Edição', $module);
    }
    else if ( $function == 'DUPLICATE')
    {
        $image = GUtil::getImageTheme('duplicateMaterial-16x16.png');
        $funcLabel = _M('Duplicação', $module);
    }
    else if ( $function == 'SEARCH' || !$function )
    {
        $image = GUtil::getImageTheme('search-16x16.png');
        $funcLabel = _M('Pesquisa', $module);
    }
    else if ( $function == 'DELETE')
    {
        $image = GUtil::getImageTheme('delete-16x16.png');
        $funcLabel = _M('Remoção', $module);
    }
    else if ( $function == 'ADDCHILDREN')
    {
        $image = GUtil::getImageTheme('addChild-16x16.png');
        $funcLabel = _M('Adicionar filho', $module);
    }

    if ( $action == 'MAIN:MATERIALMOVEMENT' || $action == 'MAIN:SEARCH:SIMPLESEARCH' || $action == 'MAIN' || !$action )
    {
        $funcLabel = '';
    }

    //só adiciona modo/função caso tenha definido
    if ( $funcLabel )
    {
        $image = new MImage('iconMode', $funcLabel, $image ) ;
        $image->addStyle('float','left');
        $navbar->addOption( $image->generate() . $funcLabel , $module, $handler);
    }

    if ( !$handled )
    {
        if ( GOperator::isLogged( ) && GOperator::hasSomePermission() )
        {
            $content = $MIOLO->getUI()->getForm('gnuteca3', "FrmMain");
        }
        else
        {
            $content = $MIOLO->getUI()->getForm($module, "FrmLogin");
        }

        $theme->setContent($content);
    }

    //adicionar class css ao formulário principal para possibilitar regras diferentes de css para submódulos
    if ( $shiftAction != 'lookup' && $shiftAction != 'verifyUser'   )
    {
        $page->addJsCode("dojo.body().className = '$shiftAction';");
    }

    //parte de registro de acesso
    $busAuthenticate = $MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');
    $busAnalycts = $MIOLO->getBusiness('gnuteca3', 'BusAnalytics');

    if ( $_REQUEST['action'] == 'main:search:simpleSearch' )
    {
        $accesType = BusinessGnuteca3BusAnalytics::ACCESS_TYPE_OUTER;
        $logLevel = ANALYCTS_LOGLEVEL_OUTER;
    }
    else
    {
        $accesType = BusinessGnuteca3BusAnalytics::ACCESS_TYPE_INNER;
        $logLevel = ANALYCTS_LOGLEVEL_INNER;
    }

    if ( $logLevel == 2 || ( !$MIOLO->page->isPostBack() && $logLevel == 1 ) || ( GUtil::getAjaxFunction() == 'subForm' && $logLevel = 1)  )
    {
        //Obtém o tempo em que terminou a requisição
        $endTime = microtime(true);

        $busAnalycts = new BusinessGnuteca3BusAnalytics();
        $busAnalycts->setData(BusinessGnuteca3BusAnalytics::getRealTimeAnalytics());
        
        $busAnalycts->accessType = $accesType;
        $busAnalycts->logLevel = $logLevel;
        $busAnalycts->menu = $menuTitle;
        
        // Calcula o tempo que a requisição levou.
        $busAnalycts->timeSpent = $endTime-$startTime;
        $busAnalycts->insertAnalytics();
    }
}
catch ( Exception $e )
{
    $busAnalycts = $MIOLO->getBusiness('gnuteca3', 'BusAnalytics');
    $msg = $e->getMessage();
    $endTime = microtime(true); //obtém o tempo em que terminou a requisição
    
    $busAnalycts = $MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');
    $busAnalycts->rollbackTransaction(); //rollback sql caso tenha erro para poder inserir o registro
    $busAnalycts = new BusinessGnuteca3BusAnalytics();
    $timeSpent = $endTime-$startTime;

    if ( $e instanceof EDatabaseQueryException && GOperator::isLogged() )
    {
        $MIOLO->getClass( 'gnuteca3','controls/GTree' );
        $msg = _M('Ocorreu um erro na base de dados:','gnuteca3') . '<br/><pre>' . pg_last_error().'</pre>';
    }

    if ( $MIOLO->getConf('gnuteca.debug'))
    {
        $msg .= "<div onclick=gnuteca.changeDisplay('extraInfo');>Clique aqui para mais informações:</div>";
        $content .= '#current:'.$e->getFile(). '('.$e->getLine() .') : ' . $e->getMessage() . "\n";
        $msg .= '<div id ="extraInfo" style="display:none; width: 430px; height: 100px; overflow:auto; border:solid 1px black;"><pre>'.$content.$e->getTraceAsString().'</pre></div>';
    }

    GPrompt::error( $msg );
}

?>