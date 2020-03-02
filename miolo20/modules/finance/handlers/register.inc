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
 * Contains the menus to access geography submenus
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Eduardo Beal Miglioransa [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 17/08/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('finRegister', A_ACCESS, true, true);

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

    $icon        = $ui->getImage($module,'register-16x16.png');
    $pnlRegister = new MActionPanel('pnlRegister', _M('Cadastro', $module), NULL, $btnClose, $icon);
    $pnlRegister->setIconType('small');
    $pnlRegister->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'),SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));

    $menuItem = array( array(_M('Conta bancária',        $module), 'bankAccount-16x16.png',       "$home:bankAccount"),
                       array(_M('Banco',                $module), 'bank-16x16.png',              "$home:bank"),
                       array(_M('Conta para débito automático',         $module), 'invoiceTarget-16x16.png',     "$home:bankTarget"),
                       array(_M('Tipo de cobrança',     $module), 'collectionType-16x16.png',    "$home:collectionType"),
                       array(_M('Operação',           $module), 'operation-16x16.png',         "$home:operation"),
                       array(_M('Política',              $module), 'policy-16x16.png',            "$home:policy"),
                       array(_M('Preço',               $module), 'price-16x16.png',             "$home:price"),
                       array(_M('Título',             $module), 'folder-16x16.png',            "$home:invoice", null, array('function'=>'resetStack')),
                       array(_M('Espécie',             $module), 'species-16x16.png',           "$home:species"),
                       array(_M('Origem',       $module), 'incomeSource-16x16.png',      "$home:incomeSource"),
                       array(_M('Previsão de lançamento',     $module), 'incomeForecast-16x16.png',    "$home:incomeForecast"),
                       array(_M('Mensagens para os demonstrativos financeiros',    $module), 'voucherMessages-16x16.png',   "$home:voucherMessages"),
                       array(_M('Caixa',             $module), 'counter-16x16.png',           "$home:counter"),
                       array(_M('Taxa de matrícula',          $module), 'enrollFee-16x16.png',         "$home:enrollFee"),
                       array(_M('Lançamento',               $module), 'entry-16x16.png',             "$home:entry"),
                       array(_M('Operações padrão',  $module), 'defaultOperations-16x16.png', "$home:defaultOperations"),
                       array(_M('Endereço de cobrança',     $module), 'physicalTarget-16x16.png',    "$home:physicalTarget"),
                       array(_M('Motivo do cancelamento', $module), 'reasonCancellation-16x16.png', "$home:reasonCancellationFinance"),
		       array(_M('Convênio da pessoa',    $module), 'convenantPerson-16x16.png',   "$home:convenantPerson"),
                       array(_M('Tipo de incentivo',      $module), 'folder-16x16.png',            "$home:incentiveType", null, array('function'=>'resetStack')),
                       array(_M('Incentivo',           $module), 'incentive-16x16.png',         "$home:incentive"),
                       array(_M('Grupo de operações',     $module), 'operationGroup-16x16.png',    "$home:operationGroup"),
                       array(_M('FIES retroativo',   $module), 'studentFinancing-16x16.png',  "$home:studentFinancing")
                     );

    $sortMenuItem = SAGU::arraySort($menuItem, 0);
    foreach ( $sortMenuItem as $m )
    {
       $pnlRegister->addAction($m[0], $ui->getImage($module,$m[1]), $module, $m[2], $m[3], $m[4]);
    }

    $theme->clearContent();
    $theme->insertContent($pnlRegister);
}

?>
