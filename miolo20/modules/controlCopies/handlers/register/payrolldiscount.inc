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
 *
 * This file handles the connection and actions for sprExamProcessOccurrence table
 *
 * @author Rafael Dutra  [rdutra@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 21/08/2006
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

$theme->clearContent($content);

$home = 'main:register:payrolldiscount';
$navbar->addOption(_M('Desconto em folha', $module), $module, $home);

$MIOLO->getClass('basic', 'access');
access::insert(_M('Desconto em folha', $module), $home, 'payrolldiscount-16x16.png');
$data->home = $home;

$function = MIOLO::_request('function');

$ui = $MIOLO->getUI();

if (( $function == '') || ($function == 'search'))
{
    $MIOLO->checkAccess('FrmPayRollDiscount', A_ACCESS, true, true);
    
    $frmPayRollDiscountSearch = $MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':')));
    $frmPayRollDiscountSearch = $ui->getForm($module,'FrmPayRollDiscountSearch', $data);
    $content   = $frmPayRollDiscountSearch;
}
else
{
    switch ( $function )
    {
        case 'insert':
            $MIOLO->checkAccess('FrmPayRollDiscount', A_INSERT, true, true);
        break;
        case 'update':
            if (strlen(MIOLO::_request('event')) > 0)
            {
                $MIOLO->checkAccess('FrmPayRollDiscount', A_UPDATE, true, true);
            }
            else
            {
                $MIOLO->checkAccess('FrmPayRollDiscount', A_ACCESS, true, true);
            }
        break;
        case 'delete':
            $MIOLO->checkAccess('FrmPayRollDiscount', A_DELETE, true, true);
        break;
    }
    
    $frmPay = $MIOLO->getActionURL($module,substr($home, 0, strrpos($home, ':')));
    $frmPay = $ui->getForm($module,'FrmPayRollDiscount', $data);
    $content   = $frmPay;
}

$theme->SetContent($content);
?>
