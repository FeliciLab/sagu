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
 * Control copies register handler.
 * Contains the menus to access register submenus
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Rafael Luís Spengler [rafael@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 17/08/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess($module, CCP_ACCESS, true, true);

$function = MIOLO::_request('function');
if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$home = 'main:register';
$navbar->addOption(_M('Cadastro', $module), $module, $home, null, array('function'=>'resetStack'));

if ( !$MIOLO->invokeHandler($module,'register/'.$context->shiftAction()) )
{

    $ui       = $MIOLO->getUI();
    $btnClose = $MIOLO->getActionURL($module,substr($home,0,strrpos($home,':')));

    $icon                = $ui->getImage($module,'register-16x16.png');
    $pnlControlCopies = new MActionPanel('pnlControlCopies', _M('Cadastro', $module), null, $btnClose, $icon);
    $pnlControlCopies->setIconType('small');
    $pnlControlCopies->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'),SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));

    $menuItem = array(
                       array(_M('Cópias', $module),        'copy-16x16.png',    "$home:copy"),
                       array(_M('Setor', $module),        'copy-16x16.png',    "$home:sector"),
                       array(_M('Período', $module),        'copy-16x16.png',    "$home:period")
                        );

    if ( SAGU::getParameter('BASIC', 'MODULE_ACADEMIC_INSTALLED') == 'YES' )
    {
        $menuItem[] = array(_M('Projeto',$module),           'project-16x16.png', "$home:project");
        $menuItem[] = array(_M('Requisição',$module),        'request-16x16.png', "$home:request");
        $menuItem[] = array(_M('Regras',  $module),          'rule-16x16.png',    "$home:rule");
        $menuItem[] = array(_M('Serviço',$module),           'service-16x16.png', "$home:service");
        $menuItem[] = array(_M('Desconto em folha',$module), 'payrolldiscount-16x16.png', "$home:payrolldiscount");
    }

    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    foreach ( $sortMenuItem as $m )
    {
       $pnlControlCopies->addAction($m[0], $ui->getImage($module,$m[1]), $module, $m[2],
       $module, $m[3], $module, $m[4], $module, $m[5], $module, $m[6]);
    }

    $theme->clearContent();
    $theme->insertContent($pnlControlCopies);

}

?>
