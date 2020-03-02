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
 * Basic payrollDiscount handler.
 * Contains the menus to access payrollDiscount registers
 *
 * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
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
 * Class created on 12/12/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

if( $_POST['__EVENTTARGETVALUE'] == 'submit_button:click' )
{
    $MIOLO->checkAccess('FrmPayrollDiscountFinance', A_EXECUTE, true, true);
}
else 
{
    $MIOLO->checkAccess('FrmPayrollDiscountFinance', A_ACCESS, true, true);
}

$home = 'main:process:payrollDiscount';
$navbar->addOption(_M('Desconto na folha de pagamento', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
access::insert(_M('Desconto na folha de pagamento', $module), $home, 'payrollDiscount-16x16.png');
$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();
          
$frmPayrollDiscount = $ui->getForm( $module, 'FrmPayrollDiscount', $data );

$content = $frmPayrollDiscount;

$theme->clearContent();
$theme->insertContent($content);

?>
