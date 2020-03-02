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
 * Academic generic reports handler.
 * Generates a user-defined report selected by the user.
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 22/07/2008
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess($module, A_EXECUTE, true, true);

$business   = new BusinessBasicBusGenericReports();
$reportData = $business->getReport(MIOLO::_request('reportId'));

$home = 'main:report:genericReports';
$navbar->addOption($reportData->name, $module, $home, null, array('reportId'=>$reportData->reportId));

$data->home = $home;

$ui = $MIOLO->getUI();
$frmGenericReportGen = $ui->getForm('basic','FrmGenericReportGeneration', $data);
$frmGenericReportGen->setClose($MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':'))));
$theme->clearContent();
$theme->insertContent($frmGenericReportGen);

?>
