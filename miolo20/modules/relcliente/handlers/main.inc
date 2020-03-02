<?php
$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$function = MIOLO::_request('function');

if ( $function == 'resetStack' )
{
    SAGU::resetStack();
}

$home = 'main';
$navbar->addOption(_M('Relacionamento com cliente', $module), $module, $home, null, array('function' => 'resetStack'));

//MIOLO2 menu
//include_once($MIOLO->GetConf('home.modules') .'/main_menu.inc');
include_once($MIOLO->GetConf('home.modules') .'/sagu2/handlers/menu.inc');


$ui = $MIOLO->getUI();

if ( !SAGU::invokeHandlerAuto() )
{
    $MIOLO->checkAccess('RELCLIENTE', A_ACCESS, true, true);

    $a = new Separator();
    $frmDiverseConsultation = $ui->getForm($module, 'FrmDiverseConsultation', $data);
    $frmDiverseConsultation->setClose($btnClose);


    if ( $theme->page->generateMethod != 'generateAJAX' )
    {
        $theme->clearContent();
        $theme->AppendContent($frmDiverseConsultation);
    }
}
?>
