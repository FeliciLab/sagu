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
 * Basic enroll handler.
 * Contains the menus to access enroll registers
 *
 * @author Jamiel Speziia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 26/08/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('FrmEnrollContract', A_ACCESS, true, true);

$home = 'main:register:enroll';
$navbar->addOption(_M('Matrícula', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
//access::insert(_M('Matrícula', $module), $home, 'enroll-16x16.png');
$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();
switch ( $function )
{
    case 'insert':
        $MIOLO->checkAccess('FrmEnrollContract', A_INSERT, true, true);
    break;
    case 'update':
        if (strlen(MIOLO::_request('event')) > 0)
        {
            $MIOLO->checkAccess('FrmEnrollContract', A_UPDATE, true, true);
        }
        else
        {
            $MIOLO->checkAccess('FrmEnrollContract', A_ACCESS, true, true);
        }
    break;
    case 'delete':
        $MIOLO->checkAccess('FrmEnrollContract', A_DELETE, true, true);
    break;
}

$frmenroll = $ui->getForm( $module, 'FrmEnroll' );
$content   = $frmenroll;

if ($theme->page->generateMethod != 'generateAJAX')
{
    $theme->clearContent();
    $theme->insertContent($content);
}

?>
