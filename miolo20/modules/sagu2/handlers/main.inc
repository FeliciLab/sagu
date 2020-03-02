<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Sagu2 main handler.
 * Contains the basic menus to access submenus
 *
 * @author Daniel Afonso Heisler [daniel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 09/08/2006
 */

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

if ( !$module )
{
    $module = 'sagu2';
}

if ( ($sa = $context->shiftAction()) != NULL )
{
    $a = $sa;
}
elseif ( $module != 'sagu2' )
{
    $a = 'main';
}

if ( SAGU::isUserLogged() )
{
    $ui = $MIOLO->getUI();

    $theme->clearContent();
    
    $handled = $MIOLO->invokeHandler($module,$a);
    
    //Verifica se existe uma configuração para troca de senha e redireciona para tela de troca
    if( BusinessBasicBusConfiguracaoTrocaDeSenha::verificaTrocaDeSenha() == DB_TRUE )
    {
        $content =  $ui->getForm('sagu2', 'FrmTrocaDeSenha');
        $theme->setContent($content);
    }
    
    //MIOLO2 menu
    include_once($MIOLO->getConf('home.modules') .'/sagu2/handlers/menu.inc');
    
    if ( $MIOLO->getConf('options.widgets') == 'true' )
    {
        $content =  $ui->getForm($module, 'FrmPrincipal');
        $theme->setContent($content);
    }
    else
    {
    }
}
else
{
    $ui = $MIOLO->getUI();
    $content =  $ui->getForm('admin', $MIOLO->getConf('options.authmd5') ? 'frmLoginMD5' : 'frmLogin');
    $theme->setContent($content);
}
?>
