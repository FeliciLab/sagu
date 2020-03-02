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
 * Tasks handler.
 * Contains the menus to access tasks registers
 *
 * @author Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 * Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 18/03/2011
 */

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

$home = 'main:config:tasks';
$module = SAGU::getFileModule(__FILE__);
$navbar->addOption(_M('Pendências', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
access::insert(_M('Pendências', $module), $home, 'timeSheet-16x16.png');
$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();
if ( (strlen($function) == 0) || ($function == 'search') )
{
    $MIOLO->checkAccess('FrmTask', A_ACCESS, true, true);

    $frmTask = $ui->getForm($module, 'FrmTaskSearchServices', $data);
    $frmTask->setClose($MIOLO->getActionURL($module, substr($home, 0, strrpos($home, ':'))));

    $content = $frmTask;
}
else
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmTask', A_INSERT, true, true);
            break;
        case 'update':
            if ( strlen(MIOLO::_request('event')) > 0 )
            {
                $MIOLO->checkAccess('FrmTask', A_ACCESS, true, true);
            }
            break;
        case 'delete':
            $MIOLO->checkAccess('FrmTask', A_DELETE, true, true);
            break;
    }

    $frmTasks = $ui->getForm($module, 'FrmTaskServices');
    $content = $frmTasks;
}

// Theme receives the contents of the form
if ( $theme->page->generateMethod != 'generateAjax' )
{
    $theme->clearContent();
    $theme->insertContent($content);
}
?>