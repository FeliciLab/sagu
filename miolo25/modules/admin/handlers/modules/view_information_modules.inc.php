<?php

$theme->clearContent();

$MIOLO->checkAccess('admin', A_ACCESS, true);

$navbar->addOption( _M('Module Information'), $module, 'main:modules:rem_modules:view_information_modules');

$ui   = $MIOLO->getUI();
$form = $ui->getForm($module,'frmViewInformationModules');
$theme->appendContent($form);


?>