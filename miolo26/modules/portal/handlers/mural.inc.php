<?php

$MIOLO->checkAccess('FrmMuralAluno', A_ACCESS, TRUE);

$theme->clearContent();

$ui = $MIOLO->getUI();
$navbar->addOption(_M('Mural', $module), $module, $self);
$form = $ui->getForm($module, 'frmMural');
$theme->insertContent($form);
?>
