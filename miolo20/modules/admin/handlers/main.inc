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
 *
 * This file main
 *
 * @author Leovan Tavares da Silva [leovan] [leovan@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 14/06/2006
 *
 **/

$MIOLO->Trace('file:'.$_SERVER['SCRIPT_NAME']);

if ( !$module )
{
    $module = 'admin';
}

if ( ($sa = $context->ShiftAction()) != NULL )
{
    $a = $sa;
}
elseif ( $module != 'admin' )
{
    $a = 'main';
}

$MIOLO->checkAccess('ADMIN', A_ACCESS, true);

$navbar->AddOption(_M('Administração', $module) ,$module, 'main');

$ui = $MIOLO->GetUI();

$theme->ClearContent();

$close = $MIOLO->GetActionURL($module, 'main');
$cmPanel = new ActionPanel('pnlAdmin', _M('Modulo administrativo', $module),'', $close, $ui->GetImage($module, 'system-16x16.png'));

$menuItem = array();
$menuItem[] = array(_M('Usuários', $module), 'user-32x32.png', 'main:users');
$menuItem[] = array(_M('Grupos', $module), 'groups-32x32.png', 'main:groups');
$menuItem[] = array(_M('Sair', $module), 'logout-32x32.png', 'logout');

foreach ( $menuItem as $m )
{
   $cmPanel->AddAction($m[0], $ui->getImage($module,$m[1]), $module, $m[2], null, array());
}

$handled = $MIOLO->InvokeHandler($module,$a);
if ( !$handled )
{
    $theme->InsertContent($cmPanel);
}

include_once($MIOLO->GetConf('home.modules') .'/main_menu.inc');
?>
