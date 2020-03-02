<?php


if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') || strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))
{
    $campos = array();
    $botoes = array();

    $campos[] = new MLabel('Esta tela so pode ser acessada através do computador');
    $botoes[] = new MButton('botaoVoltar', _M('Voltar', $module), "history.back();");
    $campos[] = MUtil::centralizedDiv($botoes);
    
    $dialog = new MDialog('dialogoAcesso', _M('Aviso', $this->modulo), $campos);
    $this->manager->page->onload("$('#dialogoAcesso').trigger('pagecreate');");
    $dialog->show();
    $theme->appendContent($dialog);
    return;
}

require_once('/var/www/sagu/modules/basic/classes/sAutoload.class');

$sAutoload = new sAutoload();
$sAutoload->definePaths();

// adicionais
require_once('/var/www/sagu/modules/basic/classes/sform.class');
require_once('/var/www/sagu/modules/basic/classes/sstepinfo.class');
require_once('/var/www/sagu/modules/basic/classes/sstepbystepform.class');
require_once('/var/www/sagu/modules/services/forms/FrmEnrollWeb.class');

require_once('/var/www/miolo2_sagu2/classes/ui/controls/mtoolbar.class');
//require_once('/var/www/miolo2_sagu2/classes/ui/controls/mtoolbarbutton.class');
//require_once('/var/www/miolo/classes/ui/controls/mtoolbar.class.php');
//require_once('/var/www/miolo/classes/ui/controls/mtoolbarbutton.class.php');

require_once('/var/www/sagu/modules/academic/classes/Matricula.class');
require_once('/var/www/sagu/modules/academic/types/AcdContract.class');

require_once('/var/www/sagu/modules/basic/classes/SHiddenField.class');


$module = 'services';
$action = 'main:enrollWeb';
$title = _M('Matrícula Web', $module);
$steps[1] = new SStepInfo('FrmEnrollWeb1', _M('Dados', $module), $module);
$steps[2] = new SStepInfo('FrmEnrollWeb2', _M('Disciplinas', $module), $module);
$steps[3] = new SStepInfo('FrmEnrollWeb3', _M('Finalização', $module), $module);

SAGU::handle($module, $action, $title, $searchForm, $steps, 'FrmEnrollWeb', true);

/*
$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Financeiro', $module), $module, $self);
$form = $ui->getForm($module, 'frmFinanceiro');
$theme->insertContent($form);
*/

?>
