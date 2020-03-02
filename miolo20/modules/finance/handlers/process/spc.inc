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
 * Finance register handler.
 * Contains the menus to access register submenus
 *
 * @author Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Class created on 11/12/2005
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('finSPC', A_ACCESS, true, true);

$function = MIOLO::_REQUEST('function');

if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$module = MIOLO::getCurrentModule();
$home = 'main:process:spc';
$navbar->addOption(_M('SPC', $module), $module, $home, null, array('function' => 'resetStack'));

if ( !$MIOLO->invokeHandler($module, 'process/spc/' . $context->shiftAction()) )
{
    
    $ui = $MIOLO->getUI();
    $btnClose = $MIOLO->getActionURL($module, substr($home, 0, strrpos($home, ':')));
    
    $icon = $ui->getImage($module, 'process-16x16.png');
    $pnlPupilAssistance = new MActionPanel('pnlPupilAssistance', _M('SPC', $module), null, $btnClose, $icon);
    $pnlPupilAssistance->setIconType('small');
    $pnlPupilAssistance->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'), SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));
    
    $menuItem = array(
                        array(_M('Razão SPC', $module), 'spcReason-16x16.png', "$home:spcReason"),
                        array(_M('Registrar pessoas no SPC', $module), 'spc-16x16.png', "$home:spc")                        
                     );
    
    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    
    foreach ( $sortMenuItem as $m )
    {
        $pnlPupilAssistance->addAction($m[0], $ui->getImage($module, $m[1]), $module, $m[2]);
    }
    
    $theme->clearContent();
    $theme->insertContent($pnlPupilAssistance);

}

?>

