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
 * Academic examination act handler.
 * Contains the menus to access center registers
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 02/12/2010
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('FrmActiveContractWithPendingMovement', A_EXECUTE, true, true);

$home = 'main:report:activeContractWithPendingMovement';
$navbar->addOption(_M('Contratos ativos com movimentação pendente', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
access::insert(_M('Contratos ativos com movimentação pendente', $module), $home, 'activeContractWithPendingMovement-16x16.png');
$data->home = $home;

$ui = $MIOLO->getUI();

$form = $ui->getForm($module,'FrmActiveContractWithPendingMovement', $data);
$form->setClose($MIOLO->getActionURL($module, substr($home, 0, strrpos($home, ':'))));
$content = $form;

$theme->clearContent();
$theme->insertContent($content);

?>
