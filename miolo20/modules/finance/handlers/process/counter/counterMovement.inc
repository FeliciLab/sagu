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
 * Finance counterMovement handler.
 * Contains the menus to access counterMovement registers
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Class created on 03/01/2006
 *
 * */
$module = MIOLO::getCurrentModule();
$action = "main:process:counter:counterMovement";
$title = _M("Caixa", $module);
$form = "FrmCounterMovement";
$acao = MIOLO::_REQUEST("acao");

$login = $MIOLO->GetLogin();
$busPhysicalPersonEmployee = $MIOLO->getBusiness('basic', 'BusPhysicalPersonEmployee');
$pessoaLogada = $busPhysicalPersonEmployee->getPersonByMioloUserName($login->id);

$caixa = null;

if ( is_object($pessoaLogada) )
{
    $busOpenCounter = $MIOLO->getBusiness($module, 'BusOpenCounter');
    $busCounter = $MIOLO->getBusiness($module, 'BusCounter');

    // Filtros para a pessoa que está tentando acessar
    $filtro = new stdClass();
    $filtro->operatorId = $pessoaLogada->personId;
    $filtro->open = 't';

    $pesquisaOpenCounter = $busOpenCounter->searchOpenCounter($filtro);
    
    if ( is_array($pesquisaOpenCounter) )
    {
        $caixaEmAberto = $busOpenCounter->getOpenCounter($pesquisaOpenCounter[0][0]);
        $caixa = $busCounter->getCounter($caixaEmAberto->counterId);    
    }
}

$EhCaixaTesouraria = $caixa->isCounterTreasury === DB_TRUE;
$CAPEstaHabilitado = SAGU::getParameter("basic", "MODULE_CONTASPAGAR_INSTALLED") === "YES";

if ( ( ( (strlen($acao) === 0 || ($acao === "pagar" ) ) && $EhCaixaTesouraria ) ) && $CAPEstaHabilitado && ( strstr($MIOLO->getCurrentURL(), "acao=receber") === false ) )
{
    $form = "FrmCounterMovementPagar";
}
else
{
    $form = "FrmCounterMovement";
}

$ui = $MIOLO->getUI();
$navbar->addOption($title, $module, $action);
$content = $ui->getForm($module, $form);

if ( $theme->page->generateMethod != "generateAjax" )
{
    $theme->clearContent();
    $theme->insertContent($content);
}

?>
