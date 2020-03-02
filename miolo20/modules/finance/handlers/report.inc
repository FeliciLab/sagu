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
 * Finance report handler.
 * Contains the menus to access geography submenus
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Cristian Edson Göhl [cristian@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Eduardo Beal Miglioransa [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 16/12/2005
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess('finReport', A_ACCESS, true, true);

$function = MIOLO::_request('function');
if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$home = 'main:report';
$navbar->addOption(_M('Relatório', $module), $module, $home, null, array('function'=>'resetStack'));

if ( !$MIOLO->invokeHandler($module,'report/'.$context->shiftAction()) )
{
    $ui       = $MIOLO->getUI();
    $btnClose = $MIOLO->getActionURL($module,substr($home,0,strrpos($home,':')));

    $icon        = $ui->getImage($module,'report-16x16.png');
    $pnlReport = new MActionPanel('pnlReport', _M('Relatório', $module), NULL, $btnClose, $icon);
    $pnlReport->setIconType('small');
    $pnlReport->setControlSize(SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_WIDTH'),SAGU::getParameter('BASIC', 'MPANEL_DEFAULT_HEIGHT'));

    $menuItem = array( 
                       array(_M('Acordos amigáveis', $module), 'folder-16x16.png', "$home:friendlyAgreement"),
                       array(_M('Histórico sintetizado', $module), 'summarizedHistory-16x16.png', "$home:summarizedHistory"),
                       array(_M('Histórico explanado', $module), 'completeHistory-16x16.png', "$home:completeHistory"),
                       array(_M('Movimentações de caixa', $module), 'counterMovementReport-16x16.png', "$home:counterMovementReport"),
                       array(_M('Movimentações bancárias', $module), 'counterMovementReport-16x16.png', "$home:bankMovementReport"),
                       array(_M('Inadimplência analítico', $module), 'defaulterReport-16x16.png', "$home:defaulterReport"),
                       array(_M('Inadimplência sintético', $module), 'defaulterReportSummary-16x16.png', "$home:defaulterReportSummary"),
                       array(_M('Contas a receber detalhado', $module), 'receivableInvoiceReport-16x16.png', "$home:receivableInvoiceReport"),
                       array(_M('Contas a receber resumido', $module), 'receivableInvoiceReport-16x16.png', "$home:receivableInvoiceReportSummary"),
                       array(_M('Relatório geral de caixa', $module), 'generalCounterMovement-16x16.png', "$home:generalCounterMovement"),
                       array(_M('Descontos incentivos e financiamentos', $module), 'receivableInvoiceReport-16x16.png', "$home:discountsIncentivesAndLoan")
                     );
                     
    $menuItem = array_merge( SAGU::getGenericReportsMenu($module), $menuItem );

    $menuItem = SAGU::arraySort($menuItem, 0);
    
    $MIOLO->uses('classes/breport.class','base');
    $breport = new BReport();
    foreach($breport->listReports(BReport::TYPE_REPORT,$module) as $report)
    {
        $menuItem[] = array(_M($report['title'].'*', $module), 'default-16x16.png', "$home:generateReport&report={$report['name']}");
    }
    
    foreach ( $menuItem as $m )
    {
       $pnlReport->addAction($m[0], $ui->getImage($module,$m[1]), $module, $m[2], $m[3], $m[4]);
    }
    $theme->clearContent();
    $theme->insertContent($pnlReport);
}

?>
