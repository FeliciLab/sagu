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
 * Pupil contracts handler.
 * Shows all the pupil contracts available for enroll
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Cristian Edson Göhl [cristian@solis.coop.br]
 *
 * @since
 * Class created on 03/05/2011
 */

$home = 'main:professor:gradeBook';
$navbar->addOption(_M('Disciplinas ministradas pelo professor', $module), $module, substr($home, 0, strrpos($home, ':')+1) . 'curricularComponents', null, array('periodId' => MIOLO::_request('periodId')));
$navbar->addOption(_M('Plano de ensino', $module), $module, $home, null, array('function' => 'resetStack'));

$ui = $MIOLO->getUI();
$frmGradeBook = $ui->getForm($module, 'FrmGradeBook');

$theme->clearContent();
$theme->insertContent($frmGradeBook);
?>