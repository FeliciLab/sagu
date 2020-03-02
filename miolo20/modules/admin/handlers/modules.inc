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
 * This file modules
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
$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);

$MIOLO->checkAccess('admin',A_ACCESS,true);

$navbar->addOption( _M('Modules', $module) ,$module, 'main:modules');

$ui = $MIOLO->getUI();

$theme->clearContent();

$close = $MIOLO->getActionURL($module,'main');

$cmPanel = new ActionPanel('pnlAdmin', _M('Modules Maintainance',$module),'', $close, $ui->getImage($module,'modules-16x16.png') );

$cmPanel->addAction( _M('Editar módulo', $module)      , $ui->getImage($module, 'module_edit-32x32.png'), $module, 'main:modules:edit_modules');
$cmPanel->addAction( _M('Instalar novo modulo', $module), $ui->getImage($module, 'module_add-32x32.png'), $module, 'main:modules:modules_new');
$cmPanel->addAction( _M('Remover modulos', $module)    , $ui->getImage($module, 'module_del-32x32.png'), $module, 'main:modules:rem_modules');

$handled = $MIOLO->invokeHandler($module,'modules/'.$context->shiftAction() );

if ( ! $handled )
{
    $theme->insertContent($cmPanel);
}
