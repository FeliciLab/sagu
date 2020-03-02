<?php

$MIOLO->trace('file:'.$_SERVER['SCRIPT_NAME']);
 
$MIOLO->checkAccess('admin', A_ACCESS, true);

$navbar->addOption( _M('Remove Modules'), $module, 'main:modules:rem_modules');

$ui   = $MIOLO->getUI();
$form = $ui->getForm($module,'frmRemModule');
$theme->appendContent($form);

$handled = $MIOLO->invokeHandler($module, 'modules/'.$context->shiftAction());
if (! $handled)
{
    $theme->insertContent($cmPanel);
}
include_once($MIOLO->getConf('home.modules') .'/main_menu.inc');

?>
