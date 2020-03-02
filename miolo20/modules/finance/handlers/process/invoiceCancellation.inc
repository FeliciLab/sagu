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
 * Invoice cancelation handler.
 * Contains the menus to access process submenus
 *
 * @author Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jonas Gualberto Diel [jonas_diel@solis.coop.br]
 *
 * @since
 * Class created on 27/08/2010
 *
 **/

$MIOLO = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('FrmInvoiceCancellation', A_EXECUTE, true, true);

$home = 'main:process:invoiceCancellation';
$navbar->addOption(_M('Cancelamento de títulos', $module), $module, $home);

$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();
          
$frmPrintInvoice = $ui->getForm( $module, 'FrmInvoiceCancellation', $data );

$content = $frmPrintInvoice;

$theme->clearContent();
$theme->insertContent($content);
?>
