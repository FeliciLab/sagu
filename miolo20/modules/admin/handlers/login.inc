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
 * This file login
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

//BLoqueia o logins para uma URL. Foi criado para os casos onde o cliente que disponibilizar o portal mas não disponibilizar o sistema para o administrativo
if ($_SERVER['HTTP_HOST'] == $MIOLO->GetConf('login.denyURL'))
{
    die();
}
 
$MIOLO->LogMessage('[LOGIN] file:'.$_SERVER['SCRIPT_NAME']);
$MIOLO->LogMessage('[LOGIN] Using login prompt');

$MIOLO->page->addScript(SAGU::ABSOLUTE_URL_JS_FILES . 'sagu.js');

$module = 'admin';
$ui = $MIOLO->GetUI();
$theme->ClearContent();
$navbar->AddOption('Login', $module, 'login');
$login = $auth->GetLogin();

// If the user is logged in, send to the start up module
if ($login && !SAGU::userIsFromServices() )
{
   //$form = $ui->GetForm($module,'frmAccess',$login);
   $MIOLO->invokeHandler($MIOLO->getConf('options.common'), 'main');
}
elseif ($MIOLO->GetConf('options.authmd5'))
{
   $form = $ui->GetForm($module,'frmLoginMD5');
}
else
{
   $form = $ui->GetForm($module,'frmLogin');
}
if ($theme->GetContent()  == '')
{
   $theme->InsertContent($form);
}
?>
