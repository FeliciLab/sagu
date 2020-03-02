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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 21/07/2008
 *
 **/

function defaultHandler( $handler , $title , $form=null, $searchForm = null, $img = null)
{
    global $module;
    global $MIOLO;
    global $theme;
    global $page;
        
    $subHandler = explode(':', $handler);
    $subHandler = $subHandler[2];

    if ( !$form )
    {
        $form = 'Frm'.ucfirst($subHandler);
    }

    if ( !$img )
    {
        $img = $subHandler.'-16x16.png';
    }

    $function = MIOLO::_REQUEST('function');
    $ui = $MIOLO->getUI();

    if ( !$searchForm )
    {
        $searchForm = $form . 'Search';
    }

    if ( ( strlen( $function ) == 0 ) || ( $function == 'search' ) || ( $function == 'detail' ) || ($function == 'execute'))
    {
        $content =  $ui->getForm($module,$searchForm);
    }
    else
    {
        $content = $ui->getForm( $module, $form );
    }

    //tenta verificar as permissões, pode retornar erro caso não exista transação
    try
    {
        if ( $content->checkAccess() )
        {
            $content->setIcon($ui->getImage($module, $img ));
            $theme->setContent($content);
        }
        else
        {
            // Testar se existe sessão e o usuário não tem acesso, mostra mensagem de falta de acesso.
            if ( $MIOLO->getLogin()->id )
            {
                GForm::error(USUARIO_SEM_PERMISSAO);
            }
            else
            {
                $loginUrl = $MIOLO->getConf('home.url').'/index.php?module=gnuteca3&action=main:login' ;
                $page->redirect( $loginUrl );
            }
        }
        
        createBreadCrumb();
    }
    catch (Exception $ex)
    {
        GForm::error( $ex->getMessage() );
    }
}

function createBreadCrumb()
{
    global $navbar;
    global $action;
        
    $handlers = explode(':', $action);
    
    if ( $handlers[count($handlers) - 2] == 'search' )
    {
        $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="iconeSearch">' . _M('Pesquisa') . '</div>', $module);
        return;
    }
    
    $options = array();
    foreach ( $handlers as $h )
    {
        $pai =  $_SESSION['menuItems'][$h]['pai'];
        if ( $handlers[1] == 'catalogue' && $h != 'marc21import' )
        {
            $pai = '';
        }
        if ( strlen($pai) > 0 )
        {            
            $descricao = $_SESSION['menuItems'][$pai]['descricao'];
            $icone = $_SESSION['menuItems'][$pai]['icone'];
            if ( strlen($descricao) > 0 && !in_array($descricao, $options) )
            {
                $options[] = $descricao;
                $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="' . $icone . '">' . $descricao . '</div>', $module);
            }
        }
        
        if ( $h == 'dictionarycontent' )
        {
            $options[] = _M('Dicionário');
            $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="iconeConfig">' . _M('Dicionário') . '</div>', $module);
        }
        
        $descricao = $_SESSION['menuItems'][$h]['descricao'];
        $icone = $_SESSION['menuItems'][$h]['icone'];
        if ( strlen($descricao) > 0 && !in_array($descricao, $options) )
        {
            $options[] = $descricao;
            $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="' . $icone . '">' . $descricao . '</div>', $module);
        }
        
        if ( $h == 'dictionary' )
        {
            $options[] = _M('Cadastro');
            $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="iconeDictionarycontent">' . _M('Cadastro') . '</div>', $module);
        }
    }
    
    if ( $handlers[2] == 'iso2709' )
    {
        $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="iconeIso2709">ISO2709</div>', $module);
    }
    
    if ( $handlers[2] == 'libraryPreference' )
    {
        $navbar->addOption('<div style="height: 22px; line-height: 22px; background-position: center left; background-repeat: no-repeat; padding-left: 20px; cursor: pointer;" onclick="return false;" class="iconeLibraryPreference">' . $_REQUEST['tabName'] . '</div>', $module);
    }
    
    
}

?>
