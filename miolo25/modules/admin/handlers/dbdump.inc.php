<?php

$perms->checkAccess('admin', A_ACCESS, true);
$home = 'main:admin';
$navbar->addOption(_M('Database Dump', $module), $module, $action);

$theme->clearContent();

$ui   = $MIOLO->getUI();
$form = $ui->getForm($module,'frmDbDump');

if( ! MIOLO::_REQUEST('ajax') )
{
    $theme->appendContent($form);
}

?>