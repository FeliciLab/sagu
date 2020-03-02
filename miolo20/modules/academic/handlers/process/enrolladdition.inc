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
 * Basic center handler.
 * Contains the menus to access center processs
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 01/02/2006
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

if( MIOLO::_request('event') == 'addition' )
{
    $MIOLO->checkAccess('FrmEnrollAddition', A_EXECUTE, true, true);
}
else
{
    $MIOLO->checkAccess('FrmEnrollAddition', A_ACCESS, true, true);    
}

$home = 'main:process:enrolladdition';
$navbar->addOption(_M('Acréscimo de disciplina', $module), $module, $home);

$MIOLO->getClass('basic', 'access');

access::insert(_M('Acréscimo de disciplina', $module), $home, 'enrollAddition-16x16.png');

$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();

$frmEnrollAddition = $ui->getForm( $module, 'FrmEnrollAddition', $data);

$content = $frmEnrollAddition;

$theme->clearContent();
$theme->insertContent($content);

?>
