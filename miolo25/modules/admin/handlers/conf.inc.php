<?php
$perms->checkAccess('group', A_ACCESS, true);
$home = 'main:admin';
$navbar->addOption( _M('Options', $module), $module, $action);

$theme->clearContent();

$ui   = $MIOLO->getUI();
$form = $ui->getForm($module,'frmConf');

if( ! MIOLO::_REQUEST('ajax') )
{
    $theme->appendContent($form);
}
?>
