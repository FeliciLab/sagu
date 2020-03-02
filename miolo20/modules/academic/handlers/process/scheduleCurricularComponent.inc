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
 * Group handler.
 * Contains the menus to access schedule curricular component registers
 *
 * @author Samuel Koch [samuel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 16/10/2008
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

if( MIOLO::_request('event') == 'btnSave:click' || $_POST['__EVENTTARGETVALUE'] == 'btnSave:click' )
{
    $MIOLO->checkAccess('FrmGroupAcademic', A_EXECUTE, true, true);
}
else 
{
    $MIOLO->checkAccess('FrmGroupAcademic', A_ACCESS, true, true);
}

$home = 'main:process:scheduleCurricularComponent';
$navbar->addOption(_M('Horário da disciplina oferecida', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
access::insert(_M('Horário da disciplina oferecida', $module), $home, 'group-16x16.png');
$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();

$frmScheduleCurricularComponent = $ui->getForm( $module, 'FrmScheduleCurricularComponent' );
$content                        = $frmScheduleCurricularComponent;

if ( $theme->page->generateMethod != 'generateAJAX' )
{
    $theme->clearContent();
    $theme->insertContent($content);
}

?>
