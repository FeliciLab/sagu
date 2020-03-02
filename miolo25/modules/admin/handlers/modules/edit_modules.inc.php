<?php

$MIOLO->checkAccess('admin', A_ACCESS, true);

$home = 'main:admin';
$navbar->addOption( _M('Modules'), $module, $home);

$ui   = $MIOLO->getUI();
$form = $ui->getForm($module,'frmModule');
$theme->appendContent($form);
?>
