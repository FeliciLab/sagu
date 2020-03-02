<?php
$MIOLO = MIOLO::getInstance();
$perms = $MIOLO->perms;
$perms instanceof BPermsBase;

$MIOLO->checkAccess('frmcapconfiguracao', A_ACCESS, true, true);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Configuração', $module), $module, $self);
$form = $ui->getForm($module, 'frmconfiguracao');
$theme->insertContent($form);
?>
